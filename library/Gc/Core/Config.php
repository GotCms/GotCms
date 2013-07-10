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
use Zend\Db\Sql\Where;

/**
 * Get and set config data
 *
 * @category   Gc
 * @package    Library
 * @subpackage Core
 */
class Config extends AbstractTable
{
    /**
     * @const integer defined session from files
     */
    const SESSION_FILES = 0;

    /**
     * @const integer defined session from database
     */
    const SESSION_DATABASE = 1;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'core_config_data';

    /**
     * Get config value
     *
     * @param string $data  Data
     * @param string $field Optional database field, by default 'identifier'
     *
     * @return string value
     */
    public function getValue($data, $field = 'identifier')
    {
        $row = $this->fetchRow($this->select(array($field => $data)));
        if (!empty($row)) {
            return $row['value'];
        }

        return null;
    }

    /**
     * Return all values from core_config_data
     *
     * @return array
     */
    public function getValues()
    {
        $rows = $this->fetchAll($this->select());
        if (!empty($rows)) {
            return $rows;
        }

        return array();
    }

    /**
     * Set config value
     *
     * @param string $identifier Identifier
     * @param string $value      Value
     *
     * @return boolean
     */
    public function setValue($identifier, $value)
    {
        if (empty($identifier)) {
            return false;
        }

        $row = $this->fetchRow($this->select(array('identifier' => $identifier)));
        if (!empty($row)) {
            $where = new Where();
            return $this->update(array('value' => $value), $where->equalTo('identifier', $identifier));
        }

        return false;
    }
}
