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
    Gc\Component\IterableInterface,
    Zend\Db\Sql\Select;

/**
 * Collection of View Model
 */
class Collection extends AbstractTable implements IterableInterface
{
    /**
     * List of \Gc\View\Model
     * @var array
     */
    protected $_views_elements = array();

    /**
     * Table name
     * @var string
     */
    protected $_name = 'view';

    /**
     * Initiliaze collection
     * @param optional integer $document_type_id
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
    private function getViews($force_reload = FALSE)
    {
        if($force_reload)
        {
            $select = new Select();
            $select->order(array('name'));
            $select->from('view');

            if($this->getDocumentTypeId() !== NULL)
            {
                $select->join('document_type_view', 'document_type_view.view_id = view.id');
                $select->where(sprintf('document_type_view.document_type_id = %s', $this->getDocumentTypeId()));
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
        if(!is_array($views))
        {
            return $select;
        }

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
        $this->_views_elements[] = $view;
        return $this;
    }

    /**
     * Clear elements
     * @return \Gc\View\Collection
     */
    public function clearElements()
    {
        $this->_views_elements = array();
        return $this;
    }

    /**
     * get all elements store in $_views_elements
     * @return array
     */
    public function getElements()
    {
        return $this->_views_elements;
    }

    /**
     * Save properties
     * @return boolean
     */
    public function save()
    {
        if(!empty($this->_data['document_type_id']))
        {
            $this->delete();
            foreach($this->getElements() as $view)
            {
                $this->getSqlInsert()->into('document_type_views')->values(array('document_type_id' => $this->getDocumentTypeId(), 'view_id' => $view->getId()));
            }

            return TRUE;
        }

        return FALSE;
    }

    /**
     * delete properties
     * @return boolean
     */
    public function delete()
    {
        if(!empty($this->_data['document_type_id']))
        {
            $this->getApdater()->delete('document_type_views', 'document_type_id = '.$this->getDocumentTypeId());
            return TRUE;
        }

        return FALSE;
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
        return $this->getViews();
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'views';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return 'Views';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return '';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        return 'folder';
    }
}
