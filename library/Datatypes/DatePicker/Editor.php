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
 * @subpackage DatePicker
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\DatePicker;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

/**
 * Editor for Date Picker datatype
 *
 * @category   Gc_Library
 * @package    Datatype
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
        $this->getHelper('headscript')->appendFile('/datatypes/DatePicker/jquery-ui-timepicker-addon.js', 'text/javascript');
        $this->getHelper('headscript')->appendFile('/datatypes/DatePicker/jquery-ui-sliderAccess.js', 'text/javascript');
        $id = 'datepicker' . $this->_property->getId();
        $datepicker = new Element\Text($this->getName());
        $datepicker->setAttribute('label', $this->getProperty()->getName())
            ->setValue($this->getValue())
            ->setAttribute('id', $id);

        $script = '<script type="text/javascript">
            $(function()
            {
                $("#' . $id . '").datetimepicker({
                    showOn: "button",
                    addSliderAccess: true,
                    sliderAccessArgs: { touchonly: false },
                    buttonImage: "/datatypes/DatePicker/calendar.gif",
                    buttonImageOnly: true,
                    timeFormat: "hh:mm:ss",
                    dateFormat: "yy/mm/dd"
                });
            });
        </script>';

        return array($datepicker, $script);
    }
}

