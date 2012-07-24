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
 * @category Gc
 * @package  Datatype
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Datatypes\Textrich;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

class Editor extends AbstractEditor
{
    /**
     * Save textrich editor
     * @return void
     */
    public function save()
    {
        $value = $this->getRequest()->getPost()->get($this->getName());
        $this->setValue($value);
    }

    /**
     * load textrich editor
     * @return mixte
     */
    public function load()
    {
        $this->getHelper('headscript')->appendFile('/js/ckeditor/ckeditor.js', 'text/javascript');
        $this->getHelper('headscript')->appendFile('/js/ckeditor/ckeditor-adapters-jquery.js', 'text/javascript');


        $parameters = $this->getConfig();
        $ckeditor = new CkEditor();
        $ckeditor->setParameters($parameters);

        $id = 'textrich'.$this->_property->getId();
        $textrich = new Element\Textarea($this->getName());
        $textrich->setAttribute('label', $this->_property->getName());
        $textrich->setAttribute('id', $id);
        $textrich->setAttribute('value', $this->_property->getValue());

        $script = '<script type="text/javascript">
            $(function()
            {
                var config = {
                    skin: "v2",
                    toolbar: '.$ckeditor->getToolbarAsJs().'
                };

                $("#'.$id.'").ckeditor(config);
            });
        </script>';

        return array($textrich, $script);
    }
}
