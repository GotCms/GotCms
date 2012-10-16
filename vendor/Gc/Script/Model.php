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
 * @subpackage  Script
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Script;

use Gc\Db\AbstractTable;
/**
 * Script Model
 */
class Model extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'script';

    /**
     * Initiliaze
     * @param integer $id
     * @return \Gc\Script\Model
     */
    public function init($id = NULL)
    {
        $this->setId($id);
    }

    /**
     * Initiliaze from array
     * @param array $array
     * @return \Gc\Script\Model
     */
    static function fromArray(array $array)
    {
        $script_table = new Model();
        $script_table->setData($array);

        return $script_table;
    }

    /**
     * Initiliaze from id
     * @param integer $id
     * @return \Gc\Script\Model
     */
    static function fromId($id)
    {
        $script_table = new Model();
        $row = $script_table->select(array('id' => $id));
        $current = $row->current();
        if(!empty($current))
        {
            return $script_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * Initiliaze from id
     * @param integer $identifier
     * @return \Gc\Script\Model
     */
    static function fromIdentifier($identifier)
    {
        $script_table = new Model();
        $row = $script_table->select(array('identifier' => $identifier));
        $current = $row->current();
        if(!empty($current))
        {
            return $script_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Save script model
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', NULL, array('object' => $this));
        $array_save = array(
            'name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
            'updated_at' => date('Y-m-d H:i:s'),
        );

        try
        {
            $id = $this->getId();
            if($this->getId() == NULL)
            {
                $array_save['created_at'] = date('Y-m-d H:i:s');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, 'id = '.(int)$this->getId());
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
     * Delete script model
     * @return boolean
     */
    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            if(parent::delete(sprintf('id = %d', $id)))
            {
                unset($this);
                return TRUE;
            }
        }

        return FALSE;
    }
}
