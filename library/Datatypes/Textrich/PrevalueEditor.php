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
 * @package    Datatypes
 * @subpackage Textrich
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Textrich;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;
use Zend\Form\Element;

/**
 * Prevalue Editor for Textrich datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage Textrich
 */
class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save Textrich prevalue editor
     *
     * @return void
     */
    public function save()
    {
        $toolbarItems = $this->getRequest()->getPost()->get('toolbar-items');
        $this->setConfig(array('toolbar-items' => $toolbarItems));
    }

    /**
     * Load Textrich prevalue editor
     *
     * @return mixed
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $ckeditor   = new CkEditor();
        if (empty($parameters) or !is_array($parameters)) {
            $parameters = array();
        }

        $ckeditor->setParameters($parameters);

        return $this->addPath(__DIR__)->render(
            'ckeditor-prevalue.phtml',
            array(
                'textrich' => $ckeditor->getAllItems()
            )
        );
    }
}
