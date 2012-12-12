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
 * @subpackage Tab
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Tab;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select;

/**
 * Collection of Tab Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Tab
 */
class Collection extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'tab';

    /**
     * Initiliaze tab collection
     *
     * @param integer $document_type_id Optional
     * @return \Gc\Tab\Collection
     */
    public function load($document_type_id = NULL)
    {
        $this->setDocumentTypeId($document_type_id);

        return $this;
    }

    /**
     * Return all tabs from collection
     *
     * @param boolean $force_reload to reload collection
     * @return array
     */
    public function getTabs($force_reload = FALSE)
    {
        $tabs = $this->getData('tabs');
        $document_type_id = $this->getDocumentTypeId();
        if(empty($tabs) or $force_reload == TRUE)
        {
            if(!empty($document_type_id))
            {
                $rows = $this->select(function(Select $select)
                {
                    $select->where->equalTo('document_type_id', $this->getDocumentTypeId());
                    $select->order('sort_order ASC');
                });
            }
            else
            {
                $rows = $this->select();
            }

            $tabs = array();
            foreach($rows as $row)
            {
                $tabs[] = Model::fromArray((array)$row);
            }

            $this->setData('tabs', $tabs);
        }

        return $this->getData('tabs');
    }

    /**
     * Return all tabs from collection
     *
     * @param integer $document_type_id
     * @return array
     */
    public function getImportableTabs($document_type_id)
    {
        $rows = $this->select(function(Select $select) use ($document_type_id)
        {
            $select->where->notEqualTo('document_type_id', $document_type_id);
        });

        $tabs = array();
        foreach($rows as $row)
        {
            $tabs[] = Model::fromArray((array)$row);
        }

        return $tabs;
    }

    /**
     * Set tabs
     *
     * @param array $tabs of \Gc\Tab\Model
     * @return void
     */
    public function setTabs(array $tabs)
    {
        $array = array();
        foreach($tabs as $tab)
        {
            $array[] = Model::fromArray($tab);
        }

        $this->setData('tabs', $array);
    }

    /**
     * Add tab from array
     *
     * @param array $tab
     * @return void
     */
    public function addTab(array $tab)
    {
        $tabs = $this->getTabs();
        $tabs[] = Model::fromArray($tab);

        $this->setData('tabs', $tabs);
    }

    /**
     * Save tabs
     *
     * @return void
     */
    public function save()
    {
        $tabs = $this->getTabs();
        foreach($tabs as $tab)
        {
            $tab->save();
        }
    }

    /**
     * Delete tabs
     *
     * @return void
     */
    public function delete()
    {
        $tabs = $this->getTabs();
        foreach($tabs as $tab)
        {
            $tab->delete();
        }

        return TRUE;
    }
}
