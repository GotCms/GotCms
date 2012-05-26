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
 * @subpackage  DocumentType
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\DocumentType;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Gc\User,
    Gc\Tab,
    Gc\View;

class Model extends AbstractTable implements IterableInterface
{
    /**
     * @var string
     */
    protected $_name = 'document_type';

    /**
     * @var integer
     */
    protected $_views = array();

    /**
     * Get user model
     * @return \Gc\Model\user
     */
    public function getUser()
    {
        if($this->getData('user') === NULL AND $this->getUserId() != NULL)
        {
            $this->setData('user', new User\Model($this->getUserId()));
        }

        return $this->getData('user');
    }

    /**
     * Add view
     * @return \Gc\DocumentType\Model
     */
    public function addView($view_id)
    {
        $this->_views[] = $view_id;
        return $this;
    }

    /**
     * Add views
     * @return \Gc\DocumentType\Model
     */
    public function addViews($views)
    {
        if(!empty($views))
        {
            $this->_views += $views;
        }

        return $this;
    }

    /**
     * Get Tabs
     * @return \Gc\Tab\Collection
     */
    public function getTabs()
    {
        if($this->getData('tabs') === NULL )
        {
            $tabs_collection = new Tab\Collection();
            $tabs_collection->load($this->getId());

            $this->setData('tabs', $tabs_collection->getTabs());
        }

        return $this->getData('tabs');
    }

    /**
     * Get available views
     * @return array of \Gc\View\Collection
     */
    public function getAvailableViews()
    {
        if($this->getData('available_views') === NULL)
        {
            $views_collection = new View\Collection();
            $views_collection->init($this->getId());

            $this->setData('available_views', $views_collection);
        }

        return $this->getData('available_views');
    }

    /**
     * Save document type model
     * @return integer
     */
    public function save()
    {
        $array_save = array(
            'name' => $this->getName()
            , 'updated_at' => date('Y-m-d H:i:s')
            , 'description' => $this->getDescription()
            , 'icon_id' => $this->getIconId()
            , 'default_view_id' => $this->getDefaultViewId()
            , 'user_id' => $this->getUserId()
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $array_save['created_at'] = date('Y-m-d H:i:s');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, sprintf('id = %d', (int)$this->getId()));
            }

            $views = $this->getViews();
            if(!empty($views))
            {
                $db = $this->getAdapter();
                $db->delete('document_type_views', sprintf('document_type_id = %s', (int)$this->getId()));
                foreach($views as $view);
                {
                    $db->insert('document_type_views', array('document_type_id' => $this->getId(), 'view_id' => $view));
                }
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
     * delete document type model
     * @return boolean
     */
    public function delete()
    {
        $document_type_id = $this->getId();
        if(!empty($document_type_id))
        {
            $tab_collection = new Tab\Collection();
            $tab_collection->load($document_type_id);
            $tab_collection->delete();
            $table = new \Zend\Db\TableGateway\TableGateway('document_type_view', $this->getAdapter());
            $result = $table->delete(array('document_type_id' => (int)$document_type_id));
            parent::delete('id = '.$document_type_id);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param array $array
     * @return \Gc\DocumentType\Model
     */
    static function fromArray(Array $array)
    {
        $document_type_table = new Model();
        $document_type_table->setData($array);

        return $document_type_table;
    }

    /**
     * @param integer $document_type_id
     * @return \Gc\DocumentType\Model
     */
    static function fromId($document_type_id)
    {
        $document_type_table = new Model();
        $row = $document_type_table->select(array('id' => $document_type_id));
        if(!empty($row))
        {
            return $document_type_table->setData((array)$row->current());
        }
        else
        {
            return FALSE;
        }
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

    public function getId()
    {
        return parent::getId();
    }

    public function getName()
    {
        return parent::getName();
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'documenttype_'.$this->getId();
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
        if($this->_icon_url === NULL)
        {
            $icon = Gc_Media_Icon_Model::fromId($this->_documentType_icon);
            $this->_icon_url = $icon->getIconUrl();
        }

        return $this->_icon_url;
    }
}
