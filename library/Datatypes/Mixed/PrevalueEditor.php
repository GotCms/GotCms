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

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;
use Gc\Form\AbstractForm;
use Zend\Form\Element;
use Zend\Form\Fieldset;

/**
 * Prevalue Editor for Mixed datatype
 *
 * @category   Gc_Library
 * @package    Datatype
 * @subpackage Mixed
 */
class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save mixed prevalue editor
     *
     * @return void
     */
    public function save()
    {
        $post      = $this->getRequest()->getPost();
        $datatypes = $post->get('datatypes');
        $model     = $post->get('add-model');
        if (!empty($datatypes)) {
            foreach ($datatypes as $datatype_id => $datatype) {
                foreach ($datatype as $name => $value) {
                    $post->set($name, $value);
                }

                //Get datatypes
                $object = $this->loadDatatype($datatype['name']);
                $object->getPrevalueEditor()->save();
                $datatypes[$datatype_id] = array(
                    'name' => $datatype['name'],
                    'label' => $datatype['label'],
                    'config' => $object->getPrevalueEditor()->getDatatype()->getConfig()
                );
            }
        }

        if (!empty($model)) {
            $datatypes[] = array('name' => $model);
        }


        $this->setConfig(array('datatypes' => $datatypes));
    }

    /**
     * Load mixed prevalue editor
     *
     * @return mixed
     */
    public function load()
    {
        $config = $this->getConfig();

        $path     = GC_APPLICATION_PATH . '/library/Datatypes/';
        $list_dir = glob($path . '*', GLOB_ONLYDIR);
        $options  = array();
        foreach ($list_dir as $dir) {
            $dir           = str_replace($path, '', $dir);
            $options[$dir] = $dir;
        }

        $datatypes = empty($config['datatypes']) ? array() : $config['datatypes'];
        foreach ($datatypes as $datatype_id => $datatype_config) {
            //Get datatypes
            $object = $this->loadDatatype($datatype_config['name']);
            //Force configuration
            $object->getPrevalueEditor()->setConfig(
                empty($datatype_config['config']) ? null : serialize($datatype_config['config'])
            );

            //Initiliaze prefix
            $prefix = 'datatypes[' . $datatype_id . ']';

            //Create form
            $fieldset = new Fieldset();
            $hidden   = new Element\Hidden();
            $hidden->setName($prefix . '[name]');
            $hidden->setValue($datatype_config['name']);
            $fieldset->add($hidden);
            $label = new Element\Text();
            $label->setName($prefix . '[label]');
            $label->setAttribute('class', 'input-text');
            $label->setAttribute('label', 'Label');
            $label->setAttribute('id', 'label' . $datatype_id);
            $label->setValue(empty($datatype_config['label']) ? '' : $datatype_config['label']);
            $fieldset->add($label);

            AbstractForm::addContent($fieldset, $object->getPrevalueEditor()->load(), $prefix);
            $datatypes[$datatype_id]['fieldset'] = $fieldset;
        }


        $data                 = array();
        $data['datatypes']    = $datatypes;
        $data['modelOptions'] = $options;

        return $this->addPath(__DIR__)->render('mixed-prevalue.phtml', $data);
    }

    /**
     * Retrieve datatypes
     *
     * @param string $name Datatype name
     *
     * @return \Gc\Datatype\AbstractDatatype
     */
    protected function loadDatatype($name)
    {
        $class  = 'Datatypes\\' . $name . '\Datatype';
        $object = new $class();
        $object->load($this->getDatatype(), $this->getDocumentId());
        return $object;
    }
}
