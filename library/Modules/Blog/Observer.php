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
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Blog;

use Gc\Module\AbstractObserver,
    Zend\EventManager\Event;
/**
 * Blog module bootstrap
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog
 */
class Observer extends AbstractObserver
{
    /**
     * Boostrap
     *
     * @return void
     */
    public function init()
    {
        //Example of attach events
        $this->events()->attach('Front', 'preDispatch', array($this, 'onPreDispatch'), array('object' => $this));
    }

    /**
     * Example of preDispatch event
     *
     * @return void
     */
    public function onPreDispatch()
    {
    }
}
