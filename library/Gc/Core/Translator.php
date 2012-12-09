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
 * @subpackage  Core
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Core;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select,
    Zend\Db\Sql\Insert,
    Zend\Db\Sql\Update;
/**
 * Get and set translation
 */
class Translator extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'core_translate';

    /**
     * Singleton for Translator
     * @var \Gc\Core\Translator $_instance
     */
    static protected $_instance = NULL;

    /**
     * Get instance of \Gc\Core\Translator
     * @return \Gc\Core\Translator
     */
    public static function getInstance()
    {
        if(empty(static::$_instance))
        {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    /**
     * Get config value
     * @param string $source
     * @param optional string $locale
     * @return string value
     */
    static function getValue($source, $locale = NULL)
    {
        $instance = self::getInstance();
        $select = new Select();
        $select->from('core_translate')
            ->columns(array('src_id' => 'id', 'source'))
            ->join('core_translate_locale', 'core_translate.id = core_translate_locale.core_translate_id', array('dst_id' => 'id', 'destination', 'locale'), Select::JOIN_INNER);

        if(!empty($source))
        {
            $select->where(array('core_translate.source' => $source));
        }

        if(!empty($locale))
        {
            $select->where(array('core_translate_locale.locale' => $locale));
        }

        return $instance->fetchRow($select);
    }

    /**
     * Return all values from core_config_data
     * @param string $locale
     * @param integer $limit
     * @return array
     */
    static function getValues($locale = NULL, $limit = NULL)
    {
        $instance = self::getInstance();
        $select = new Select();
        $select->from('core_translate')
            ->columns(array('src_id' => 'id', 'source'))
            ->join('core_translate_locale', 'core_translate.id = core_translate_locale.core_translate_id', array('dst_id' => 'id', 'destination', 'locale'), Select::JOIN_INNER);

        if(!empty($locale))
        {
            $select->where->equalTo('core_translate_locale.locale', $locale);
        }
        if(!empty($limit))
        {
            $select->limit($limit);
        }

        $select->order('core_translate.source ASC');

        return $instance->fetchAll($select);
    }

    /**
     * Set config value
     * @param string $source
     * @param array $destinations
     * @return boolean
     */
    static function setValue($source, array $destinations)
    {
        $instance = self::getInstance();
        if(is_numeric($source))
        {
            $row = $instance->select(array('id' => $source))->current();
            if(empty($row))
            {
                return FALSE;
            }

            $source_id = $row->id;
        }
        else
        {
            $row = $instance->select(array('source' => $source))->current();
            if(!empty($row))
            {
                $source_id = $row->id;
            }
            else
            {
                $instance->insert(array('source' => $source));
                $source_id = $instance->getLastInsertId();
            }
        }

        foreach($destinations as $destination)
        {
            if(empty($destination['locale']) or empty($destination['value']))
            {
                continue;
            }
            if(!empty($destination['dst_id']))
            {
                $update = new Update('core_translate_locale');
                $update->set(array(
                    'destination' => $destination['value'],
                    'locale' => $destination['locale']
                ));
                $update->where->equalTo('id', $destination['dst_id']);

                $instance->execute($update);
            }
            else
            {
                $insert = new Insert();
                $insert->into('core_translate_locale')
                ->values(array(
                    'destination' => $destination['value'],
                    'locale' => $destination['locale'],
                    'core_translate_id' => $source_id,
                ));

                $instance->execute($insert);
            }
        }

        return TRUE;
    }
}
