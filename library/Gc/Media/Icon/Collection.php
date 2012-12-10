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

namespace Gc\Media\Icon;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select;

/**
 * Collection of Media icons
 *
 * @category    Gc
 * @package     Library
 * @subpackage  View
 */
class Collection extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'icon';

    /**
     * Initiliaze collection
     * @return void
     */
    public function init()
    {
        $this->getIcons(TRUE);
    }

    /**
     * Get views
     * @param boolean $force_reload to initiliaze views
     * @return array
     */
    public function getIcons($force_reload = FALSE)
    {
        if($force_reload)
        {
            $rows = $this->select(function(Select $select)
            {
                $select->order('name');
            });
            $array = array();
            foreach($rows as $row)
            {
                $array[] = Model::fromId($row['id']);
            }

            $this->setData('icons', $array);
        }

        return $this->getData('icons');
    }

    /**
     * Get array for input select
     * @return array
     */
    public function getSelect()
    {
        $select = array();
        $icons = $this->getIcons();

        foreach($icons as $icon)
        {
            $select[$icon->getId()] = $icon->getName();
        }

        return $select;
    }
}
