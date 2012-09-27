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

namespace Module\Form;

use Gc\Form\AbstractForm,
    Gc\Module\Collection as ModuleCollection,
    Gc\Media\Info,
    Zend\Form\Element,
    Zend\InputFilter\Factory as InputFilterFactory;

class Module extends AbstractForm
{
    /**
     * Init Module form
     * @return void
     */
    public function init()
    {
        $file_info = new Info();
        $path = GC_APPLICATION_PATH . '/vendor/Modules/';
        $list_dir = glob($path . '*', GLOB_ONLYDIR);

        $modules_infos = array();
        $options = array('' => 'Select an option');
        foreach($list_dir as $dir)
        {
            $dir = str_replace($path, '', $dir);
            $options[$dir] = $dir;

            $config_file = $path . $dir . '/module.info';
            if($file_info->fromFile($config_file) === TRUE)
            {
                $modules_infos[$dir] = $file_info->render();
            }
        }

        $collection = new ModuleCollection();
        $modules = $collection->getModules();
        foreach($modules as $module)
        {
            if(in_array($module->getName(), $options))
            {
                unset($options[$module->getName()]);
                unset($modules_infos[$module->getName()]);
            }
        }

        $module  = new Element\Select('module');
        $module->setAttribute('label', 'Module')
            ->setAttribute('id', 'module')
            ->setAttribute('modules_info', $modules_infos)
            ->setValueOptions($options);
        $this->add($module);

        $inputFilterFactory = new InputFilterFactory();
        $inputFilter = $inputFilterFactory->createInputFilter(array(
            'module' => array(
                'name' => 'module',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                )
            )
        ));

        $this->setInputFilter($inputFilter);
    }
}
