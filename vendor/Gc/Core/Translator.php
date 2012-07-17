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
 * @subpackage  Core
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Core;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select,
    Zend\Db\Sql\Insert;

class Translator extends AbstractTable
{
    /**
     * @var string
     */
    protected $_name = 'core_translate';

    /**
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
     * @param string $data
     * @param optional $field, database field, by default 'identifier'
     * @return string value
     */
    static function getValue($source, $locale = NULL)
    {
        $instance = self::getInstance();
        $select = new Select();
        $select->from('core_translate')
            ->columns(array('source'))
            ->join('core_translate_locale', 'core_translate.id = core_translate_locale.core_translate_id', '*', Select::JOIN_INNER);

        if(!empty($source))
        {
            $select->where(array('core_translate.source' => $source));
        }

        if(!empty($locale))
        {
            $select->where(array('core_translate_locale.locale' => $locale));
        }

        $current = $instance->fetchRow($select);
        if(!empty($current))
        {
        }

        return NULL;
    }

    /**
     * Return all values from core_config_data
     * @return array
     */
    static function getValues($locale)
    {
        $instance = self::getInstance();
        $rows = $instance->select(array('locale' => $locale));
        if(!empty($rows))
        {
            return $rows->toArray();
        }

        return NULL;
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
        $row = $instance->select(array('source' => $source))->current();
        if(!empty($row))
        {
            $id = $row->id;
        }
        else
        {
            $instance->insert(array('source' => $source));
            $id = $instance->getLastInsertId();
        }

        foreach($destinations as $destination)
        {
            if(empty($destination['locale']) or empty($destination['value']))
            {
                continue;
            }

            $insert = new Insert();
            $insert->into('core_translate_locale')
            ->columns(array(
                'destination',
                'locale',
                'core_translate_id'
            ))
            ->values(array(
                $destination['value'],
                $destination['locale'],
                $id,
            ));

            $instance->execute($insert);

        }

        return FALSE;
    }
}
