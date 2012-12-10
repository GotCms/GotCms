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
 * @subpackage Textstring
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Textstring;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor,
    Zend\Form\Element;

/**
 * Prevalue Editor for Textstring datatype
 *
 * @category   Gc_Library
 * @package    Datatype
 * @subpackage Textstring
 */
class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save textstring prevalue editor
     * @return void
     */
    public function save()
    {
        $length = $this->getRequest()->getPost()->get('length');

        $this->setConfig(array('length' => $length));
    }

    /**
     * Load textstring prevalue editor
     * @return mixed
     */
    public function load()
    {
        $config = $this->getConfig();

        $length = new Element\Text('length');
        $length->setAttributes(array(
            'label' => 'Length',
            'value' => isset($config['length']) ? $config['length'] : '',
            'class' => 'input-text',
            'id' => 'length',
        ));

        return $length;
    }
}
