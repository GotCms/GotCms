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
 * @subpackage  Component
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Component;

class TreeView
{
    protected $_data;

    /**
     * @param array $array
     */
    public function __construct(Array $treeview_data)
    {
        $this->_data = $treeview_data;
    }


    /**
     * @param array $tab contains objects
     * @return string
     */
    static function render(Array $treeview_data = NULL, $init = TRUE)
    {
        $html = '';
        if($init)
        {
            $html .= '<div id="browser">';
        }

        $html .= '<ul>';

        foreach($treeview_data as $iterator)
        {
            $children = $iterator->getChildren();
            $haveChildren = !empty($children);
            $html .= '<li id="'.$iterator->getIterableId().'"';

            if($haveChildren)
            {
                $rel = ' rel="folder"';
                $ins ='<ins class="jstree-icon">&nbsp;</ins>';
                $renderChildren = self::render($children, FALSE);
            }
            else
            {
                $renderChildren = '';
                $rel = ' rel="default"';
                $ins = '';
            }


            $html .= $rel . '>' . $ins;
            $id = $iterator->getId();
            $html .= '<a ' . (!empty($id) ?  'rel="' . $id . '" ' : '') . 'href="'.$iterator->getUrl().'">';

            if(in_array($iterator->getIcon(), array('folder','file')))
            {
                $html .= '<ins class="jstree-icon">&nbsp;</ins>';
            }
            else
            {
                $html .= '<ins style="background:url(medias/icon/'.$iterator->getIcon().') no-repeat scroll 0 0;padding-left:20px;">&nbsp;</ins>';
            }

            $html .= $iterator->getName().'</a>';

            $html .='</span>';
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
}
