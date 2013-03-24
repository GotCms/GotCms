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
 * @subpackage Mixed
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Mixed;

use Gc\Datatype\AbstractDatatype as AbstractDatatype;
use Gc\Property\Model as PropertyModel;

/**
 * Manage Mixed datatype
 *
 * @category   Gc_Library
 * @package    Datatype
 * @subpackage Mixed
 */
class Datatype extends AbstractDatatype
{
    /**
     * Datatype name
     *
     * @var string
     */
    protected $name = 'mixed';

    /**
     * Retrieve editor
     *
     * @param PropertyModel $property Property
     *
     * @return \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    public function getEditor(PropertyModel $property)
    {
        $this->setProperty($property);
        if ($this->editor === null) {
            $this->editor = new Editor($this);
        }

        return $this->editor;
    }

    /**
     * Retrieve prevalue editor
     *
     * @return \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    public function getPrevalueEditor()
    {
        if ($this->prevalueEditor === null) {
            $this->prevalueEditor = new PrevalueEditor($this);
        }

        return $this->prevalueEditor;
    }
}
