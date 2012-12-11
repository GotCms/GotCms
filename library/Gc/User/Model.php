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
 * @category    Gc
 * @package     Library
 * @subpackage  User
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\User;

use Gc\Db\AbstractTable,
    Zend\Authentication\Adapter,
    Zend\Authentication\AuthenticationService,
    Zend\Db\Sql\Predicate\Expression,
    Zend\Db\Sql\Select,
    Zend\Validator\EmailAddress;

/**
 * Model of user
 *
 * @category    Gc
 * @package     Library
 * @subpackage  User
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'user';

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
        $auth_adapter->setTableName($this->_name);
        $auth_adapter->setIdentityColumn('login');
        $auth_adapter->setCredentialColumn('password');

        $auth_adapter->setIdentity($login);
        $auth_adapter->setCredential(sha1($password));

        $auth = new AuthenticationService();
        $result = $auth->authenticate($auth_adapter);

        if($result->isValid())
        {
            $data = $auth_adapter->getResultRowObject(null, 'password');
            $this->setData((array)$data);
            $auth->getStorage()->write($this);

            return TRUE;
        }

        return FALSE;
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
        if($validator->isValid($user_email))
        {
            $select = $this->select(function(Select $select) use ($user_email)
            {
                $select->where->equalTo('email', $user_email);

                if($this->getId() != -1)
                {
                    $select->where->notEqualTo('id', $this->getId());
                }
            });

            $rows = $this->fetchAll($select);
            if(count($rows) == 0)
            {
                $this->setData('email', $user_email);
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Set user password
     *
     * @param string $user_password
     * @param boolean $encrypt
     * @return void
     */
    public function setPassword($user_password, $encrypt = TRUE)
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
        $this->events()->trigger(__CLASS__, 'beforeSave', NULL, array('object' => $this));
        $array_save = array(
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail(),
            'login' => $this->getLogin(),
            'updated_at' => new Expression('NOW()'),
            'user_acl_role_id' => $this->getUserAclRoleId(),
        );

        $password = $this->getPassword();
        if(!empty($password))
        {
            $array_save['password'] = $password;
        }

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $array_save['created_at'] = new Expression('NOW()');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, array('id' => $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'afterSave', NULL, array('object' => $this));

            return $this->getId();
        }
        catch(\Exception $e)
        {
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->events()->trigger(__CLASS__, 'afterSaveFailed', NULL, array('object' => $this));

        return FALSE;
    }

    /**
     * Delete user
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', NULL, array('object' => $this));
        $id = $this->getId();
        if(!empty($id))
        {
            try
            {
                parent::delete(array('id' => $id));
            }
            catch(\Exception $e)
            {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'afterDelete', NULL, array('object' => $this));
            unset($this);

            return TRUE;
        }

        $this->events()->trigger(__CLASS__, 'afterDeleteFailed', NULL, array('object' => $this));

        return FALSE;
    }

    /**
     * Initiliaze from array
     *
     * @param array $array
     * @return \Gc\User\Model
     */
    static function fromArray(array $array)
    {
        $user_table = new Model();
        $user_table->setData($array);
        $user_table->unsetData('password');

        return $user_table;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $user_id
     * @return \Gc\User\Model
     */
    static function fromId($user_id)
    {
        $user_table = new Model();
        $row = $user_table->select(array('id' => (int)$user_id));
        $current = $row->current();
        if(!empty($current))
        {
            $user_table->setData((array)$current);
            $user_table->unsetData('password');
            return $user_table;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Get User Role
     *
     * @param boolean $force_reload
     * @return \Gc\User\Role\Model
     */
    public function getRole($force_reload = FALSE)
    {
        $role = $this->getData('role');
        if(empty($role) or !empty($force_reload))
        {
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
        $row = $this->select(array('email' => $email));

        return TRUE;
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
