<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Datatype
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Datatype;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    /**
     * Collection of \Gc\Datatype\Model
     * @var array
     */
    protected $_datatypes;

    /**
     * @var string
     */
    protected $_name = 'datatype';

    /**
     * Initialize datatype collection
     * @return void
     */
    public function init()
    {
        $this->setDatatypes();
    }

    /**
     * Set datatype collection
     * @return \Gc\Datatype\Collection
     */
    private function setDatatypes()
    {
        $rows = $this->select();
        $datatypes = array();
        foreach($rows as $row)
        {
            $datatypes[] = Model::fromArray((array)$row);
        }

        $this->_datatypes = $datatypes;

        return $this;
    }

    /**
     * Get datatypes
     * @return array
     */
    public function getDatatypes()
    {
        return $this->_datatypes;
    }

    /**
     * Return array of datatypes for input select
     * @return array
     */
    public function getSelect()
    {
        $select = array();
        $datatypes = $this->getDatatypes();
        if(!is_array($datatypes))
        {
            return $select;
        }

        foreach($datatypes as $datatype)
        {
            $select[$datatype->getId()] = $datatype->getName();
        }

        return $select;
    }

    /*
     * Gc\Component\IterableInterfaces methods
     */
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
        return $this->getDatatypes();
    }
    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return FALSE;
    }


    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        return 'folder';
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'datatypes';
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return 'Datatypes';
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return '';
    }
}
