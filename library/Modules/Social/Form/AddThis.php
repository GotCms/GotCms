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
 * @package    Modules
 * @subpackage Social\Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Social\Form;

use Gc\Form\AbstractForm;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\Form\FormInterface;
use Zend\Form\Fieldset;
use Social\Model;

/**
 * Comment form
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Social\Form
 */
class AddThis extends AbstractForm
{
    /**
     * Init Module form
     *
     * @return void
     */
    public function init()
    {
        $this->setAttribute('class', 'relative form-horizontal');
    }

    /**
     * Prepare elements for widgets
     *
     * @return void
     */
    public function prepareWidgets()
    {
        foreach ($this->getModel()->getWidgets() as $idx => $widget) {
            $this->addWidget('widget-' . $idx, $widget);
        }
    }


    /**
     * Prepare elements for configuration
     *
     * @return void
     */
    public function prepareConfig()
    {
        $options  = $this->getModel()->getConfig();
        $fieldset = new Fieldset('config');

        $language = new Element\Select(
            $this->setNameAsArray('config', 'language')
        );
        $language->setLabel('Language')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValueOptions($this->getModel()->getLanguages())
            ->setValue($options['language']);
        $fieldset->add($language);

        $ga = new Element\Text(
            $this->setNameAsArray('config', 'data_ga_property_id')
        );
        $ga->setLabel('Google Analytics property ID')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValue($options['data_ga_property_id']);
        $fieldset->add($ga);

        $showOnDashboard = new Element\Checkbox(
            $this->setNameAsArray('config', 'show_stats')
        );
        $showOnDashboard->setLabel('Show stats on dashboard')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('id', 'show_stats')
            ->setAttribute('class', 'input-checkbox')
            ->setValue($options['show_stats']);
        $fieldset->add($showOnDashboard);

        $profileId = new Element\Text(
            $this->setNameAsArray('config', 'profile_id')
        );
        $profileId->setLabel('AddThis Profile ID')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValue($options['profile_id']);
        $fieldset->add($profileId);

        $username = new Element\Text(
            $this->setNameAsArray('config', 'username')
        );
        $username->setLabel('AddThis Username')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValue($options['username']);
        $fieldset->add($username);

        $password = new Element\Password(
            $this->setNameAsArray('config', 'password')
        );
        $password->setLabel('AddThis Password')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValue($options['password']);
        $fieldset->add($password);

        $dataTrackClickback = new Element\Checkbox(
            $this->setNameAsArray('config', 'data_track_clickback')
        );
        $dataTrackClickback->setLabel('Track clickback')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('id', 'data_track_clickback')
            ->setAttribute('class', 'input-checkbox')
            ->setValue($options['data_track_clickback']);
        $fieldset->add($dataTrackClickback);

        $dataTrackAddressbar = new Element\Checkbox(
            $this->setNameAsArray('config', 'data_track_addressbar')
        );
        $dataTrackAddressbar->setLabel('Track adressbar')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('id', 'data_track_addressbar')
            ->setAttribute('class', 'input-checkbox')
            ->setValue($options['data_track_addressbar']);
        $fieldset->add($dataTrackAddressbar);

        $jsonConfig = new Element\Textarea(
            $this->setNameAsArray('config', 'config_json')
        );
        $jsonConfig->setLabel('Json config')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValue($options['config_json']);
        $fieldset->add($jsonConfig);



        $this->add($fieldset);

        $this->getInputFilter()->add(
            array(
                'type' => 'Zend\InputFilter\InputFilter',
                'language' => array(
                    'name' => 'language',
                    'required' => true,
                ),
                'data_ga_property_id' => array(
                    'name' => 'data_ga_property_id',
                    'required' => false,
                ),
                'profile_id' => array(
                    'name' => 'profile_id',
                    'required' => false,
                ),
                'show_stats' => array(
                    'name' => 'show_stats',
                    'required' => false,
                ),
                'password' => array(
                    'name' => 'password',
                    'required' => false,
                ),
                'username' => array(
                    'name' => 'username',
                    'required' => false,
                ),
                'data_track_clickback' => array(
                    'name' => 'data_track_clickback',
                    'required' => false,
                ),
                'data_track_addressbar' => array(
                    'name' => 'data_track_addressbar',
                    'required' => false,
                ),
                'json_config' => array(
                    'name' => 'json_config',
                    'required' => false,
                ),
            ),
            'config'
        );
    }

    /**
     * Add widgets
     *
     * @param string $fieldsetName Fieldset name
     * @param array  $values       Widgets values
     *
     * @return void
     */
    public function addWidget($fieldsetName, $values = array())
    {
        $fieldset = new Fieldset($fieldsetName);
        $this->add($fieldset);

        $name = new Element\Text(
            $this->setNameAsArray($fieldsetName, 'name')
        );
        $name->setLabel('Name')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValue(isset($values['name']) ? $values['name'] : '');
        $fieldset->add($name);

        $identifier = new Element\Text(
            $this->setNameAsArray($fieldsetName, 'identifier')
        );
        $identifier->setLabel('Identifier')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValue(isset($values['identifier']) ? $values['identifier'] : '');
        $fieldset->add($identifier);

        $radio = new Element\Radio(
            $this->setNameAsArray($fieldsetName, 'settings')
        );
        $radio->setLabel('Sharing Tool')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setValue(isset($values['settings']) ? $values['settings'] : '');
        $radioValues = array();
        foreach ($this->getModel()->getDefaultStyles() as $styleName => $style) {
            $radioValues[$styleName] = array(
                'name' => $style['name'],
                'options' => array(
                    'img' => isset($style['img']) ? $style['img'] : null,
                )
            );
        }

        $radio->setValueOptions($radioValues);
        $fieldset->add($radio);

        $customString = new Element\Textarea(
            $this->setNameAsArray($fieldsetName, 'custom_string')
        );
        $customString->setLabel('Custom string')
            ->setLabelAttributes(
                array(
                    'class' => 'col-lg-2 control-label',
                )
            )
            ->setAttribute('class', 'form-control col-lg-10')
            ->setValue(isset($values['custom_string']) ? $values['custom_string'] : '');
        $fieldset->add($customString);

        $this->add($fieldset);

        $chosenList = new Element\Hidden(
            $this->setNameAsArray($fieldsetName, 'chosen_list')
        );
        $chosenList->setValue(isset($values['chosen_list']) ? $values['chosen_list'] : '');
        $fieldset->add($chosenList);

        $this->getInputFilter()->add(
            array(
                'type' => 'Zend\InputFilter\InputFilter',
                'name' => array(
                    'name' => 'name',
                    'required' => true,
                ),
                'identifier' => array(
                    'name' => 'identifier',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array(
                            'name' => 'regex',
                            'options' => array(
                                'pattern' => parent::IDENTIFIER_PATTERN
                            )
                        )
                    ),
                ),
                'settings' => array(
                    'name' => 'settings',
                    'required' => true,
                ),
                'custom_string' => array(
                    'name' => 'custom_string',
                    'required' => false,
                ),
                'chosen_list' => array(
                    'name' => 'chosen_list',
                    'required' => false,
                ),
            ),
            $fieldsetName
        );
    }

    /**
     * Set name as array for elements name
     *
     * @param string $fieldsetName Fieldset name
     * @param string $name         Name
     *
     * @return string
     */
    protected function setNameAsArray($fieldsetName, $name)
    {
        return sprintf('%s[%s]', $fieldsetName, $name);
    }

    /**
     * Set model
     *
     * @param Social\Model\AddThis $model AddThis model
     *
     * @return Social\Form\AddThis
     */
    public function setModel(Model\AddThis $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Return addthis model
     *
     * @return Social\Model\AddThis
     */
    public function getModel()
    {
        return $this->model;
    }
}
