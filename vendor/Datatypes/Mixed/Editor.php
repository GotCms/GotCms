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
 * @category Gc
 * @package  Datatype
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Datatypes\Mixed;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element,
    Zend\Form\Fieldset;

/**
 * Editor for Mixed datatype
 */
class Editor extends AbstractEditor
{
    protected $_datatypes = array();
    /**
     * Save mixed editor
     * @return void
     */
    public function save()
    {
        $config = $this->getConfig();
        $config_datatypes = empty($config['datatypes']) ? array() : $config['datatypes'];

        $post = $this->getRequest()->getPost();
        $datatypes = $post->get($this->getName());
        if(!empty($datatypes))
        {
            foreach($datatypes as $line_id => $values)
            {
                $datatypes[$line_id] = array();
                foreach($values as $datatype_id => $datatype)
                {
                    foreach($datatype as $name => $value)
                    {
                        $post->set($name, $value);
                    }

                    //Get datatypes
                    $object = $this->_getDatatype($datatype['name']);
                    $editor = $object->getEditor($this->getProperty());
                    $editor->save();
                    $datatypes[$line_id][$datatype_id] = array(
                        'name' => $datatype['name'],
                        'value' => $editor->getValue()
                    );
                }
            }
        }

        $this->setValue(serialize($datatypes));
    }

    /**
     * Load mixed editor
     * @return mixte
     */
    public function load()
    {
        $config = $this->getConfig();
        $values = unserialize($this->getValue());

        $datatypes = empty($config['datatypes']) ? array() : $config['datatypes'];
        $datatypes_elements = array();
        $line_id = 0;
        if(!empty($values))
        {
            foreach($values as $line_id => $datatype_value)
            {
                foreach($datatype_value as $datatype_id => $value)
                {
                    if(empty($datatypes[$datatype_id]))
                    {
                        continue;
                    }

                    $datatype_config = $datatypes[$datatype_id];
                     //Get datatypes
                    $object = $this->_getDatatype($datatype_config['name']);
                    $editor = $object->getEditor($this->getProperty());
                    if(empty($values[$line_id][$datatype_id]))
                    {
                        $values[$line_id][$datatype_id] = array('value' => '');
                    }

                    $editor->setValue($values[$line_id][$datatype_id]['value']);

                    if(!empty($datatype_config['config']))
                    {
                        $editor->setConfig(serialize($datatype_config['config']));
                    }

                    //Initialize prefix
                    $prefix = $this->getName() . '['.$line_id.']['.$datatype_id.']';
                    //Create form
                    $fieldset = new Fieldset($datatype_config['name'] . $datatype_id);
                    $hidden = new Element\Hidden();
                    $hidden->setName($prefix. '[name]');
                    $hidden->setValue($datatype_config['name']);
                    $fieldset->add($hidden);

                    \Gc\Form\AbstractForm::addContent($fieldset, $editor->load(), $prefix);
                    $datatypes_elements[$line_id][$datatype_id]['label'] = empty($datatype_config['label']) ? '' : $datatype_config['label'];
                    $datatypes_elements[$line_id][$datatype_id]['fieldset'] = $fieldset;
                }
            }
        }

        //Defauts elements

        $template = array();
        foreach($datatypes as $datatype_id => $datatype_config)
        {
            $datatype_config = $datatypes[$datatype_id];
             //Get datatypes
            $object = $this->_getDatatype($datatype_config['name']);
            $editor = $object->getEditor($this->getProperty());
            if(empty($values['#{line}'][$datatype_id]))
            {
                $values['#{line}'][$datatype_id] = array('value' => '');
            }

            $editor->setValue($values['#{line}'][$datatype_id]['value']);

            if(!empty($datatype_config['config']))
            {
                $editor->setConfig(serialize($datatype_config['config']));
            }

            //Initialize prefix
            $prefix = $this->getName() . '['.'#{line}'.']['.$datatype_id.']';
            //Create form
            $fieldset = new Fieldset($datatype_config['name'] . $datatype_id);
            $hidden = new Element\Hidden();
            $hidden->setName($prefix. '[name]');
            $hidden->setValue($datatype_config['name']);
            $fieldset->add($hidden);

            \Gc\Form\AbstractForm::addContent($fieldset, $editor->load(), $prefix);
            $template[$datatype_id]['label'] = empty($datatype_config['label']) ? '' : $datatype_config['label'];
            $template[$datatype_id]['fieldset'] = $fieldset;
        }

        return $this->addPath(__DIR__)->render('mixed-editor.phtml', array(
            'datatypeName' => $this->getProperty()->getName(),
            'datatypes' => $datatypes_elements,
            'propertyName' => $this->getName(),
            'templateElements' => $template,
        ));
    }

    /**
     * Retrieve datatypes
     * @param string $name
     * @return \Gc\Datatype\AbstractDatatype
     */
    protected function _getDatatype($name)
    {
        if(!empty($this->_datatypes[$name]))
        {
            return $this->_datatypes[$name];
        }

        $class = 'Datatypes\\'.$name.'\Datatype';
        $object = new $class();
        $object->load($this->getDatatype(), $this->getDocumentId());
        $this->_datatypes[$name] = $object;
        return $this->_datatypes[$name];
    }
}
