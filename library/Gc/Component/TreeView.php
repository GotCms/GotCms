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
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Component;

use Gc\Component\IterableInterface;

/**
 * Create xml for jQuery treeview
 *
 * @category   Gc
 * @package    Library
 * @subpackage Component
 */
class TreeView
{

    /**
     * Treeview constructor
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Render treeview html
     *
     * @param array   $treeview_data Contains data as array
     * @param boolean $init          Initialize
     *
     * @return string
     */
    public static function render(array $treeview_data = null, $init = true)
    {
        $html = '';
        if ($init) {
            $html .= '<div id="browser">';
        }

        $html .= '<ul>';

        foreach ($treeview_data as $iterator) {
            if (!$iterator instanceof IterableInterface) {
                continue;
            }

            $children     = $iterator->getChildren();
            $has_children = !empty($children);
            $html        .= '<li id="' . $iterator->getIterableId() . '"';

            if ($has_children) {
                $rel             = ' class="folder"';
                $ins             = '<ins class="jstree-icon">&nbsp;</ins>';
                $render_children = self::render($children, false);
            } else {
                $render_children = '';
                $rel             = ' class="default"';
                $ins             = '';
            }


            $html        .= $rel . '>' . $ins;
            $id           = $iterator->getId();
            $is_published = null;
            if (method_exists($iterator, 'isPublished')) {
                $is_published = $iterator->isPublished();
            }

            $html .= '<a ' . (!empty($id) ?  'id="' . $id . '" ' : '')
                . 'href="' . $iterator->getEditUrl() . '"'
                . ($is_published === false ? ' class="not-published"' : '') . '>';

            if ($iterator->getIcon() == 'folder') {
                $html .= '<ins
                    style="background:url(/media/icons/folder.gif) no-repeat scroll 0 0;"
                    class="jstree-icon">&nbsp;</ins>';
            } else {
                $html .= '<ins
                    style="background:url(' . $iterator->getIcon() . ') no-repeat scroll 0 0;"
                    class="jstree-icon">&nbsp;</ins>';
            }

            $html .= $iterator->getName() . '</a>';
            $html .= $render_children;
            $html .= '</li>';
        }

        $html .= '</ul>';

        if ($init) {
            $html .= '</div>';
        }

        return $html;
    }
}
