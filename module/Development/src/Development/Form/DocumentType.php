<?php
namespace Development\Form;

use Gc\Form\AbstractForm,
    Gc\DocumentType\Model as DocumentTypeModel,
    Gc\Datatype,
    Gc\Validator,
    Gc\View,
    Zend\Validator\Db,
    Zend\Form\Element,
    Zend\Form\SubForm;

class DocumentType extends AbstractForm
{

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

    public function init()
    {
        $this->getInfos();
        $this->getViews();
        $this->getProperties();
        $this->getTabs();

        $this->setAttrib('id', 'development-form');
        $this->addDecorator('ViewScript', array('viewScript' => 'development-forms/document-type.phtml'));

        $collection = new Datatype\Collection();
        $datatypes = $collection->getSelect();
        $this->getSubForm('properties')->getElement('datatype')->addMultioptions($datatypes);

        $views_collection = new View\Collection();
        $views = $views_collection->getSelect();

        $this->getSubForm('views')->getElement('default_view')->addMultioptions($views);
        $this->getSubForm('views')->getElement('available_views')->addMultioptions($views);

        $this->getDecorator('ViewScript')->setOption('datatypes', $datatypes);
        $this->getDecorator('ViewScript')->setOption('views', $views);

    }

    /**
    * @param unknown_type $values
    * @return Development_Form_DocumentType
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
        $name->setLabel('Name')
            ->setValue('');

        $description = new Element\Text('description');
        $description->setLabel('Description')
            ->setValue('');

        $icon_id = new Element\Select('icon_id');
        $icon_id->setLabel('Icon');

        $sub_form->addElements(array($name, $description, $icon_id));

        return $this->addSubForm($sub_form, 'infos');
    }

    /**
    * @param unknown_type $values
    * @return Development_Form_DocumentType
    */
    private function getViews()
    {
        $sub_form = $this->getSubForm('views');
        if(!empty($sub_form))
        {
            return $sub_form;
        }
        $sub_form = new SubForm($this->_subDocumentTypeForms['views']);

        $default_view = new Element\Select('default_view');
        $default_view->setLabel('Default view')
            ->setRequired(TRUE);

        $available_views = new Element\Multiselect('available_views');
        $available_views->setLabel('Available views');


        $sub_form->addElements(array($default_view, $available_views));

        return $this->addSubForm($sub_form, 'views');
    }

    /**
    * @param unknown_type $values
    * @return Development_Form_DocumentType
    */
    private function getProperties()
    {
        $sub_form = $this->getSubForm('properties');
        if(!empty($sub_form))
        {
            return $sub_form;
        }

        $sub_form = new SubForm($this->_subDocumentTypeForms['properties']);

        $name = new Element\Text('name');
        $name->setLabel('Name')
            ->setIsArray(TRUE);

        $identifier = new Element\Text('identifier');
        $identifier->setLabel('Identifier')
            ->setIsArray(TRUE);

        $tab = new Element\Select('tab');
        $tab->setLabel('Tab')
            ->setAttrib('class', 'select-tab')
            ->addMultioptions(array())
            ->setRegisterInArrayValidator(FALSE)
            ->setRequired(TRUE)
            ->setIsArray(TRUE);

        $datatype = new Element\Select('datatype');
        $datatype->setLabel('Datatype')
            ->setAttrib('class', 'select-datatype')
            ->addMultioptions(array())
            ->setRequired(TRUE)
            ->setIsArray(TRUE);

        $description = new Element\Text('description');
        $description->setLabel('Description')
            ->setIsArray(TRUE);

        $required = new Element\Checkbox('required');
        $required->setLabel('Required')
            ->setIsArray(TRUE);


        $sub_form->addElements(array($name, $identifier, $tab, $datatype, $description, $required));

        return $this->addSubForm($sub_form, 'properties');
    }

    /**
    * @param array $values
    * @return Development_Form_DocumentType
    */
    private function getTabs()
    {
        $sub_form = $this->getSubForm('tabs');
        if(!empty($sub_form))
        {
            return $sub_form;
        }

        $sub_form = new SubForm($this->_subDocumentTypeForms['tabs']);

        $add_name = new Element\Text('name');
        $add_name->setLabel('Name')
            ->setIsArray(TRUE)
            ->setRequired(TRUE);

        $description = new Element\Text('description');
        $description->setLabel('Description')
            ->setBelongsTo('description')
            ->setIsArray(TRUE)
            ->setRequired(TRUE);

        $sub_form->addElements(array($add_name, $description));

        return $this->addSubForm($sub_form, 'tabs');
    }

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
            $tab_select = array();
            foreach($tabs as $tab_id => $tab)
            {
                //@TODO Change content here to elements depends of session values
                $tab_form = $this->getTabs();
                $tab_form->getElement('name')->setValue($tab->getName());
                $tab_form->getElement('description')->setValue($tab->getDescription());
                $tab_select[$tab_id] = $tab->getName();
                $properties = $tab->getProperties();
                foreach($properties as $property)
                {
                    $property_form = $this->getProperties();
                    $property_form->getElement('name')->setValue($property->getName());
                    $property_form->getElement('identifier')->setValue($property->getIdentifier());
                    $property_form->getElement('tab')->addMultiOptions($tab_select)->setValue($property->getTabId());
                    $property_form->getElement('datatype')->setValue($property->getDatatypeId());
                    $property_form->getElement('description')->setValue($property->getDescription());
                    $property_form->getElement('required')->setValue($property->isRequired());
                }
            }
        }
        else
        {
            if(empty($element['tabs']))
            {
                return;
            }

            $session = $element;
            $tab_select = array();
            foreach($session['tabs'] as $tab_id => $tab)
            {
                //@TODO Change content here to elements depends of session values
                $tab_form = $this->getTabs();
                $tab_form->getElement('name')->setValue($tab['name']);
                $tab_form->getElement('description')->setValue($tab['description']);
                $tab_select[$tab_id] = $tab['name'];
                foreach($tab['properties'] as $property)
                {
                    $property_form = $this->getProperties();
                    $property_form->getElement('name')->setValue($property['name']);
                    $property_form->getElement('identifier')->setValue($property['identifier']);
                    $property_form->getElement('tab')->addMultiOptions($tab_select)->setValue($property['tab']);
                    $property_form->getElement('datatype')->setValue($property['datatype']);
                    $property_form->getElement('description')->setValue($property['description']);
                    $property_form->getElement('required')->setValue($property['is_required']);
                }
            }
        }
    }
}
