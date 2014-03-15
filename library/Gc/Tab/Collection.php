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

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

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
    protected $name = 'tab';

    /**
     * Initiliaze tab collection
     *
     * @param integer $documentTypeId Optional document type id
     *
     * @return \Gc\Tab\Collection
     */
    public function load($documentTypeId = null)
    {
        $this->setDocumentTypeId($documentTypeId);

        return $this;
    }

    /**
     * Return all tabs from collection
     *
     * @param boolean $forceReload Force reload collection
     *
     * @return array
     */
    public function getTabs($forceReload = false)
    {
        $tabs           = $this->getData('tabs');
        $documentTypeId = $this->getDocumentTypeId();
        if (empty($tabs) or $forceReload == true) {
            if (!empty($documentTypeId)) {
                $rows = $this->fetchAll(
                    $this->select(
                        function (Select $select) use ($documentTypeId) {
                            $select->where->equalTo('document_type_id', $documentTypeId);
                            $select->order('sort_order ASC');
                        }
                    )
                );
            } else {
                $rows = $this->fetchAll($this->select());
            }

            $tabs = array();
            foreach ($rows as $row) {
                $tabs[] = Model::fromArray((array) $row);
            }

            $this->setData('tabs', $tabs);
        }

        return $this->getData('tabs');
    }

    /**
     * Return all tabs from collection
     *
     * @param integer $documentTypeId Document type id
     *
     * @return array
     */
    public function getImportableTabs($documentTypeId)
    {
        $rows = $this->fetchAll(
            $this->select(
                function (Select $select) use ($documentTypeId) {
                    $select->where->notEqualTo('document_type_id', $documentTypeId);
                }
            )
        );

        $tabs = array();
        foreach ($rows as $row) {
            $tabs[] = Model::fromArray((array) $row);
        }

        return $tabs;
    }

    /**
     * Set tabs
     *
     * @param array $tabs of \Gc\Tab\Model
     *
     * @return void
     */
    public function setTabs(array $tabs)
    {
        $array = array();
        foreach ($tabs as $tab) {
            $array[] = Model::fromArray($tab);
        }

        $this->setData('tabs', $array);
    }

    /**
     * Add tab from array
     *
     * @param array $array Data
     *
     * @return void
     */
    public function addTab(array $array)
    {
        $tabs   = $this->getTabs();
        $tabs[] = Model::fromArray($array);

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
        foreach ($tabs as $tab) {
            $tab->save();
        }
    }

    /**
     * Delete tabs
     *
     * @return boolean
     */
    public function delete()
    {
        $tabs = $this->getTabs();
        foreach ($tabs as $tab) {
            $tab->delete();
        }

        return true;
    }
}
