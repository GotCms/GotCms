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
 * @category   Gc_Application
 * @package    Development
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Development\Form;

use Gc\Form\AbstractForm;
use Zend\Validator\Db;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * Layout form
 *
 * @category   Gc_Application
 * @package    Development
 * @subpackage Form
 */
class Layout extends AbstractForm
{
    /**
     * Initialize form
     *
     * @return void
     */
    public function init()
    {
        $input_filter_factory = new InputFilterFactory();
        $input_filter = $input_filter_factory->createInputFilter(
            array(
                'name' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array(
                            'name' => 'db\\no_record_exists',
                            'options' => array(
                                'table' => 'layout',
                                'field' => 'name',
                                'adapter' => $this->getAdapter(),
                            ),
                        ),
                    ),
                ),
                'identifier' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array('name' => 'regex', 'options' => array(
                            'pattern' => parent::IDENTIFIER_PATTERN
                        )),
                        array(
                            'name' => 'db\\no_record_exists',
                            'options' => array(
                                'table' => 'layout',
                                'field' => 'identifier',
                                'adapter' => $this->getAdapter(),
                            ),
                        ),
                    ),
                ),
            )
        );

        $this->setInputFilter($input_filter);

        $this->add(new Element('name'));
        $this->add(new Element('identifier'));
        $this->add(new Element('description'));
        $this->add(new Element('content'));
    }
}
