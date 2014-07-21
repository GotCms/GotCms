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
 * @subpackage DocumentType
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\DocumentType;

use Gc\Db\AbstractTable;
use Gc\User;
use Gc\Tab;
use Gc\View;
use Zend\Db\Sql;
use Zend\Db\TableGateway\TableGateway;

/**
 * Model for Document Type
 *
 * @category   Gc
 * @package    Library
 * @subpackage DocumentType
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'document_type';

    /**
     * List of view id
     *
     * @var array
     */
    protected $views = array();

    /**
     * Get user model
     *
     * @return \Gc\User\Model
     */
    public function getUser()
    {
        if ($this->getData('user') === null and $this->getUserId() != null) {
            $this->setData('user', User\Model::fromId($this->getUserId()));
        }

        return $this->getData('user');
    }

    /**
     * Add view
     *
     * @param integer $viewId View id
     *
     * @return \Gc\DocumentType\Model
     */
    public function addView($viewId)
    {
        $this->views[] = $viewId;
        return $this;
    }

    /**
     * Add views
     *
     * @param array $views Views
     *
     * @return \Gc\DocumentType\Model
     */
    public function addViews(array $views)
    {
        if (!empty($views)) {
            $this->views += $views;
        }

        return $this;
    }

    /**
     * Get Tabs
     *
     * @return \Gc\Tab\Collection
     */
    public function getTabs()
    {
        if ($this->getData('tabs') === null) {
            $tabsCollection = new Tab\Collection();
            $tabsCollection->load($this->getId());

            $this->setData('tabs', $tabsCollection->getTabs());
        }

        return $this->getData('tabs');
    }

    /**
     * Get available views
     *
     * @return \Gc\View\Collection
     */
    public function getAvailableViews()
    {
        if ($this->getData('available_views') === null) {
            $viewsCollection = new View\Collection();
            $viewsCollection->init($this->getId());

            $this->setData('available_views', $viewsCollection);
        }

        return $this->getData('available_views');
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        $dependencies = $this->getData('dependencies');
        if (empty($dependencies)) {
            $select = new Sql\Select();
            $select->from(array('dtd' => 'document_type_dependency'))
                ->columns(array('children_id'))
                ->where->equalTo('parent_id', $this->getId());
            $rows = $this->fetchAll($select);

            $result = array();
            foreach ($rows as $row) {
                $result[] = $row['children_id'];
            }

            $this->setData('dependencies', $result);
        }

        return $this->getData('dependencies');
    }

    /**
     * Save document type model
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $this->setUpdatedAt(date('Y-m-d H:i:s'));
        $arraySave = array(
            'name' => $this->getName(),
            'updated_at'  => $this->getUpdatedAt(),
            'description' => $this->getDescription(),
            'icon_id' => $this->getIconId(),
            'default_view_id' => $this->getDefaultViewId(),
            'user_id' => $this->getUserId(),
        );

        try {
            $id = $this->getId();
            if (empty($id)) {
                $this->setCreatedAt($this->getUpdatedAt());
                $arraySave['created_at'] = $this->getCreatedAt();
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => (int) $this->getId()));
            }

            $delete = new Sql\Delete();
            $delete->from('document_type_view');
            $delete->where(array('document_type_id' => (int) $this->getId()));
            $this->execute($delete);
            foreach ($this->views as $viewId) {
                if (empty($viewId)) {
                    continue;
                }

                $insert = new Sql\Insert();
                $insert->into('document_type_view')
                    ->values(array('document_type_id' => $this->getId(), 'view_id' => $viewId));
                $this->execute($insert);
            }

            $delete = new Sql\Delete();
            $delete->from('document_type_dependency');
            $delete->where->equalTo('parent_id', (int) $this->getId());
            $this->execute($delete);
            $dependencies = $this->getDependencies();
            if (!empty($dependencies)) {
                foreach ($dependencies as $childrenId) {
                    $insert = new Sql\Insert();
                    $insert->into('document_type_dependency')
                        ->values(array('parent_id' => $this->getId(), 'children_id' => $childrenId));
                    $this->execute($insert);
                }
            }

            $this->events()->trigger(__CLASS__, 'after.save', $this);

            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete document type model
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $documentTypeId = $this->getId();
        if (!empty($documentTypeId)) {
            $tabCollection = new Tab\Collection();
            $tabCollection->load($documentTypeId);
            $tabCollection->delete();
            $table = new TableGateway('document_type_view', $this->getAdapter());
            $table->delete(array('document_type_id' => (int) $documentTypeId));
            parent::delete(array('id' => $documentTypeId));
            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    /**
     * Get model from array
     *
     * @param array $array Data
     *
     * @return \Gc\DocumentType\Model
     */
    public static function fromArray(array $array)
    {
        $documentTypeTable = new Model();
        $documentTypeTable->setData($array);
        $documentTypeTable->setOrigData();

        return $documentTypeTable;
    }

    /**
     * Get model from id
     *
     * @param integer $documentTypeId Document type id
     *
     * @return \Gc\DocumentType\Model
     */
    public static function fromId($documentTypeId)
    {
        $documentTypeTable = new Model();
        $row               = $documentTypeTable->fetchRow(
            $documentTypeTable->select(array('id' => (int) $documentTypeId))
        );
        $documentTypeTable->events()->trigger(__CLASS__, 'before.load', $documentTypeTable);
        if (!empty($row)) {
            $documentTypeTable->setData((array) $row);
            $documentTypeTable->setOrigData();
            $documentTypeTable->events()->trigger(__CLASS__, 'after.load', $documentTypeTable);
            return $documentTypeTable;
        } else {
            $documentTypeTable->events()->trigger(
                __CLASS__,
                'after.load.failed',
                $documentTypeTable
            );
            return false;
        }
    }
}
