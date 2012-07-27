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
 * @subpackage  Script
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Script;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Model extends AbstractTable implements IterableInterface
{
    /**
     * @var string
     */
    protected $_name = 'script';

    /**
     * Initiliaze
     * @param integer $id
     * @return \gc\Script\Model
     */
    public function init($id = NULL)
    {
        $this->setId($id);
    }

    /**
     * Initiliaze from array
     * @param array $script
     * @return \gc\Script\Model
     */
    static function fromArray(Array $array)
    {
        $script_table = new Model();
        $script_table->setData($array);

        return $script_table;
    }

    /**
     * Initiliaze from id
     * @param integer $id
     * @return \gc\Script\Model
     */
    static function fromId($id)
    {
        $script_table = new Model();
        $row = $script_table->select(array('id' => $id));
        $current = $row->current();
        if(!empty($current))
        {
            return $script_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * Initiliaze from id
     * @param integer $id
     * @return \gc\Script\Model
     */
    static function fromIdentifier($identifier)
    {
        $script_table = new Model();
        $row = $script_table->select(array('identifier' => $identifier));
        $current = $row->current();
        if(!empty($current))
        {
            return $script_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Save script model
     * @return integer
     */
    public function save()
    {
        $array_save = array(
            'name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
            'updated_at' => date('Y-m-d H:i:s'),
        );

        try
        {
            $id = $this->getId();
            if($this->getId() == NULL)
            {
                $array_save['created_at'] = date('Y-m-d H:i:s');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, 'id = '.(int)$this->getId());
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
     * Delete script model
     * @return boolean
     */
    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            if(parent::delete(sprintf('id = %d', $id)))
            {
                unset($this);
                return TRUE;
            }
        }

        return FALSE;
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getParent()
     */
    public function getParent()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getChildren()
     */
    public function getChildren()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return parent::getId();
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'script-'.$this->getId();
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return parent::getName();
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return '';
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        return 'file';
    }
}
