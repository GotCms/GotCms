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
 * @subpackage  Component
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Component;

interface IterableInterface {

    /**
     * Get Children
     *
     * @return array
     */
    public function getChildren();

    /**
     * Get Name
     *
     * @return string
     */
    public function getName();

    /**
     * Get Id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get Parent
     *
     * @return Object
     */
    public function getParent();

    /**
     * Get Url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get Iterable Id
     *
     * @return string
     */
    public function getIterableId();

    /**
     * Get Icon
     *
     * @return string
     */
    public function getIcon();
}
