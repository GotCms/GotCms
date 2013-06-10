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
     * @param string $login    Login
     * @param string $password Password
     *
     * @return boolean
     */
    public function authenticate($login, $password)
    {
        $authAdapter = new Adapter\DbTable($this->getAdapter());
        $authAdapter->setTableName($this->name);
        $authAdapter->setIdentityColumn('login');
        $authAdapter->setCredentialColumn('password');

        $authAdapter->setIdentity($login);
        $authAdapter->setCredential(sha1($password));

        $auth   = new AuthenticationService(new Storage\Session(self::BACKEND_AUTH_NAMESPACE));
        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()) {
            $data = $authAdapter->getResultRowObject(null, 'password');
            $this->setData((array) $data);
            $auth->getStorage()->write($this);

            return true;
        }

        return false;
    }

    /**
     * Set User email
     *
     * @param string $userEmail Email address
     *
     * @return boolean
     */
    public function setEmail($userEmail)
    {
        $userEmail = trim($userEmail);
        $validator = new EmailAddress();
        if ($validator->isValid($userEmail)) {
            $userId = $this->getId();
            $select = $this->select(
                function (Select $select) use ($userEmail, $userId) {
                    $select->where->equalTo('email', $userEmail);

                    if ($userId !== null) {
                        $select->where->notEqualTo('id', $userId);
                    }
                }
            );

            $row = $this->fetchRow($select);
            if (empty($row)) {
                $this->setData('email', $userEmail);
                return true;
            }
        }

        return false;
    }

    /**
     * Set user password
     *
     * @param string  $userPassword User password
     * @param boolean $encrypt      Encrypt or not the password
     *
     * @return void
     */
    public function setPassword($userPassword, $encrypt = true)
    {
        $this->setData('password', ($encrypt ? sha1(trim($userPassword)) : trim($userPassword)));
    }


    /**
     * Save user
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', null, array('object' => $this));
        $arraySave = array(
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
            $arraySave['password'] = $password;
        }

        try {
            $id = $this->getId();
            if (empty($id)) {
                $arraySave['created_at'] = new Expression('NOW()');
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $this->getId()));
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
     * @param array $array Data
     *
     * @return \Gc\User\Model
     */
    public static function fromArray(array $array)
    {
        $userTable = new Model();
        $userTable->setData($array);
        $userTable->unsetData('password');
        $userTable->setOrigData();

        return $userTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userId User id
     *
     * @return \Gc\User\Model
     */
    public static function fromId($userId)
    {
        $userTable = new Model();
        $row       = $userTable->fetchRow($userTable->select(array('id' => (int) $userId)));
        if (!empty($row)) {
            $userTable->setData((array) $row);
            $userTable->unsetData('password');
            $userTable->setOrigData();
            return $userTable;
        } else {
            return false;
        }
    }

    /**
     * Get User Role
     *
     * @param boolean $forceReload Force reload
     *
     * @return \Gc\User\Role\Model
     */
    public function getRole($forceReload = false)
    {
        $role = $this->getData('role');
        if (empty($role) or !empty($forceReload)) {
            $role = Role\Model::fromId($this->getUserAclRoleId());
            $this->setData('role', $role);
        }

        return $this->getData('role');
    }

    /**
     * Send new password
     *
     * @param string $email Email address
     *
     * @return boolean
     */
    public function sendForgotPasswordEmail($email)
    {
        $row = $this->fetchRow($this->select(array('email' => $email)));
        if (!empty($row)) {
            $user        = self::fromArray((array) $row);
            $passwordKey = sha1(uniqid());
            $user->setRetrievePasswordKey($passwordKey);
            $user->setRetrieveUpdatedAt(new Expression('NOW()'));
            $user->save();

            $message  = Registry::get('Application')->getServiceManager()->get('translator')
                ->translate(
                    'To reset your password follow this link but be careful ' .
                    'you only have one hour before the link expires:'
                );
            $message .= '<br>';
            $message .= Registry::get('Application')->getMvcEvent()->getRouter()->assemble(
                array(
                    'id'  => $user->getId(),
                    'key' => $passwordKey,
                ),
                array(
                    'force_canonical' => true,
                    'name'            => 'config/user/forgot-password-key'
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
