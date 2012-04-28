<?php

namespace Gc\User\Resource;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Expression,
    Zend\Db\Sql\Select;

class Model extends AbstractTable
{
    protected $_name = 'user_acl_resources';

    /**
    * @desc Save user
    */
    public function save()
    {
        $array_save = array(
            'resource' => $this->getResource()
        );

        try
        {
            if($this->getId() === NULL)
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
            * TODO(Make \Gc\Error)
            */
            \Gc\Error::set(get_class($this), $e);
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
    * @return Gc\User
    */
    static function fromArray(Array $array)
    {
        $resource_table = new Model();
        $resource_table->setData($array);

        return $resource_table;
    }

    /**
    * @param integer $id
    * @return Gc\User
    */
    static function fromId($id)
    {
        $resource_table = new Model();
        $row = $resource_table->select(array('id' => $id));
        if(!empty($row))
        {
            return $resource_table->setData((array)$row->current());
        }
        else
        {
            return FALSE;
        }
    }

    public function getPermissions()
    {

    }
}
