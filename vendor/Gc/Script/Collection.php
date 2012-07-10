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
 * @subpackage  Script
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Script;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Zend\Db\Sql\Select;

class Collection extends AbstractTable implements IterableInterface
{
    /**
     * @var string
     */
    protected $_name = 'script';

    /**
     * Initiliaze collection
     * @param optional integer $document_type_id
     * @return void
     */
    public function init($document_type_id = NULL)
    {
        $this->setDocumentTypeId($document_type_id);
        $this->getScripts(TRUE);
    }

    /**
     * Get scripts
     * @param boolean $force_reload to initiliaze scripts
     * @return array
     */
    private function getScripts($force_reload = FALSE)
    {
        if($force_reload)
        {
            $select = new Select();
            $select->order(array('name'));
            $select->from('script');

            if($this->getDocumentTypeId() !== NULL)
            {
                $select->join('document_type_script', 'document_type_script.script_id = script.id');
                $select->where(sprintf('document_type_script.document_type_id = %s', $this->getDocumentTypeId()));
            }

            $rows = $this->fetchAll($select);
            $scripts = array();
            foreach($rows as $row)
            {
                $scripts[] = Model::fromArray((array)$row);
            }

            $this->setData('scripts', $scripts);
        }

        return $this->getData('scripts');
    }

    /**
     * get all elements store in $_scripts_elements
     * @return array
     */
    public function getElements()
    {
        return $this->_scripts_elements;
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
            foreach($this->getElements() as $script)
            {
                $this->getSqlInsert()->into('document_type_scripts')->values(array('document_type_id' => $this->getDocumentTypeId(), 'script_id' => $script->getId()));
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
            $this->getApdater()->delete('document_type_scripts', 'document_type_id = '.$this->getDocumentTypeId());
            return TRUE;
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
        return $this->getScripts();
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'scripts';
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return 'Scripts';
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
        return 'folder';
    }
}
