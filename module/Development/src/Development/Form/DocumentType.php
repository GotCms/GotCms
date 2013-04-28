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
 * @package    Development
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Development\Form;

use Gc\Form\AbstractForm;
use Gc\Datatype\Collection as DatatypeCollection;
use Gc\DocumentType\Model as DocumentTypeModel;
use Gc\DocumentType\Collection as DocumentTypeCollection;
use Gc\Media\Icon;
use Gc\Property;
use Gc\Tab;
use Gc\View\Collection as ViewCollection;
use Zend\Validator\Db;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\Form\FieldSet;

/**
 * Document type form
 *
 * @category   Gc_Application
 * @package    Development
 * @subpackage Form
 */
class DocumentType extends AbstractForm
{
    /**
     * View collection
     *
     * @var \Gc\View\Collection
     */
     protected $viewCollection;

     /**
      * Datatype collection
      *
      * @var \Gc\Datatype\Collection
      */
     protected $datatypeCollection;

    /**
     * Init document type form
     *
     * @return void
     */
    public function init()
    {
        $this->setInputFilter(new InputFilter());
        $this->datatypeCollection = new DatatypeCollection();
        $this->viewCollection     = new ViewCollection();

        $this->getInfos();
        $this->getViews();
        $this->getProperties();
        $this->getTabs();

        $this->setInputFilter($this->getInputFilter());
    }

    /**
     * Initialize infos sub form
     *
     * @return \Zend\Form\FieldSet
     */
    protected function getInfos()
    {
        $fieldsets = $this->getFieldSets();
        if (!empty($fieldsets['infos'])) {
            return $fieldsets['infos'];
        }

        $fieldsets = new FieldSet('infos');

        $this->getInputFilter()->add(
            array(
                'type'   => 'Zend\InputFilter\InputFilter',
                'name' => array(
                    'name' => 'name',
                    'required' => true,
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
                    'required' => false,
                ),
                'icon_id' => array(
                    'name' => 'icon_id',
                    'required' => true,
                    'allow_empty' => false,
                ),
                'dependency' => array(
                    'name' => 'dependency',
                    'required' => false,
                    'allow_empty' => false,
                ),
            ),
            'infos'
        );


        $fieldsets->add(new Element\Text('name'));
        $fieldsets->add(new Element\Text('description'));

        $iconId     = new Element\Select('icon_id');
        $collection = new Icon\Collection();
        $iconId->setValueOptions($collection->getIcons())
            ->setAttribute('class', 'input-select');
        $fieldsets->add($iconId);

        $documentTypeCollection = new DocumentTypeCollection();
        $select                 = $documentTypeCollection->getSelect();
        if (!empty($select)) {
            $dependency = new Element\MultiCheckbox('infos[dependency]');
            $dependency->setAttribute('class', 'input-checkbox');
            $dependency->setValueOptions($documentTypeCollection->getSelect());
            $fieldsets->add($dependency);
        }

        $this->add($fieldsets);

        return $fieldsets;
    }

    /**
     * Initialize views sub form
     *
     * @return \Zend\Form\FieldSet
     */
    protected function getViews()
    {
        $fieldsets = $this->getFieldSets();
        if (!empty($fieldsets['views'])) {
            return $fieldsets['views'];
        }

        $fieldsets = new FieldSet('views');

        $availableViews = new Element\Select('available_views');
        $availableViews->setAttribute('multiple', 'multiple');
        $availableViews->setValueOptions(array('' => '') + $this->viewCollection->getSelect());
        $fieldsets->add($availableViews);

        $defaultView = new Element\Select('default_view');
        $defaultView->setValueOptions($this->viewCollection->getSelect());
        $fieldsets->add($defaultView);

        $this->add($fieldsets);

        $this->getInputFilter()->add(
            array(
                'type'   => 'Zend\InputFilter\InputFilter',
                'default_view' => array(
                    'name' => 'default_view',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty')
                    ),
                ),
                'available_views' => array(
                    'name' => 'available_views',
                    'required' => false,
                    'allow_empty' => false,
                ),
            ),
            'views'
        );

        return $fieldsets;
    }

    /**
     * Initialize properties sub form
     *
     * @return \Zend\Form\FieldSet
     */
    protected function getProperties()
    {
        $fieldsets = $this->getFieldSets();
        if (!empty($fieldsets['properties'])) {
            return $fieldsets['properties'];
        }

        $fieldsets = new FieldSet('properties');

        $datatypes = new Element\Select('datatypes');
        $datatypes->setValueOptions($this->datatypeCollection->getSelect())
            ->setAttribute('type', 'select');

        $fieldsets->add($datatypes);

        $this->add($fieldsets);
        $this->getInputFilter()->add(array('type'   => 'Zend\InputFilter\InputFilter'), 'properties');

        return $fieldsets;
    }

    /**
     * Add property sub form
     *
     * @param mixed $property \Gc\Property\Model|array
     *
     * @return \Developpement\Form\DocumentType
     */
    public function addProperty($property)
    {
        if (!is_array($property) and !$property instanceof Property\Model) {
            return $this;
        }

        $fieldsets = $this->getProperties();

        $name = new Element\Text('name');

        $identifier = new Element('identifier');

        $tab = new Element\Select('tab');
        $tab->setAttribute('class', 'select-tab')
            ->setValueOptions(array());

        $datatype = new Element\Select('datatype');
        $datatype->setAttribute('class', 'select-datatype')
            ->setValueOptions($this->datatypeCollection->getSelect());

        $description = new Element\Text('description');
        $required    = new Element\Checkbox('required');
        $required->setValue(1)
            ->setAttribute('id', 'required')
            ->setAttribute('class', 'input-checkbox');
        $propertyId = new Element\Hidden('property_id');

        if ($property instanceof Property\Model) {
            $name->setValue($property->getName());
            $identifier->setValue($property->getIdentifier());
            $tab->setValue($property->getTabId());
            $datatype->setValue($property->getDatatypeId());
            $description->setValue($property->getDescription());
            $required->setCheckedValue($property->isRequired());
            $propertyId->setValue($property->getId());
            $propertyFieldsetName = $property->getId();
        } elseif (is_array($property)) {
            $name->setValue($property['name']);
            $identifier->setValue($property['identifier']);
            $tab->setValue($property['tab']);
            $datatype->setValue($property['datatype']);
            $description->setValue($property['description']);
            $required->setCheckedValue(!empty($property['is_required']));
            $propertyId->setValue(str_replace('property', '', $property['id']));
            $propertyFieldsetName = $property['id'];
        }

        $propertyForm = new FieldSet($propertyFieldsetName);
        $propertyForm->add($propertyId);
        $propertyForm->add($name);
        $propertyForm->add($identifier);
        $propertyForm->add($tab);
        $propertyForm->add($datatype);
        $propertyForm->add($description);
        $propertyForm->add($required);
        $fieldsets->add($propertyForm);

        $this->getInputFilter()->get('properties')->add(
            array(
                'type'   => 'Zend\InputFilter\InputFilter',
                'name' => array(
                    'name' => 'name',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty')
                    ),
                ),
                'identifier' => array(
                    'name' => 'identifier',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array('name' => 'regex', 'options' => array(
                            'pattern' => parent::IDENTIFIER_PATTERN
                        ))
                    ),
                ),
                'tab' => array(
                    'name' => 'tab',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty')
                    ),
                ),
                'datatype' => array(
                    'name' => 'datatype',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty')
                    ),
                ),
                'description' => array(
                    'name' => 'description',
                    'required' => false,
                    'allow_empty' => false,
                ),
                'required' => array(
                    'name' => 'required',
                    'required' => false,
                    'allow_empty' => true,
                )
            ),
            $propertyFieldsetName
        );

        return $this;
    }

    /**
     * Initialize tabs sub form
     *
     * @return \Zend\Form\FieldSet
     */
    protected function getTabs()
    {
        $fieldsets = $this->getFieldSets();
        if (!empty($fieldsets['tabs'])) {
            return $fieldsets['tabs'];
        }

        $fieldsets = new FieldSet('tabs');

        $tabsList      = new Element\Select('tabs_list');
        $tabCollection = new Tab\Collection();
        $tabsList->setValueOptions($tabCollection->getTabs());
        $fieldsets->add($tabsList);
        $this->add($fieldsets);
        $this->getInputFilter()->add(array('type'   => 'Zend\InputFilter\InputFilter'), 'tabs');

        return $fieldsets;
    }

    /**
     * Add tab sub form
     *
     * @param mixed $tab \Gc\Tab\Model|array
     *
     * @return \Developpement\Form\DocumentType
     */
    public function addTab($tab)
    {
        if (!is_array($tab) and !$tab instanceof Tab\Model) {
            return $this;
        }

        $fieldsets = $this->getTabs();

        $name = new Element\Text('name');

        $description = new Element\Text('description');

        $tabId = new Element\Hidden('tab_id');

        if ($tab instanceof Tab\Model) {
            $name->setValue($tab->getName());
            $description->setValue($tab->getDescription());
            $tabId->setValue($tab->getId());
            $tabFieldsetName = $tab->getId();

        } elseif (is_array($tab)) {
            $name->setValue($tab['name']);
            $description->setValue($tab['description']);
            $tabId->setValue(str_replace('tab', '', $tab['id']));
            $tabFieldsetName = $tab['id'];
        }

        $tabForm = new FieldSet($tabFieldsetName);
        $fieldsets->add($tabForm);
        $tabForm->add($name);
        $tabForm->add($description);
        $tabForm->add($tabId);

        //Input filter

        $this->getInputFilter()->get('tabs')->add(
            array(
                'type'   => 'Zend\InputFilter\InputFilter',
                'name' => array(
                    'name' => 'name',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty')
                    ),
                ),
                'description' => array(
                    'name' => 'description',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty')
                    ),
                ),
            ),
            $tabFieldsetName
        );

        return $this;
    }

    /**
     * Set values and create tabs and properties FieldSet
     * from parameter
     *
     * @param mixed $element \Gc\DocumentType\Model | array
     *
     * @return \Developpement\Form\DocumentType
     */
    public function setValues($element)
    {
        if ($element instanceof DocumentTypeModel) {
            $infosForm = $this->getInfos();
            $infosForm->get('name')->setValue($element->getName());
            $infosForm->get('description')->setValue($element->getDescription());
            $infosForm->get('icon_id')->setValue($element->getIconId());
            $dependency = $infosForm->get('infos[dependency]');
            if (!empty($dependency)) {
                $dependency->setValue($element->getDependencies());
            }

            $viewsForm = $this->getViews();
            $viewsForm->get('default_view')->setValue($element->getDefaultViewId());
            $viewsCollection = $element->getAvailableViews();

            $viewsForm->get('available_views')->setValue($viewsCollection->getSelect());

            $tabs          = $element->getTabs();
            $tabCollection = new Tab\Collection();
            $this->getTabs()->get('tabs_list')->setValueOptions($tabCollection->getImportableTabs($element->getId()));

            $session = $element;
            foreach ($tabs as $tabId => $tab) {
                $this->addTab($tab);
                $properties = $tab->getProperties();
                foreach ($properties as $property) {
                    $this->addProperty($property);
                }
            }
        } else {
            if (empty($element['tabs'])) {
                return;
            }

            $tabSelect = array();
            foreach ($element['tabs'] as $tabId => $tab) {
                if (!is_array($tab)) {
                    continue;
                }

                $tab['id'] = $tabId;
                $this->addTab($tab);
                $tabSelect[$tabId] = $tab['name'];
            }

            foreach ($element['properties'] as $propertyId => $property) {
                if (!is_array($property)) {
                    continue;
                }

                $property['id'] = $propertyId;
                $this->addProperty($property);
            }
        }

        return $this;
    }
}
