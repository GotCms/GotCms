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
 * @subpackage Boolean
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Boolean;

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Zend\Form\Element;

/**
 * Editor for Boolean datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage Boolean
 */
class Editor extends AbstractEditor
{
    /**
     * Save Boolean editor
     *
     * @return void
     */
    public function save()
    {
        $value = $this->getRequest()->getPost()->get($this->getName());
        $this->setValue($value);
    }

    /**
     * Load Boolean editor
     *
     * @return Element\Checkbox
     */
    public function load()
    {
        $boolean = new Element\Checkbox($this->getName());
        $boolean->setLabel($this->getProperty()->getName());
        $boolean->setAttribute('class', 'input-checkbox');
        $boolean->setAttribute('id', 'boolean' . $this->getProperty()->getId());
        $boolean->setAttribute('description', $this->getProperty()->getDescription());
        $boolean->setValue($this->getProperty()->getValue());

        return $boolean;
    }
}
