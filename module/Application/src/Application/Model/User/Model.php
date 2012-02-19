<?php

namespace Application\Model\User;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface;

class Model extends AbstractTable implements IterableInterface
{
    protected $_name = 'users';

    public function authenticate($email, $password)
    {
        $authAdapter = new Zend_Auth_Adapter_DbTable($this->getAdapter());
        $authAdapter->setTableName($this->_name);
        $authAdapter->setIdentityColumn('email');
        $authAdapter->setCredentialColumn('password');

        $authAdapter->setIdentity($email);
        //@TODO password with sha1
        $authAdapter->setCredential($password);

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);

        if($result->isValid())
        {
            $data = $authAdapter->getResultRowObject(null, 'password');
            $auth->getStorage()->write($data);

            $storage = $auth->getStorage();
            $storage->write($authAdapter->getResultRowObject(array('id')));
            $storage->write($authAdapter->getResultRowObject(null, 'password'));

            self::fromArray($data);
            return TRUE;
        }

        return FALSE;
    }

    /**
    * @param integer $defaultId
    */
    public function __construct($defaultId = -1)
    {
        $this->setId($defaultId);
    }

    /**
    * @param integer $user_id
    */
    private function setId($user_id)
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
        $u = new Es_Model_DbTable_User_Model($array['id']);
        $u->setFirstName($array['firstname']);
        $u->setLastName($array['lastname']);
        $u->setEmail($array['email']);
        $u->setDateCreated($array['date_created']);
        $u->setPassword($array['password'], FALSE);
        $u->setUserTypeId($array['user_type_id']);

        return $u;
    }

    /**
    * @param integer $id
    * @return Es_User
    */
    static function fromId($id)
    {
        $select = $this->select()
            ->where('id = ?', $id);
        $user = $this->fetchRow($select);
        if(!empty($user))
        {
            return self::fromArray($user);
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
        return $this->_id;
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
