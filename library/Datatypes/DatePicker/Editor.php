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
 * @subpackage DatePicker
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\DatePicker;

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Zend\Form\Element;

/**
 * Editor for Date Picker datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage DatePicker
 */
class Editor extends AbstractEditor
{
    /**
     * Save datepicker editor
     *
     * @return void
     */
    public function save()
    {
        $this->setValue($this->getRequest()->getPost()->get($this->getName()));
    }

    /**
     * Load datepicker editor
     *
     * @return mixed
     */
    public function load()
    {
        $this->getHelper('headlink')->appendStylesheet(
            '/datatypes/DatePicker/css/bootstrap-datetimepicker.min.css'
        );
        $this->getHelper('headscript')->appendFile(
            '/datatypes/DatePicker/js/moment.min.js',
            'text/javascript'
        );
        $this->getHelper('headscript')->appendFile(
            '/datatypes/DatePicker/js/bootstrap-datetimepicker.min.js',
            'text/javascript'
        );
        $id         = 'datepicker' . $this->getProperty()->getId();
        $datepicker = new Element\Text($this->getName());
        $datepicker->setLabel($this->getProperty()->getName())
            ->setAttribute('description', $this->getProperty()->getDescription())
            ->setAttribute('class', 'form-control')
            ->setAttribute('required', $this->getProperty()->isRequired())
            ->setValue($this->getValue());

        return $this->addPath(__DIR__)->render(
            'datepicker-editor.phtml',
            array(
                'id' => $id,
                'element' => $datepicker,
            )
        );
    }
}
