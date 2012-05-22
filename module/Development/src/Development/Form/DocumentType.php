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
     * @var \Zend\InputFilter\Factory $_inputFilterFactory
     */
    protected $_inputFilter = NULL;

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
        $this->_inputFilter = new InputFilter();
        $this->_datatypeCollection = new Datatype\Collection();
        $this->_viewCollection = new View\Collection();

        $this->getInfos();
        $this->getViews();
        $this->getProperties();
        $this->getTabs();

        $this->setInputFilter($this->_inputFilter);

    }

    /**
     * Initialize infos sub form
     * @return \Zend\Form\FieldSet
     */
    private function getInfos()
    {
        $fieldsets = $this->getFieldSets();
        if(!empty($sub_form['infos']))
        {
            return $sub_form['infos'];
        }

        $sub_form = new FieldSet('infos');

        $this->_inputFilter->add(array(
            'infos' => array(
                'type'   => 'Zend\InputFilter\InputFilter',
                'name' => array(
                    'name'    => 'name',
                    'required'=> TRUE
                    , 'validators' => array(
                        array('name' => 'not_empty')
                        , array(
                            'name' => 'db\\no_record_exists'
                            , 'options' => array(
                                'table' => 'document_type'
                                , 'field' => 'name'
                                , 'adapter' => $this->getAdapter()
                            )
                        )
                    )
                ),
                'description' => array(
                    'required'=> TRUE
                    , 'validators' => array(
                        array('name' => 'not_empty')
                    )
                ),
                'icon_id' => array()
            ),
        ));

        $sub_form->add(new Element('name'));
        $sub_form->add(new Element('description'));
        $sub_form->add(new Element('icon_id'));


        $this->add($sub_form);

        return $sub_form;
    }

    /**
     * Initialize views sub form
     * @return \Zend\Form\FieldSet
     */
    private function getViews()
    {
        $fieldsets = $this->getFieldSets();
        if(!empty($sub_form['views']))
        {
            return $sub_form['views'];
        }

        $sub_form = new FieldSet('views');

        $this->_inputFilter->add(array(), 'available_views');
        $this->_inputFilter->add(array(), 'icon_id');
        $this->_inputFilter->add(array(
            'required'=> TRUE
            , 'validators' => array(
                array('name' => 'not_empty')
            )
        ), 'default_view');

        $available_views = new Element('available_views');
        $available_views->setAttribute('options', $this->_viewCollection->getSelect());
        $sub_form->add($available_views);

        $default_view = new Element('default_view');
        $default_view->setAttribute('options', $this->_viewCollection->getSelect());
        $sub_form->add($default_view);

        $sub_form->add(new Element('icon_id'));

        $this->add($sub_form);

        return $sub_form;
    }

    /**
     * Initialize properties sub form
     * @return \Zend\Form\FieldSet
     */
    private function getProperties()
    {
        $fieldsets = $this->getFieldSets();
        if(!empty($sub_form['properties']))
        {
            return $sub_form['properties'];
        }

        $sub_form = new FieldSet('properties');
        $this->add($sub_form);

        return $sub_form;
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

        $sub_form = $this->getProperties();

        $name = new Element\Text('name');
        $identifier = new Element\Text('identifier');

        $tab = new Element\Select('tab');
        $tab->setAttrib('class', 'select-tab')
            ->addMultioptions(array())
            ->setRegisterInArrayValidator(FALSE)
            ->setRequired(TRUE);

        $datatype = new Element\Select('datatype');
        $datatype->setAttrib('class', 'select-datatype')
            ->addMultioptions($this->_datatypeCollection->getSelect())
            ->setRequired(TRUE);

        $description = new Element\Text('description');
        $required = new Element\Checkbox('required');
        $property_id = new Element\Hidden('property_id');

        $property_form = new FieldSet();

        if($property instanceof Property\Model)
        {
            $sub_form->add($property_form, $property->getId());
            $name->setValue($property->getName());
            $identifier->setValue($property->getIdentifier());
            $tab->setValue($property->getTabId());
            $datatype->setValue($property->getDatatypeId());
            $description->setValue($property->getDescription());
            $required->setValue($property->isRequired());
            $property_id->setValue($property->getId());
        }
        elseif(is_array($property))
        {
            $sub_form->add($property_form, $property['id']);
            $name->setValue($property['name']);
            $identifier->setValue($property['identifier']);
            $tab->setValue($property['tab']);
            $datatype->setValue($property['datatype']);
            $description->setValue($property['description']);
            $required->setValue(!empty($property['is_required']));
        }

        $property_form->addElements(array($property_id, $name, $identifier, $tab, $datatype, $description, $required));

        return $this;
    }

    /**
     * Initialize tabs sub form
     * @return \Zend\Form\FieldSet
     */
    private function getTabs()
    {
        $fieldsets = $this->getFieldSets();
        if(!empty($sub_form['tabs']))
        {
            return $sub_form['tabs'];
        }

        $sub_form = new FieldSet('tabs');

        $this->add($sub_form);

        return $sub_form;
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

        $sub_form = $this->getTabs();

        $name = new Element\Text('name');
        $name->setRequired(TRUE);

        $description = new Element\Text('description');
        $description->setRequired(TRUE);

        $tab_id = new Element\Hidden('tab_id');

        $tab_form = new FieldSet();

        if($tab instanceof Tab\Model)
        {
            $sub_form->addFieldSet($tab_form, $tab->getId());
            $name->setValue($tab->getName());
            $description->setValue($tab->getDescription());
            $tab_id->setValue($tab->getId());
        }
        elseif(is_array($tab))
        {
            $sub_form->addFieldSet($tab_form, $tab['id']);
            $name->setValue($tab['name']);
            $description->setValue($tab['description']);
        }

        $tab_form->addElements(array($name, $description, $tab_id));

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
            $infos_form->getElement('name')->setValue($element->getName());
            $infos_form->getElement('description')->setValue($element->getDescription());

            $views_form = $this->getViews();
            $views_form->getElement('default_view')->setValue($element->getDefaultViewId());
            $views_collection = $element->getAvailableViews();
            $views_form->getElement('available_views')->setValue($views_collection->getSelect());

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
