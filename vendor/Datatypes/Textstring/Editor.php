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

namespace Datatypes\Textstring;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

class Editor extends AbstractEditor
{
    /**
     * Save textstring editor
     * @return mixte
     */
    public function save()
    {
        $value = $this->getRequest()->getPost()->get($this->getName());
        $this->setValue($value);
    }

    /**
     * Load textstring editor
     * @return mixte
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $property = $this->getProperty();
        $textstring = new Element\Text($this->getName());
        $textstring->setAttribute('label', $property->getName());
        $textstring->setValue($this->getValue());
        if(!empty($parameters['length']))
        {
            $textstring->setAttribute('maxlength', $parameters['length']);
        }

        return $textstring;
    }
}
