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
 * @subpackage Mixed
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Mixed;

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Gc\Form\AbstractForm;
use Zend\Form\Element;
use Zend\Form\Fieldset;

/**
 * Editor for Mixed datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage Mixed
 */
class Editor extends AbstractEditor
{
    /**
     * List of Datatypes
     *
     * @var array
     */
    protected $datatypes = array();

    /**
     * Save mixed editor
     *
     * @return void
     */
    public function save()
    {
        $config          = $this->getConfig();
        $datatypesConfig = empty($config['datatypes']) ? array() : $config['datatypes'];

        $post      = $this->getRequest()->getPost();
        $oldPost   = $post->toArray();
        $datatypes = $post->get($this->getName());
        $oldFiles  = $_FILES;

        if (!empty($datatypes) and is_array($datatypes)) {
            foreach ($datatypes as $lineId => $values) {
                $datatypes[$lineId] = array();
                foreach ($values as $datatypeId => $datatype) {
                    if (!empty($oldFiles[$this->getName()]['name'][$lineId][$datatypeId])) {
                        $name = array_keys($oldFiles[$this->getName()]['name'][$lineId][$datatypeId]);
                        if (!empty($name[0])) {
                            $data = array(
                                'name'     => $oldFiles[$this->getName()]
                                    ['name'][$lineId][$datatypeId][$name[0]],
                                'type'     => $oldFiles[$this->getName()]
                                    ['type'][$lineId][$datatypeId][$name[0]],
                                'tmp_name' => $oldFiles[$this->getName()]
                                    ['tmp_name'][$lineId][$datatypeId][$name[0]],
                                'error'    => $oldFiles[$this->getName()]
                                    ['error'][$lineId][$datatypeId][$name[0]],
                                'error'    => $oldFiles[$this->getName()]
                                    ['error'][$lineId][$datatypeId][$name[0]],
                            );

                            $_FILES[$name[0]] = $data;
                            unset($_FILES[$this->getName()]);
                        }
                    }

                    foreach ($datatype as $name => $value) {
                        $post->set($name, $value);
                    }

                    //Get datatypes
                    $datatypeConfig = $datatypesConfig[$datatypeId];
                    $object         = $this->loadDatatype($datatypeConfig['name']);
                    $editor         = $object->getEditor($this->getProperty());

                    if (!empty($datatypeConfig['config'])) {
                        $editor->setConfig(serialize($datatypeConfig['config']));
                    }

                    $editor->save();
                    $datatypes[$lineId][$datatypeId] = array(
                        'value' => $editor->getValue()
                    );

                    foreach ($oldPost as $key => $value) {
                        $post->set($key, $value);
                    }

                    $_FILES = $oldFiles;
                }
            }
        }

        $this->setValue(serialize($datatypes));
    }

    /**
     * Load mixed editor
     *
     * @return mixed
     */
    public function load()
    {
        $config = $this->getConfig();
        $values = unserialize($this->getValue());

        $datatypes         = empty($config['datatypes']) ? array() : $config['datatypes'];
        $datatypesElements = array();
        $lineId            = 0;
        if (!empty($values)) {
            foreach ($values as $lineId => $datatypeValue) {
                foreach ($datatypeValue as $datatypeId => $value) {
                    if (empty($datatypes[$datatypeId])) {
                        continue;
                    }

                    $datatypeConfig = $datatypes[$datatypeId];
                     //Get datatypes
                    $object = $this->loadDatatype($datatypeConfig['name']);
                    $editor = $object->getEditor($this->getProperty());
                    if (empty($values[$lineId][$datatypeId])) {
                        $values[$lineId][$datatypeId] = array('value' => '');
                    }

                    $editor->setValue($values[$lineId][$datatypeId]['value']);

                    if (!empty($datatypeConfig['config'])) {
                        $editor->setConfig(serialize($datatypeConfig['config']));
                    }

                    //Initialize prefix
                    $prefix = $this->getName() . '[' . $lineId . '][' . $datatypeId . ']';
                    //Create form
                    $fieldset = new Fieldset($datatypeConfig['name'] . $datatypeId);

                    AbstractForm::addContent($fieldset, $editor->load(), $prefix);
                    $datatypesElements[$lineId][$datatypeId]['label']    = empty($datatypeConfig['label']) ?
                        '' :
                        $datatypeConfig['label'];
                    $datatypesElements[$lineId][$datatypeId]['fieldset'] = $fieldset;
                }
            }
        }

        //Defauts elements

        $template = array();
        foreach ($datatypes as $datatypeId => $datatypeConfig) {
            $datatypeConfig = $datatypes[$datatypeId];
             //Get datatypes
            $object = $this->loadDatatype($datatypeConfig['name']);
            $editor = $object->getEditor($this->getProperty());
            if (empty($values['#{line}'][$datatypeId])) {
                $values['#{line}'][$datatypeId] = array('value' => '');
            }

            $editor->setValue($values['#{line}'][$datatypeId]['value']);

            if (!empty($datatypeConfig['config'])) {
                $editor->setConfig(serialize($datatypeConfig['config']));
            }

            //Initialize prefix
            $prefix = $this->getName() . '[#{line}][' . $datatypeId . ']';
            //Create form
            $fieldset = new Fieldset($datatypeConfig['name'] . $datatypeId);
            $hidden   = new Element\Hidden();
            $hidden->setName($prefix . '[name]');
            $hidden->setValue($datatypeConfig['name']);
            $fieldset->add($hidden);

            AbstractForm::addContent($fieldset, $editor->load(), $prefix);
            $template[$datatypeId]['label']    = empty($datatypeConfig['label']) ? '' : $datatypeConfig['label'];
            $template[$datatypeId]['fieldset'] = $fieldset;
        }

        return $this->addPath(__DIR__)->render(
            'mixed-editor.phtml',
            array(
                'datatypeName' => $this->getProperty()->getName(),
                'datatypes' => $datatypesElements,
                'propertyName' => $this->getName(),
                'templateElements' => $template,
            )
        );
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
        if (!empty($this->datatypes[$name])) {
            return $this->datatypes[$name];
        }

        $class  = 'Datatypes\\' . $name . '\Datatype';
        $object = new $class();
        $object->setRequest($this->getRequest());
        $object->setHelperManager($this->getHelperManager());
        $object->setRouter($this->getRouter());
        $object->load($this->getDatatype()->getDatatypeModel(), $this->getProperty()->getDocumentId());
        $this->datatypes[$name] = $object;
        return $this->datatypes[$name];
    }
}
