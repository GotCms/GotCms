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
    Gc\Component\IterableInterface,
    Zend\Authentication\Adapter,
    Zend\Authentication\AuthenticationService;
/**
 * Model of user
 */
class Model extends AbstractTable implements IterableInterface
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'user';

    /**
     * Authenticate user
     * @param string $login
     * @param string $password
     * @return boolean
     */
    public function authenticate($login, $password)
    {
        $authAdapter = new Adapter\DbTable($this->getAdapter());
        $authAdapter->setTableName($this->_name);
        $authAdapter->setIdentityColumn('login');
        $authAdapter->setCredentialColumn('password');

        $authAdapter->setIdentity($login);
        $authAdapter->setCredential($password);

        $auth = new AuthenticationService();
        $result = $auth->authenticate($authAdapter);

        if($result->isValid())
        {
            $data = $authAdapter->getResultRowObject(null, 'password');
            $this->setData((array)$data);
            $auth->getStorage()->write($this);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Set User email
     * @param string $user_email
     * @return boolean
     */
    public function setEmail($user_email)
    {
        $value = trim($user_email);
        $validateur = new Zend_Validate_EmailAddress();
        if($validateur->isValid($user_email))
        {
            $select = $this->select()
                ->where('email = ?', $user_email);

            if($this->getId() != -1)
            {
                $select->where('id != ?', $this->getId());
            }

            $user = $this->fetchRow($select);
            if(count($user) == 0)
            {
                $this->_email = $user_email;
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Set user password
     * @param string $user_password
     * @param boolean $encrypt
     * @return void
     */
    public function setPassword($user_password, $encrypt = TRUE)
    {
        $this->_password = ($encrypt) ? sha1($user_password) : trim($user_password);
    }


    /**
     * Save user
     * @return integer
     */
    public function save()
    {
        $array_save = array(
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail(),
            'login' => $this->getLogin(),
            'updated_at' => date('Y-m-d H:i:s'),
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
                $array_save['created_at'] = date('Y-m-d H:i:s');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, 'id = '.$this->getId());
            }

            return $this->getId();
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make \Gc\Error)
            */
            \Gc\Error::set(get_class($this), $e);
        }

        return FALSE;
    }

    /**
     * Delete user
     * @return boolean
     */
    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            parent::delete('id = '.$id);
            unset($this);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Initiliaze from array
     * @param array $array
     * @return \Gc\User\Model
     */
    static function fromArray(array $array)
    {
        $user_table = new Model();
        $user_table->setData($array);

        return $user_table;
    }

    /**
     * Initiliaze from id
     * @param integer $id
     * @return \Gc\User\Model
     */
    static function fromId($id)
    {
        $user_table = new Model();
        $row = $user_table->select(array('id' => $id));
        $current = $row->current();
        if(!empty($current))
        {
            return $user_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Get User Role
     * @return \Gc\User\Role\Model
     */
    public function getRole()
    {
        $role = $this->getData('role');
        if(empty($role))
        {
            $role = Role\Model::fromId($this->getId());
            $this->setData('role', $role);
        }

        return $this->getData('role');
    }

    /**
     * Send new password
     * @param string $email
     * @return boolean
     */
    public function sendForgotPasswordEmail($email)
    {
        $row = $this->select(array('email' => $email));

        return TRUE;
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getParent()
     */
    public function getParent()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getChildren()
     */
    public function getChildren()
    {
        return array();
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getName()
     */
    public function getName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getId()
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getTreeViewId()
     */
    public function getIterableId()
    {
        return 'user_'.$this->getId();
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getUrl()
     */
    public function getUrl()
    {
        return '';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        return 'file';
    }

}
