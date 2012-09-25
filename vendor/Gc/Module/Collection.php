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
    Zend\Db\Sql\Select;
/**
 * Collection of Module Model
 */
class Collection extends AbstractTable implements IterableInterface
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'module';

    /**
     * Initialize collection
     * @return \Gc\Module\Collection
     */
    public function init()
    {
        $this->setModules();

        return $this;
    }

    /**
     * Initialize documents
     * @return \Gc\Module\Collection
     */
    private function setModules()
    {
        $rows = $this->select(function(Select $select)
        {
            $select->order('name ASC');
        });

        $modules = array();
        foreach($rows as $row)
        {
            $modules[] = Model::fromArray((array)$row);
        }

        $this->setData('modules', $modules);

        return $this;
    }

    /**
     * Return array for input select
     * @return array
     */
    public function getSelect()
    {
        $select = array();
        $modules = $this->getModules();
        if(!is_array($modules))
        {
            return $select;
        }

        foreach($modules as $module)
        {
            $select[$module->getId()] = $module->getName();
        }

        return $select;
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getParent()
     */
    public function getParent()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getChildren()
     */
    public function getChildren()
    {
        return $this->getModules();
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        return 'folder';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'modules';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return 'Modules';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return NULL;
    }
}
