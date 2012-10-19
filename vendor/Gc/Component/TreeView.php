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

use Gc\Component\IterableInterface;

/**
 * Create xml for jQuery treeview
 */
class TreeView
{
    /**
     * Data stored
     * @var array
     */
    protected $_data;

    /**
     * Treeview constructor
     * @param array $treeview_data
     */
    public function __construct(array $treeview_data = array())
    {
        $this->_data = $treeview_data;
    }

    /**
     * Render treeview html
     * @param array $treeview_data contains data as array
     * @param boolean $init
     * @return string
     */
    static function render(array $treeview_data = NULL, $init = TRUE)
    {
        $html = '';
        if($init)
        {
            $html .= '<div id="browser">';
        }

        $html .= '<ul>';

        foreach($treeview_data as $iterator)
        {
            if(!$iterator instanceof IterableInterface)
            {
                continue;
            }

            $children = $iterator->getChildren();
            $haveChildren = !empty($children);
            $html .= '<li id="'.$iterator->getIterableId().'"';

            if($haveChildren)
            {
                $rel = ' class="folder"';
                $ins ='<ins class="jstree-icon">&nbsp;</ins>';
                $renderChildren = self::render($children, FALSE);
            }
            else
            {
                $renderChildren = '';
                $rel = ' class="default"';
                $ins = '';
            }


            $html .= $rel . '>' . $ins;
            $id = $iterator->getId();
            $html .= '<a ' . (!empty($id) ?  'id="' . $id . '" ' : '') . 'href="'.$iterator->getEditUrl().'">';

            if($iterator->getIcon() == 'folder')
            {
                $html .= '<ins style="background:url(/media/icons/folder.gif) no-repeat scroll 0 0;" class="jstree-icon">&nbsp;</ins>';
            }
            else
            {
                $html .= '<ins style="background:url('.$iterator->getIcon().') no-repeat scroll 0 0;" class="jstree-icon">&nbsp;</ins>';
            }

            $html .= $iterator->getName().'</a>';
            $html .= $renderChildren;
            $html .='</li>';
        }

        $html .= '</ul>';

        if($init)
        {
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Render treeview html
     * @return string
     */
    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch(Exception $e)
        {
            trigger_error($e->getMessage(), E_USER_ERROR);
            return FALSE;
        }
    }

    /**
     * Set data
     * @param array $array
     * @return Tabs
     */
    public function setData(array $array)
    {
        $this->_data = $array;

        return $this;
    }
}
