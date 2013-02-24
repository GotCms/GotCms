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
 * @package    Datatype
 * @subpackage Textrich
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Textrich;

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Zend\Form\Element;

/**
 * Editor for Textrich datatype
 *
 * @category   Gc_Library
 * @package    Datatype
 * @subpackage Textrich
 */
class Editor extends AbstractEditor
{
    /**
     * Save textrich editor
     *
     * @return void
     */
    public function save()
    {
        $value = $this->getRequest()->getPost()->get($this->getName());
        $this->setValue($value);
    }

    /**
     * Load textrich editor
     *
     * @return mixed
     */
    public function load()
    {
        $this->getHelper('headscript')->appendFile('/datatypes/Textrich/ckeditor.js', 'text/javascript');
        $this->getHelper('headscript')->appendFile(
            '/datatypes/Textrich/ckeditor-adapters-jquery.js',
            'text/javascript'
        );


        $parameters = $this->getConfig();
        $ckeditor = new CkEditor();

        if (empty($parameters) or !is_array($parameters)) {
            $parameters = array();
        }

        $ckeditor->setParameters($parameters);

        $id = 'textrich' . $this->getProperty()->getId();
        $textrich = new Element\Textarea($this->getName());
        $textrich->setAttribute('label', $this->getProperty()->getName());
        $textrich->setAttribute('id', $id);
        $textrich->setAttribute('class', $id);
        $textrich->setValue($this->getProperty()->getValue());

        $script = '<script type="text/javascript">
            $(function () {
                var config = {
                    skin: "v2",
                    toolbar: ' . $ckeditor->getToolbarAsJs() . '
                };

                $("#' . $id . '").ckeditor(config)
                .ckeditor(function () {
                    this.addCommand("saveDocument",
                    {
                        exec : function (editor, data) {
                            $("#input-save").click();
                        }
                    });
                    this.keystrokeHandler.keystrokes[CKEDITOR.CTRL + 83 /* S */] =  "saveDocument";
                });
            });
        </script>';

        return array($textrich, $script);
    }
}
