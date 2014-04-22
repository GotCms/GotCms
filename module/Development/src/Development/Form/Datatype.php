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
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * Datatype form
 *
 * @category   Gc_Application
 * @package    Development
 * @subpackage Form
 */
class Datatype extends AbstractForm
{
    /**
     * Init Datatype form
     *
     * @return void
     */
    public function init()
    {
        $this->setAttribute('class', 'relative form-horizontal');
        $this->setAttribute('enctype', 'application/x-www-form-urlencoded');

        $name = new Element\Text('name');
        $name->setLabel('Name')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            );
        $name->setAttribute('class', 'form-control')
            ->setAttribute('id', 'name');
        $this->add($name);

        $model   = new Element\Select('model');
        $options = array();
        foreach ($this->getServiceLocator()->get('DatatypesList') as $dir) {
            $options[$dir] = $dir;
        }

        $model->setOptions(
            array(
                'label' => 'Model',
                'label_attributes' => array(
                    'class' => 'required control-label col-lg-2',
                )
            )
        );
        $model->setValueOptions($options)
            ->setAttribute('class', 'form-control');
        $this->add($model);

        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'name' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array(
                            'name' => 'db\\no_record_exists',
                            'options' => array(
                                'table' => 'datatype',
                                'field' => 'name',
                                'adapter' => $this->getAdapter(),
                            ),
                        ),
                    ),
                ),
                'model' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    )
                )
            )
        );

        $this->setInputFilter($inputFilter);
    }
}
