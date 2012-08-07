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
    Gc\Component\IterableInterface,
    Zend\Db\Sql\Select;
/**
 * Collection of Layout Model
 */
class Collection extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'layout';

    /**
     * Initiliaze collection
     * @return void
     */
    public function init()
    {
        $this->setLayouts();
    }

    /**
     * Set layout collection
     * @return \Gc\Layout\Collection
     */
    private function setLayouts()
    {
        $rows = $this->select(function (Select $select)
        {
            $select->order('name ASC');
        });

        //$select->order(array('name ASC'));
        $layout = array();
        foreach($rows as $row)
        {
            $layout[] = Model::fromArray((array)$row);
        }

        $this->setData('layouts', $layout);

        return $this;
    }

    /**
     * Return array for input select
     * @return array
     */
    public function getSelect()
    {
        $select = array();
        $layouts = $this->getLayouts();
        if(!is_array($layouts))
        {
            return $select;
        }

        foreach($layouts as $layout)
        {
            $select[$layout->getId()] = $layout->getName();
        }

        return $select;
    }
}
