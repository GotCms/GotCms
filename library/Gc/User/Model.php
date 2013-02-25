<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage User
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User;

use Gc\Db\AbstractTable;
use Gc\Core\Config;
use Gc\Mail;
use Gc\Registry;
use Zend\Authentication\Adapter;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Select;
use Zend\Validator\EmailAddress;

/**
 * Model of user
 *
 * @category   Gc
 * @package    Library
 * @subpackage User
 */
class Model extends AbstractTable
{
    /**
     * Backend auth namespace for authenticationService
     *
     * @const string
     */
    const BACKEND_AUTH_NAMESPACE = 'Zend_Auth_Backend';

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user';

    /**
     * Authenticate user
     *
     * @param string $login
     * @param string $password
     * @return boolean
     */
    public function authenticate($login, $password)
    {
        $auth_adapter = new Adapter\DbTable($this->getAdapter());
        $auth_adapter->setTableName($this->name);
        $auth_adapter->setIdentityColumn('login');
        $auth_adapter->setCredentialColumn('password');

        $auth_adapter->setIdentity($login);
        $auth_adapter->setCredential(sha1($password));

        $auth = new AuthenticationService(new Storage\Session(self::BACKEND_AUTH_NAMESPACE));
        $result = $auth->authenticate($auth_adapter);

        if ($result->isValid()) {
            $data = $auth_adapter->getResultRowObject(null, 'password');
            $this->setData((array)$data);
            $auth->getStorage()->write($this);

            return true;
        }

        return false;
    }

    /**
     * Set User email
     *
     * @param string $user_email
     * @return boolean
     */
    public function setEmail($user_email)
    {
        $user_email = trim($user_email);
        $validator = new EmailAddress();
        if ($validator->isValid($user_email)) {
            $select = $this->select(
                function (Select $select) use ($user_email) {
                    $select->where->equalTo('email', $user_email);

                    if ($this->getId() != -1) {
                        $select->where->notEqualTo('id', $this->getId());
                    }
                }
            );

            $rows = $this->fetchAll($select);
            if (count($rows) == 0) {
                $this->setData('email', $user_email);
                return true;
            }
        }

        return false;
    }

    /**
     * Set user password
     *
     * @param string $user_password
     * @param boolean $encrypt
     * @return void
     */
    public function setPassword($user_password, $encrypt = true)
    {
        $this->setData('password', ($encrypt ? sha1(trim($user_password)) : trim($user_password)));
    }


    /**
     * Save user
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', null, array('object' => $this));
        $array_save = array(
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail(),
            'login' => $this->getLogin(),
            'updated_at' => new Expression('NOW()'),
            'user_acl_role_id' => $this->getUserAclRoleId(),
            'retrieve_password_key' => $this->getRetrievePasswordKey(),
            'retrieve_updated_at' => $this->getRetrieveUpdatedAt(),
        );

        $password = $this->getPassword();
        if (!empty($password)) {
            $array_save['password'] = $password;
        }

        try {
            $id = $this->getId();
            if (empty($id)) {
                $array_save['created_at'] = new Expression('NOW()');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($array_save, array('id' => $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'afterSave', null, array('object' => $this));

            return $this->getId();
        } catch (\Exception $e) {
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->events()->trigger(__CLASS__, 'afterSaveFailed', null, array('object' => $this));

        return false;
    }

    /**
     * Delete user
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', null, array('object' => $this));
        $id = $this->getId();
        if (!empty($id)) {
            try {
                parent::delete(array('id' => $id));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'afterDelete', null, array('object' => $this));
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'afterDeleteFailed', null, array('object' => $this));

        return false;
    }

    /**
     * Initiliaze from array
     *
     * @param array $array
     * @return \Gc\User\Model
     */
    public static function fromArray(array $array)
    {
        $user_table = new Model();
        $user_table->setData($array);
        $user_table->unsetData('password');
        $user_table->setOrigData();

        return $user_table;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $user_id
     * @return \Gc\User\Model
     */
    public static function fromId($user_id)
    {
        $user_table = new Model();
        $row = $user_table->fetchRow($user_table->select(array('id' => (int)$user_id)));
        if (!empty($row)) {
            $user_table->setData((array)$row);
            $user_table->unsetData('password');
            $user_table->setOrigData();
            return $user_table;
        } else {
            return false;
        }
    }

    /**
     * Get User Role
     *
     * @param boolean $force_reload
     * @return \Gc\User\Role\Model
     */
    public function getRole($force_reload = false)
    {
        $role = $this->getData('role');
        if (empty($role) or !empty($force_reload)) {
            $role = Role\Model::fromId($this->getUserAclRoleId());
            $this->setData('role', $role);
        }

        return $this->getData('role');
    }

    /**
     * Send new password
     *
     * @param string $email
     * @return boolean
     */
    public function sendForgotPasswordEmail($email)
    {
        $row = $this->fetchRow($this->select(array('email' => $email)));
        if (!empty($row)) {
            $user = self::fromArray((array)$row);
            $password_key = sha1(uniqid());
            $user->setRetrievePasswordKey($password_key);
            $user->setRetrieveUpdatedAt(new Expression('NOW()'));
            $user->save();

            $message = Registry::get('Translator')
                ->translate(
                    'To reset your password follow this link but be careful ' .
                    'you only have one hour before the link expires:'
                );
            $message .= '<br>';
            $message .= Registry::get('Application')->getMvcEvent()->getRouter()->assemble(
                array(
                    'id' => $user->getId(),
                    'key' => $password_key,
                ),
                array(
                    'force_canonical' => true,
                    'name' => 'userForgotPasswordKey'
                )
            );

            $mail = new Mail('utf-8', $message, Config::getValue('mail_from'), $user->getEmail());
            $mail->send();
            return true;
        }

        return false;
    }

    /**
     * Return user name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }
}
