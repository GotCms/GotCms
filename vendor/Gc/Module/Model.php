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
 * @subpackage  Module
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Module;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Gc\ModuleType,
    Gc\Media\Icon,
    Gc\View;
/**
 * Module Model
 */
class Model extends AbstractTable implements IterableInterface
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'module';

    /**
     * Initiliaze module
     * @param integer $module_id
     * @return void
     */
    public function init($module_id = NULL)
    {
        if(!empty($module_id))
        {
            $this->setData('module_id', $module_id);
        }
    }

    /**
     * Initialize module from array
     * @param array $array
     * @return \Gc\Module\Model
     */
    static function fromArray(array $array)
    {
        $module_table = new Model();
        $module_table->setData($array);

        return $module_table;
    }

    /**
     * Initiliaze module from id
     * @param array $module_id
     * @return \Gc\Module\Model
     */
    static function fromId($module_id)
    {
        $module_table = new Model();
        $row = $module_table->select(array('id' => $module_id));
        $current = $row->current();
        if(!empty($current))
        {
            return $module_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Save Model
     * @return integer
     */
    public function save()
    {
        $array_save = array(
            'name' => $this->getName(),
        );

        try
        {
            $module_id = $this->getId();
            if(empty($module_id))
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
            \Gc\Error::set(get_class($this),$e);
        }

        return FALSE;
    }

    /**
     * Delete module
     * @return boolean
     */
    public function delete()
    {
        $module_id = $this->getId();
        if(!empty($module_id))
        {
            try
            {
                parent::delete('id = '.$this->getId());
            }
            catch (Exception $e)
            {
                \Gc\Error::set(get_class($this), $e);
            }
        }

        return FALSE;
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getParent()
     */
    public function getParent()
    {
        return NULL;
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getChildren()
     */
    public function getChildren()
    {
        return array();
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        return 'file';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'module_'.$this->getId();
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return \Gc\Registry::get('Application')->getMvcEvent()->getRouter()->assemble(array('id' => $this->getId()), array('name' => 'moduleEdit'));
    }
}
