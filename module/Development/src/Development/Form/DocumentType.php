<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
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
    Gc\DocumentType\Model as DocumentTypeModel,
    Gc\Datatype,
    Gc\Validator,
    Gc\View,
    Gc\Tab,
    Gc\Property,
    Zend\Validator\Db,
    Zend\Form\Element,
    Zend\InputFilter\InputFilter,
    Zend\Form\FieldSet;

class DocumentType extends AbstractForm
{
    /**
     * @var \Gc\View\Collection
     */
     protected $_viewCollection;

     /**
      * @var \Gc\Datatype\Collection
      */
     protected $_datatypeCollection;

    /**
     * Init document type form
     */
    public function init()
    {
        $this->setInputFilter(new InputFilter());
        $this->_datatypeCollection = new Datatype\Collection();
        $this->_viewCollection = new View\Collection();

        $this->getInfos();
        $this->getViews();
        $this->getProperties();
        $this->getTabs();

        $this->setInputFilter($this->getInputFilter());
    }

    /**
     * Initialize infos sub form
     * @return \Zend\Form\FieldSet
     */
    private function getInfos()
    {
        $fieldsets = $this->getFieldSets();
        if(!empty($fieldsets['infos']))
        {
            return $fieldsets['infos'];
        }

        $fieldsets = new FieldSet('infos');

        $this->getInputFilter()->add(array(
            'type'   => 'Zend\InputFilter\InputFilter',
            'name' => array(
                'name' => 'name',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array(
                        'name' => 'db\\no_record_exists',
                        'options' => array(
                            'table' => 'document_type',
                            'field' => 'name',
                            'adapter' => $this->getAdapter(),
                        ),
                    ),
                ),
            ),
            'description' => array(
                'name' => 'description',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty')
                ),
            ),
            'icon_id' => array(
                'name' => 'icon_id',
                'required' => FALSE,
                'allow_empty' => FALSE,
            ),
        ), 'infos');


        $fieldsets->add(new Element('name'));
        $fieldsets->add(new Element('description'));
        $fieldsets->add(new Element('icon_id'));

        $this->add($fieldsets);

        return $fieldsets;
    }

    /**
     * Initialize views sub form
     * @return \Zend\Form\FieldSet
     */
    private function getViews()
    {
        $fieldsets = $this->getFieldSets();
        if(!empty($fieldsets['views']))
        {
            return $fieldsets['views'];
        }

        $fieldsets = new FieldSet('views');

        $available_views = new Element('available_views');

        $default_view = new Element('default_view');
        $default_view->setAttribute('options', $this->_viewCollection->getSelect());
        $fieldsets->add($default_view);

        $available_views->setAttribute('options', $this->_viewCollection->getSelect());
        $fieldsets->add($available_views);

        $this->add($fieldsets);

        $this->getInputFilter()->add(array(
            'type'   => 'Zend\InputFilter\InputFilter',
            'default_view' => array(
                'name' => 'default_view',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty')
                ),
            ),
            'available_views' => array(
                'name' => 'available_views',
                'required' => FALSE,
                'allow_empty' => FALSE,
            ),
        ), 'views');

        return $fieldsets;
    }

    /**
     * Initialize properties sub form
     * @return \Zend\Form\FieldSet
     */
    private function getProperties()
    {
        $fieldsets = $this->getFieldSets();
        if(!empty($fieldsets['properties']))
        {
            return $fieldsets['properties'];
        }

        $fieldsets = new FieldSet('properties');

        $datatypes = new Element('datatypes');
        $datatypes->setAttribute('options', $this->_datatypeCollection->getSelect())
            ->setAttribute('type', 'select');

        $fieldsets->add($datatypes);

        $this->add($fieldsets);
        $this->getInputFilter()->add(array('type'   => 'Zend\InputFilter\InputFilter'), 'properties');

        return $fieldsets;
    }

    /**
     * Add property sub form
     * @param mixed \Gc\Property\Model | array
     * @return \Developpement\Form\DocumentType
     */
    public function addProperty($property)
    {
        if(!is_array($property) and !$property instanceof Property\Model)
        {
            return $this;
        }

        $fieldsets = $this->getProperties();

        $name = new Element\Text('name');

        $identifier = new Element('identifier');

        $tab = new Element\Select('tab');
        $tab->setAttribute('class', 'select-tab')
            ->setAttribute('options', array());

        $datatype = new Element\Select('datatype');
        $datatype->setAttribute('class', 'select-datatype')
            ->setAttribute('options', $this->_datatypeCollection->getSelect());

        $description = new Element\Text('description');
        $required = new Element\Checkbox('required');
        $required->setValue(1);
        $property_id = new Element\Hidden('property_id');

        if($property instanceof Property\Model)
        {
            $name->setValue($property->getName());
            $identifier->setValue($property->getIdentifier());
            $tab->setValue($property->getTabId());
            $datatype->setValue($property->getDatatypeId());
            $description->setValue($property->getDescription());
            $required->setAttribute('checkedValue', $property->isRequired());
            $property_id->setValue($property->getId());
            $property_fieldset_name = $property->getId();
        }
        elseif(is_array($property))
        {
            $name->setValue($property['name']);
            $identifier->setValue($property['identifier']);
            $tab->setValue($property['tab']);
            $datatype->setValue($property['datatype']);
            $description->setValue($property['description']);
            $required->setAttribute('checkedValue', !empty($property['is_required']));
            $property_id->setValue(str_replace('property', '', $property['id']));
            $property_fieldset_name = $property['id'];
        }

        $property_form = new FieldSet($property_fieldset_name);
        $property_form->add($property_id);
        $property_form->add($name);
        $property_form->add($identifier);
        $property_form->add($tab);
        $property_form->add($datatype);
        $property_form->add($description);
        $property_form->add($required);
        $fieldsets->add($property_form);

        $this->getInputFilter()->get('properties')->add(array(
            'type'   => 'Zend\InputFilter\InputFilter',
            'name' => array(
                'name' => 'name',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty')
                ),
            ),
            'identifier' => array(
                'name' => 'identifier',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty')
                ),
            ),
            'tab' => array(
                'name' => 'tab',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty')
                ),
            ),
            'datatype' => array(
                'name' => 'datatype',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty')
                ),
            ),
            'description' => array(
                'name' => 'description',
                'required' => FALSE,
                'allow_empty' => FALSE,
            ),
            'required' => array(
                'name' => 'required',
                'required' => FALSE,
                'allow_empty' => FALSE,
            )
        ), $property_fieldset_name);

        return $this;
    }

    /**
     * Initialize tabs sub form
     * @return \Zend\Form\FieldSet
     */
    private function getTabs()
    {
        $fieldsets = $this->getFieldSets();
        if(!empty($fieldsets['tabs']))
        {
            return $fieldsets['tabs'];
        }

        $fieldsets = new FieldSet('tabs');

        $this->add($fieldsets);
        $this->getInputFilter()->add(array('type'   => 'Zend\InputFilter\InputFilter'), 'tabs');

        return $fieldsets;
    }

    /**
     * Add tab sub form
     * @param mixed \Gc\Tab\Model | array
     * @return \Developpement\Form\DocumentType
     */
    public function addTab($tab)
    {
        if(!is_array($tab) and !$tab instanceof Tab\Model)
        {
            return $this;
        }

        $fieldsets = $this->getTabs();

        $name = new Element\Text('name');

        $description = new Element\Text('description');

        $tab_id = new Element\Hidden('tab_id');

        if($tab instanceof Tab\Model)
        {
            $name->setValue($tab->getName());
            $description->setValue($tab->getDescription());
            $tab_id->setValue($tab->getId());
            $tab_fieldset_name = $tab->getId();

        }
        elseif(is_array($tab))
        {
            $name->setValue($tab['name']);
            $description->setValue($tab['description']);
            $tab_id->setValue(str_replace('tab', '', $tab['id']));
            $tab_fieldset_name = $tab['id'];
        }

        $tab_form = new FieldSet($tab_fieldset_name);
        $fieldsets->add($tab_form);
        $tab_form->add($name);
        $tab_form->add($description);
        $tab_form->add($tab_id);

        //Input filter

        $this->getInputFilter()->get('tabs')->add(array(
            'type'   => 'Zend\InputFilter\InputFilter',
            'name' => array(
                'name' => 'name',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty')
                ),
            ),
            'description' => array(
                'name' => 'description',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty')
                ),
            ),
        ), $tab_fieldset_name);

        return $this;
    }

    /**
     * Set values and create tabs and properties FieldSet
     * from parameter
     * @param mixed \Gc\DocumentType\Model | array
     * @return \Developpement\Form\DocumentType
     */
    public function setValues($element)
    {
        if($element instanceof DocumentTypeModel)
        {
            $infos_form = $this->getInfos();
            $infos_form->get('name')->setValue($element->getName());
            $infos_form->get('description')->setValue($element->getDescription());

            $views_form = $this->getViews();
            $views_form->get('default_view')->setValue($element->getDefaultViewId());
            $views_collection = $element->getAvailableViews();
            $views_form->get('available_views')->setValue($views_collection->getSelect());

            $tabs = $element->getTabs();
            $session = $element;
            foreach($tabs as $tab_id => $tab)
            {
                $this->addTab($tab);
                $properties = $tab->getProperties();
                foreach($properties as $property)
                {
                    $this->addProperty($property);
                }
            }
        }
        else
        {
            if(empty($element['tabs']))
            {
                return;
            }

            $tab_select = array();
            foreach($element['tabs'] as $tab_id => $tab)
            {
                if(!is_array($tab))
                {
                    continue;
                }

                $tab['id'] = $tab_id;
                $this->addTab($tab);
                $tab_select[$tab_id] = $tab['name'];
            }

            foreach($element['properties'] as $property_id => $property)
            {
                if(!is_array($property))
                {
                    continue;
                }

                $property['id'] = $property_id;
                $this->addProperty($property);
            }
        }

        return $this;
    }
}
