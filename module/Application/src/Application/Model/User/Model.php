<?php

namespace Application\Model\User;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface,
    Zend\Authentication\Adapter,
    Zend\Authentication\AuthenticationService;

class Model extends AbstractTable implements IterableInterface
{
    protected $_name = 'users';

    public function authenticate($email, $password)
    {
        $authAdapter = new Adapter\DbTable($this->getAdapter());
        $authAdapter->setTableName($this->_name);
        $authAdapter->setIdentityColumn('email');
        $authAdapter->setCredentialColumn('password');

        $authAdapter->setIdentity($email);
        $authAdapter->setCredential($password);

        $auth = new AuthenticationService();
        $result = $auth->authenticate($authAdapter);

        if($result->isValid())
        {
            $data = $authAdapter->getResultRowObject(null, 'password');
            $auth->getStorage()->write($data);

            $this->setData((array)$data);
            return TRUE;
        }

        return FALSE;
    }

    /**
    * @param integer $user_id
    */
    protected function setId($user_id)
    {
        $this->setData('id', (int) $user_id);
    }

    /**
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
    * @param string $user_password
    * @param boolean $encrypt
    */
    public function setPassword($user_password,$encrypt = TRUE)
    {
        $this->_password = ($encrypt) ? sha1($user_password) : trim($user_password);
    }


    /**
    * @desc Save user
    */
    public function save()
    {
        $array_save = array(
            'firstname'=>$this->getFirstName()
            , 'lastname'=>$this->getLastName()
            , 'email'=>$this->getEmail()
            , 'date_created'=>$this->getDateCreated()
            , 'password'=>$this->getPassword()
            , 'user_type_id'=>$this->getUserTypeId()
        );

        try
        {
            if($this->getId() == -1)
            {
                $this->insert($array_save);
            }
            else
            {
                $this->update($array_save, 'id = '.$this->getId());
            }

            return TRUE;
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make Es_Error)
            */
            Es_Error::set(get_class($this), $e);
        }

        return FALSE;
    }

    /**
    * @desc Delete user
    */
    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            parent::delete('id = '.$this->getId());
            unset($this);
            return TRUE;
        }

        return FALSE;
    }

    /**
    * @param array $array
    * @return Es_User
    */
    static function fromArray(Array $array)
    {
        $user_table = new Model();
        $user_table->setData($array);

        return $u;
    }

    /**
    * @param integer $id
    * @return Es_User
    */
    static function fromId($id)
    {
        $user_table = new Model();
        $select = $user_table->select()
            ->where('id = ?', $id);
        $row = $user_table->fetchRow($select);
        if(!empty($row))
        {
            return $user_table->setData($row->toArray());
        }
        else
        {
            return FALSE;
        }
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Es_Interfaces_Iterable#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Es_Interfaces_Iterable#getChildren()
    */
    public function getChildren()
    {
        return array();
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Es_Interfaces_Iterable#getName()
    */
    public function getName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Es_Interfaces_Iterable#getId()
    */
    public function getId()
    {
        return $this->getData('id');
    }

    /* (non-PHPdoc)
    * @see include/Es/Interfaces/Es_Interfaces_Iterable#getTreeViewId()
    */
    public function getIterableId()
    {
        return 'user_'.$this->getId();
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Es_Interfaces_Iterable#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'config','action'=>'edit')).'/type/user/id/'.$this->getId().'\')';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        return 'file';
    }

}
