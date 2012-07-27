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
 * @category Form
 * @package  Development
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Development\Form;

use Gc\Form\AbstractForm,
    Gc\Validator,
    Zend\Validator\Db,
    Zend\Form\Element,
    Zend\InputFilter\Factory as InputFilterFactory;

class Datatype extends AbstractForm
{
    /**
     * Init Datatype form
     * @return void
     */
    public function init()
    {

        $model  = new Element('model');

        $path = getcwd().'/vendor/Datatypes/';
        $list_dir = glob($path.'*', GLOB_ONLYDIR);
        $options = array();
        foreach($list_dir as $dir)
        {
            $dir = str_replace($path, '', $dir);
            $options[$dir] = $dir;
        }

        $model->setAttribute('options', $options);

        $this->add(new Element('name'));
        $this->add($model);

        $inputFilterFactory = new InputFilterFactory();
        $inputFilter = $inputFilterFactory->createInputFilter(array(
            'name' => array(
                'required'=> TRUE,
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
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                )
            )
        ));

        $this->setInputFilter($inputFilter);
    }
}
