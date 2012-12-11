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
 * @subpackage RadioButtonList
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\RadioButtonList;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

/**
 * Editor for Radio Button List datatype
 *
 * @category   Gc_Library
 * @package    Datatype
 * @subpackage RadioButtonList
 */
class Editor extends AbstractEditor
{
    /**
     * Save radiobuttonlist editor
     *
     * @return void
     */
    public function save()
    {
        $this->setValue($this->getRequest()->getPost()->get($this->getName()));
    }

    /**
     * Load radiobuttonlist editor
     *
     * @return mixed
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $element = new Element\Radio($this->getName());
        if(!empty($parameters))
        {
            $element->setValueOptions($parameters);
        }

        $element->setLabel($this->getName());
        $element->setAttribute('label', $this->getProperty()->getName());
        $element->setValue($this->getValue());

        return $element;
    }
}

