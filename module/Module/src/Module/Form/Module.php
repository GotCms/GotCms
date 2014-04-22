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
 * @package    Module
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Module\Form;

use Gc\Form\AbstractForm;
use Gc\Module\Collection as ModuleCollection;
use Gc\Media\Info;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * Module form
 *
 * @category   Gc_Application
 * @package    Module
 * @subpackage Form
 */
class Module extends AbstractForm
{
    /**
     * Init Module form
     *
     * @return void
     */
    public function init()
    {
        $this->setAttribute('class', 'relative');

        $fileInfo = new Info();

        $modulesInfos = array();
        $options      = array('' => 'Select an option');
        foreach ($this->getServiceLocator()->get('ModulesList') as $path => $dir) {
            $options[$dir] = $dir;

            $configFile = $path . '/module.info';
            if ($fileInfo->fromFile($configFile) === true) {
                $modulesInfos[$dir] = $fileInfo->render();
            }
        }

        $collection = new ModuleCollection();
        $modules    = $collection->getModules();
        foreach ($modules as $module) {
            if (in_array($module->getName(), $options)) {
                unset($options[$module->getName()]);
                unset($modulesInfos[$module->getName()]);
            }
        }

        $module = new Element\Select('module');
        $module->setLabel('Module')
            ->setLabelAttributes(
                array(
                    'class' => 'required'
                )
            );
        $module->setAttribute('id', 'module')
            ->setAttribute('class', 'form-control')
            ->setAttribute('modules_info', $modulesInfos)
            ->setValueOptions($options);
        $this->add($module);

        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'module' => array(
                    'name' => 'module',
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
