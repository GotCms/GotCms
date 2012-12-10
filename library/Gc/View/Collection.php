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
 * @subpackage  View
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\View;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select,
    Zend\Db\Sql\Insert,
    Zend\Db\TableGateway\TableGateway;

/**
 * Collection of View Model
 *
 * @category    Gc
 * @package     Library
 * @subpackage  View
 */
class Collection extends AbstractTable
{
    /**
     * List of \Gc\View\Model
     * @var array
     */
    protected $_viewsElements = array();

    /**
     * Table name
     * @var string
     */
    protected $_name = 'view';

    /**
     * Initiliaze collection
     * @param integer $document_type_id Optional
     * @return void
     */
    public function init($document_type_id = NULL)
    {
        $this->setDocumentTypeId($document_type_id);
        $this->getViews(TRUE);
    }

    /**
     * Get views
     * @param boolean $force_reload to initiliaze views
     * @return array
     */
    public function getViews($force_reload = FALSE)
    {
        if($force_reload)
        {
            $select = new Select();
            $select->order(array('name'));
            $select->from('view');

            if($this->getDocumentTypeId() !== NULL)
            {
                $select->join('document_type_view', 'document_type_view.view_id = view.id', array());
                $select->where->equalTo('document_type_view.document_type_id', $this->getDocumentTypeId());
            }

            $rows = $this->fetchAll($select);
            $views = array();
            foreach($rows as $row)
            {
                $views[] = Model::fromArray((array)$row);
            }

            $this->setData('views', $views);
        }

        return $this->getData('views');
    }

    /**
     * Get array for input select
     * @return array
     */
    public function getSelect()
    {
        $select = array();
        $views = $this->getViews();

        foreach($views as $view)
        {
            $select[$view->getId()] = $view->getName();
        }

        return $select;
    }

    /**
     * Add view
     * @param Model $view
     */
    public function addElement(Model $view)
    {
        $this->_viewsElements[] = $view;
        return $this;
    }

    /**
     * Clear elements
     * @return \Gc\View\Collection
     */
    public function clearElements()
    {
        $this->_viewsElements = array();
        return $this;
    }

    /**
     * get all elements store in $_viewsElements
     * @return array
     */
    public function getElements()
    {
        return $this->_viewsElements;
    }

    /**
     * Save properties
     * @return boolean
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', NULL, array('object' => $this));
        if(!empty($this->_data['document_type_id']))
        {
            $this->delete();

            $insert = new Insert();
            $insert->into('document_type_view');

            foreach($this->getElements() as $view)
            {
                $insert->values(array('document_type_id' => $this->getDocumentTypeId(), 'view_id' => $view->getId()));
                $this->execute($insert);
            }

            $this->events()->trigger(__CLASS__, 'afterSave', NULL, array('object' => $this));

            return TRUE;
        }

        $this->events()->trigger(__CLASS__, 'afterSaveFailed', NULL, array('object' => $this));

        return FALSE;
    }

    /**
     * delete properties
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', NULL, array('object' => $this));
        if(!empty($this->_data['document_type_id']))
        {
            $table = new TableGateway('document_type_view', $this->getAdapter());
            $table->delete(array('document_type_id' => $this->getDocumentTypeId()));
            $this->events()->trigger(__CLASS__, 'afterDelete', NULL, array('object' => $this));

            return TRUE;
        }

        $this->events()->trigger(__CLASS__, 'afterDeleteFailed', NULL, array('object' => $this));

        return FALSE;
    }
}
