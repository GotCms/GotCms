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

    /**
     * Save mixed editor
     * @return void
     */
    public function save()
    {
        $post = $this->getRequest()->getPost();
        $datatypes = $post->get($this->getName());
        if(!empty($datatypes))
        {
            foreach($datatypes as $datatype_id => $datatype)
            {
                foreach($datatype as $name => $value)
                {
                    $post->set($name, $value);
                }

                //Get datatypes
                $object = $this->_getDatatype($datatype['name']);
                $editor = $object->getEditor($this->getProperty());
                $editor->save();
                $datatypes[$datatype_id] = array(
                    'name' => $datatype['name'],
                    'value' => $editor->getValue()
                );
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
        foreach($datatypes as $datatype_id => $datatype_config)
        {
            //Get datatypes
            $object = $this->_getDatatype($datatype_config['name']);
            $editor = $object->getEditor($this->getProperty());
            if(empty($values[$datatype_id]))
            {
                $values[$datatype_id] = array('value' => '');
            }

            $editor->setValue($values[$datatype_id]['value']);

            if(!empty($datatype_config['config']))
            {
                $editor->setConfig(serialize($datatype_config['config']));
            }

            //Initialize prefix
            $prefix = $this->getName() . '['.$datatype_id.']';

            //Create form
            $fieldset = new Fieldset($datatype_config['name'] . $datatype_id);
            $hidden = new Element\Hidden();
            $hidden->setName($prefix. '[name]');
            $hidden->setValue($datatype_config['name']);
            $fieldset->add($hidden);

            \Gc\Form\AbstractForm::addContent($fieldset, $editor->load(), $prefix);
            $datatypes[$datatype_id]['label'] = empty($datatype_config['label']) ? '' : $datatype_config['label'];
            $datatypes[$datatype_id]['fieldset'] = $fieldset;
        }

        return $this->addPath(__DIR__)->render('mixed-editor.phtml', array('datatypeName' => $this->getProperty()->getName(), 'datatypes' => $datatypes));
    }

    /**
     * Retrieve datatypes
     * @param string $name
     * @return \Gc\Datatype\AbstractDatatype
     */
    protected function _getDatatype($name)
    {
        $class = 'Datatypes\\'.$name.'\Datatype';
        $object = new $class();
        $object->load($this->getDatatype(), $this->getDocumentId());
        return $object;
    }
}
