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
 * @subpackage Component
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Component;

/**
 * Create html for jQuery Ui Tabs
 *
 * @category   Gc
 * @package    Library
 * @subpackage Component
 */
class Tabs
{
    /**
     * Data stored
     *
     * @var array
     */
    protected $data;

    /**
     * Tabs constructor
     *
     * @param array $array Data
     */
    public function __construct(array $array = array())
    {
        $this->data = $array;
    }

    /**
     * Render tab
     *
     * @param array $tabs Contains objects
     *
     * @return string
     */
    public function render(array $tabs = null)
    {
        if ($tabs === null) {
            $tabs = $this->data;
        }

        $html = '<ul>';
        $i    = 1;
        foreach ($tabs as $iterator) {
            if (!$iterator instanceof IterableInterface) {
                $html .= '<li><a href="#tabs-' . $i . '">' . $iterator . '</a></li>';
            } else {
                $html .= '<li><a href="#tabs-' . $iterator->getId() . '">' . $iterator->getName() . '</a></li>';
            }

            $i++;
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Render tab
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->data)) {
            return false;
        }

        return $this->render();
    }

    /**
     * Set data
     *
     * @param array $array Data
     *
     * @return Tabs
     */
    public function setData(array $array)
    {
        $this->data = $array;

        return $this;
    }
}
