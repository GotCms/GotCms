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
    Zend\Form\SubForm;

class DocumentType extends AbstractForm
{
    /**
     * Contains configuration of all sub forms
     * @var $_subDocumentTypeForms array
     *
     */
    protected $_subDocumentTypeForms = array(
        'infos' => array(
            'legend' => 'Infos'
            , 'decorators' => array(
                'FormElements'
                , array(
                    'HtmlTag'
                    , array(
                        'tag' => 'dl'
                        , 'id' => 'tabs-1')
            )))
        , 'views' => array(
            'legend' => 'View'
            ,'decorators' => array(
                'FormElements'
                , array(
                    'HtmlTag'
                    , array(
                        'tag' => 'dl'
                        , 'id' => 'tabs-2')
            )))
        , 'properties' => array(
            'legend' => 'Properties'
            , 'decorators' => array(
                'FormElements'
                , array(
                    'HtmlTag'
                    , array(
                        'tag' => 'dl'
                        , 'id' => 'tabs-3')
            )))
        , 'tabs' => array(
            'legend' => 'Tabs'
            , 'decorators' => array(
                'FormElements'
                , array(
                    'HtmlTag'
                    , array(
                        'tag' => 'dl'
                        , 'id' => 'tabs-4')
        )))
    );

    /**
     * @var $_datatypeValues array
     * Contains datatype values key => value for select input
     */
    protected $_datatypeValues = NULL;

    /**
     * Init document type form
     */
    public function init()
    {
        $this->getInfos();
        $this->getViews();
        $this->getProperties();
        $this->getTabs();

        $this->setAttrib('id', 'development-form');
        $this->addDecorator('ViewScript', array('viewScript' => 'development-forms/document-type.phtml'));

        $collection = new Datatype\Collection();
        $this->_datatypeValues = $collection->getSelect();

        $views_collection = new View\Collection();
        $views = $views_collection->getSelect();

        $this->getSubForm('views')->getElement('default_view')->addMultioptions($views);
        $this->getSubForm('views')->getElement('available_views')->addMultioptions($views);

        $this->getDecorator('ViewScript')->setOption('datatypes', $this->_datatypeValues);
        $this->getDecorator('ViewScript')->setOption('views', $views);

    }

    /**
     * Initialize infos sub form
     * @return \Zend\Form\SubForm
     */
    private function getInfos()
    {
        $sub_form = $this->getSubForm('infos');
        if(!empty($sub_form))
        {
            return $sub_form;
        }

        $sub_form = new SubForm($this->_subDocumentTypeForms['infos']);

        $name = new Element\Text('name');
        $name->addValidator(new Db\NoRecordExists(array(
                'table' => 'document_type'
                , 'field' => 'name'
                ))
            );
        $description = new Element\Text('description');
        $icon_id = new Element\Select('icon_id');

        $sub_form->addElements(array($name, $description, $icon_id));

        $this->addSubForm($sub_form, 'infos');

        return $sub_form;
    }

    /**
     * Initialize views sub form
     * @return \Zend\Form\SubForm
     */
    private function getViews()
    {
        $sub_form = $this->getSubForm('views');
        if(!empty($sub_form))
        {
            return $sub_form;
        }
        $sub_form = new SubForm($this->_subDocumentTypeForms['views']);

        $available_views = new Element\Multiselect('available_views');
        $default_view = new Element\Select('default_view');
        $default_view->setRequired(TRUE);

        $sub_form->addElements(array($default_view, $available_views));

        $this->addSubForm($sub_form, 'views');

        return $sub_form;
    }

    /**
     * Initialize properties sub form
     * @return \Zend\Form\SubForm
     */
    private function getProperties()
    {
        $sub_form = $this->getSubForm('properties');
        if(!empty($sub_form))
        {
            return $sub_form;
        }

        $sub_form = new SubForm($this->_subDocumentTypeForms['properties']);
        $this->addSubForm($sub_form, 'properties');

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

        $sub_form = $this->getSubForm('properties');

        $name = new Element\Text('name');
        $identifier = new Element\Text('identifier');

        $tab = new Element\Select('tab');
        $tab->setAttrib('class', 'select-tab')
            ->addMultioptions(array())
            ->setRegisterInArrayValidator(FALSE)
            ->setRequired(TRUE);

        $datatype = new Element\Select('datatype');
        $datatype->setAttrib('class', 'select-datatype')
            ->addMultioptions($this->_datatypeValues)
            ->setRequired(TRUE);

        $description = new Element\Text('description');
        $required = new Element\Checkbox('required');
        $property_id = new Element\Hidden('property_id');

        $property_form = new SubForm();

        if($property instanceof Property\Model)
        {
            $sub_form->addSubForm($property_form, $property->getId());
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
            $sub_form->addSubForm($property_form, $property['id']);
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
     * @return \Zend\Form\SubForm
     */
    private function getTabs()
    {
        $sub_form = $this->getSubForm('tabs');
        if(!empty($sub_form))
        {
            return $sub_form;
        }

        $sub_form = new SubForm($this->_subDocumentTypeForms['tabs']);
        $this->addSubForm($sub_form, 'tabs');

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
        $sub_form = $this->getSubForm('tabs');

        $name = new Element\Text('name');
        $name->setRequired(TRUE);

        $description = new Element\Text('description');
        $description->setRequired(TRUE);

        $tab_id = new Element\Hidden('tab_id');

        $tab_form = new SubForm();

        if($tab instanceof Tab\Model)
        {
            $sub_form->addSubForm($tab_form, $tab->getId());
            $name->setValue($tab->getName());
            $description->setValue($tab->getDescription());
            $tab_id->setValue($tab->getId());
        }
        elseif(is_array($tab))
        {
            $sub_form->addSubForm($tab_form, $tab['id']);
            $name->setValue($tab['name']);
            $description->setValue($tab['description']);
        }

        $tab_form->addElements(array($name, $description, $tab_id));

        return $this;
    }

    /**
     * Set values and create tabs and properties subform
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

    /**
     * Validate the form
     *
     * @param  array $data
     * @return boolean
     */
    public function isValid($data)
    {
        $this->setValues($data);

        return parent::isValid($data);
    }
}
