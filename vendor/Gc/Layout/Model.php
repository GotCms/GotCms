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
 * @subpackage  Layout
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Layout;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Predicate\Expression;
/**
 * Layout Model
 */
class Model extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'layout';

    /**
     * Initiliaze layout
     * @param integer $id
     * @return \Gc\Layout\Model
     */
    public function init($id = NULL)
    {
        $this->setId($id);

        return $this;
    }

    /**
     * Initiliaze from array
     * @param array $array
     * @return \Gc\Layout\Model
     */
    static function fromArray(array $array)
    {
        $layout_table = new Model();
        $layout_table->setData($array);

        return $layout_table;
    }

    /**
     * Initiliaze from id
     * @param integer $id
     * @return \Gc\Layout\Model
     */
    static function fromId($id)
    {
        $layout_table = new Model();
        $row = $layout_table->select(array('id' => $id));
        $current = $row->current();
        if(!empty($current))
        {
            return $layout_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Initiliaze from identifier
     * @param string $identifier
     * @return \Gc\Layout\Model
     */
    static function fromIdentifier($identifier)
    {
        $layout_table = new Model();
        $row = $layout_table->select(array('identifier' => $identifier));
        $current = $row->current();
        if(!empty($current))
        {
            return $layout_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Save layout
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', NULL, array('object' => $this));
        $array_save = array('name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
            'updated_at' => new Expression('NOW()')
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $array_save['created_at'] = new Expression('NOW()');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, sprintf('id = %d', $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'afterSave', NULL, array('object' => $this));

            return $this->getId();
        }
        catch (Exception $e)
        {
            /**
             * TODO(Make \Gc\Error)
             */
            \Gc\Error::set(get_class($this), $e);
        }

        $this->events()->trigger(__CLASS__, 'afterSaveFailed', NULL, array('object' => $this));

        return FALSE;
    }

    /**
     * Delete layout
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', NULL, array('object' => $this));
        $id = $this->getId();
        if(!empty($id))
        {
            if(parent::delete('id = '.$id))
            {
                $this->events()->trigger(__CLASS__, 'afterDelete', NULL, array('object' => $this));
                unset($this);

                return TRUE;
            }
        }

        $this->events()->trigger(__CLASS__, 'afterDeleteFailed', NULL, array('object' => $this));

        return FALSE;
    }
}
