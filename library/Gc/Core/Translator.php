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
 * @subpackage Core
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Core;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;

/**
 * Get and set translation
 *
 * @category   Gc
 * @package    Library
 * @subpackage Core
 */
class Translator extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'core_translate';

    /**
     * Get config value
     *
     * @param string $source Source
     * @param string $locale Optional locale
     *
     * @return string value
     */
    public function getValue($source, $locale = null)
    {
        $select = new Select();
        $select->from('core_translate')
            ->columns(array('src_id' => 'id', 'source'))
            ->join(
                'core_translate_locale',
                'core_translate.id = core_translate_locale.core_translate_id',
                array('dst_id' => 'id', 'destination', 'locale'),
                Select::JOIN_INNER
            );

        if (!empty($source)) {
            $select->where(array('core_translate.source' => $source));
        }

        if (!empty($locale)) {
            $select->where(array('core_translate_locale.locale' => $locale));
        }

        return $this->fetchRow($select);
    }

    /**
     * Return all values from core_config_data
     *
     * @param string  $locale Locale
     * @param integer $limit  Limit
     *
     * @return array
     */
    public function getValues($locale = null, $limit = null)
    {
        $select = new Select();
        $select->from('core_translate')
            ->columns(array('src_id' => 'id', 'source'))
            ->join(
                'core_translate_locale',
                'core_translate.id = core_translate_locale.core_translate_id',
                array('dst_id' => 'id', 'destination', 'locale'),
                Select::JOIN_INNER
            );

        if (!empty($locale)) {
            $select->where->equalTo('core_translate_locale.locale', $locale);
        }
        if (!empty($limit)) {
            $select->limit($limit);
        }

        $select->order('core_translate.source ASC');

        return $this->fetchAll($select);
    }

    /**
     * Set config value
     *
     * @param string $source       Source
     * @param array  $destinations Destinations
     *
     * @return boolean
     */
    public function setValue($source, array $destinations)
    {
        if (is_numeric($source)) {
            $row = $this->fetchRow($this->select(array('id' => $source)));
            if (empty($row)) {
                return false;
            }

            $sourceId = $row['id'];
        } else {
            $row = $this->fetchRow($this->select(array('source' => $source)));
            if (!empty($row)) {
                $sourceId = $row['id'];
            } else {
                $this->insert(array('source' => $source));
                $sourceId = $this->getLastInsertId();
            }
        }

        foreach ($destinations as $destination) {
            if (empty($destination['locale']) or empty($destination['value'])) {
                continue;
            }
            if (!empty($destination['dst_id'])) {
                $update = new Update('core_translate_locale');
                $update->set(
                    array(
                        'destination' => $destination['value'],
                        'locale' => $destination['locale']
                    )
                );
                $update->where->equalTo('id', $destination['dst_id']);

                $this->execute($update);
            } else {
                $insert = new Insert();
                $insert->into('core_translate_locale')
                    ->values(
                        array(
                            'destination' => $destination['value'],
                            'locale' => $destination['locale'],
                            'core_translate_id' => $sourceId,
                        )
                    );

                $this->execute($insert);
            }
        }

        return true;
    }

    /**
     * Generate php array file as cache
     *
     * @return void
     */
    public function generateCache()
    {
        $values     = $this->getValues();
        $data       = array();
        foreach ($values as $value) {
            if (empty($data[$value['locale']])) {
                $data[$value['locale']] = array();
            }

            $data[$value['locale']][$value['source']] = $value['destination'];
        }

        $translatePath   = GC_APPLICATION_PATH . '/data/translation/%s.php';
        $templateContent = file_get_contents(GC_APPLICATION_PATH . '/data/install/tpl/language.tpl.php');

        foreach ($data as $locale => $values) {
            file_put_contents(sprintf($translatePath, $locale), sprintf($templateContent, var_export($values, true)));
        }
    }
}
