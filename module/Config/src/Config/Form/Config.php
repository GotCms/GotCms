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
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Config\Form;

use Gc\Form\AbstractForm,
    Gc\Document,
    Gc\User\Permission,
    Zend\Form\Element,
    Zend\Form\Fieldset,
    Zend\InputFilter\InputFilter,
    Zend\Validator\Db,
    Zend\Validator\Identical;

class Config extends AbstractForm
{
    /**
     * Initialize form
     */
    public function init()
    {
        $this->setInputFilter(new InputFilter());
    }
    /**
     * Initialize General sub form
     *
     * @return \Config\Form\Config
     */
    public function initGeneral()
    {
        //General settings
        $general_fieldset = new Fieldset('general');
        $name = new Element('site_name');
        $name->setAttribute('label', 'Site name')
            ->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text');

        $is_offline = new Element('site_is_offline');
        $is_offline->setAttribute('label', 'Is offline')
            ->setAttribute('value', '1')
            ->setAttribute('type', 'checkbox');

        $offline_document = new Element('site_offline_document');
        $offline_document->setAttribute('label', 'Offline document')
            ->setAttribute('type', 'select');
        $document_collection = new Document\Collection();
        $document_collection->load(0);
        $offline_document->setAttribute('options', $document_collection->getSelect());

        $general_fieldset->add($name);
        $general_fieldset->add($is_offline);
        $general_fieldset->add($offline_document);
        $this->add($general_fieldset);

        $this->getInputFilter()->add(array(
            'name' => 'site_name',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'site_name');

        $this->getInputFilter()->add(array(
            'name' => 'site_is_offline',
            'required' => FALSE,
            'allow_empty' => TRUE
        ), 'site_is_offline');

        $this->getInputFilter()->add(array(
            'name' => 'site_offline_document',
            'required' => TRUE,
        ), 'site_offline_document');

        return $this;
    }

    /**
     * Initialize System sub form
     *
     * @return \Config\Form\Config
     */
    public function initSystem()
    {
        //Session settings
        $session_fieldset = new Fieldset('session');
        $cookie_domain = new Element('cookie_domain');
        $cookie_domain->setAttribute('label', 'Cookie domain')
            ->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text');

        $cookie_path = new Element('cookie_path');
        $cookie_path->setAttribute('label', 'Cookie path')
            ->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text');

        $session_lifetime = new Element('session_lifetime');
        $session_lifetime->setAttribute('label', 'Session lifetime')
            ->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text');

        $session_handler = new Element('session_handler');
        $session_handler->setAttribute('label', 'Session handler')
            ->setAttribute('type', 'select')
            ->setAttribute('options', array('Files' => '0', 'Database' => '1'));

        $session_fieldset->add($cookie_domain);
        $session_fieldset->add($cookie_path);
        $session_fieldset->add($session_handler);
        $session_fieldset->add($session_lifetime);
        $this->add($session_fieldset);

        //Debug settings
        $debug_fieldset = new Fieldset('debug');
        $debug_is_active = new Element('debug_is_active');
        $debug_is_active->setAttribute('label', 'Is active')
            ->setAttribute('type', 'checkbox')
            ->setAttribute('class', 'input-text');

        $debug_fieldset->add($debug_is_active);
        $this->add($debug_fieldset);

        $this->getInputFilter()->add(array(
            'name' => 'cookie_domain',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'cookie_domain');

        $this->getInputFilter()->add(array(
            'name' => 'cookie_path',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'cookie_path');

        $this->getInputFilter()->add(array(
            'name' => 'session_lifetime',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'session_lifetime');

        $this->getInputFilter()->add(array(
            'name' => 'session_handler',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'session_handler');

        $this->getInputFilter()->add(array(
            'name' => 'debug_is_active',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'debug_is_active');

        return $this;
    }

    /**
     * Initialize Server sub form
     *
     * @return \Config\Form\Config
     */
    public function initServer()
    {
        //Local settings
        $locale_list = array(
            'Français' => 'fr_FR',
            'English' => 'en_GB',
        );

        $locale_fieldset = new Fieldset('locale');
        $locale = new Element('locale');
        $locale->setAttribute('type', 'select')
            ->setAttribute('label', 'Server locale')
            ->setAttribute('options', $locale_list);

        $locale_fieldset->add($locale);
        $this->add($locale_fieldset);

        //Mail settings
        $mail_fieldset = new Fieldset('mail');
        $mail_from = new Element('mail_from');
        $mail_from->setAttribute('type', 'text')
            ->setAttribute('label', 'From E-mail')
            ->setAttribute('class', 'input-text');

        $mail_from_name = new Element('mail_from_name');
        $mail_from_name->setAttribute('type', 'text')
            ->setAttribute('label', 'From name')
            ->setAttribute('class', 'input-text');

        $mail_fieldset->add($mail_from);
        $mail_fieldset->add($mail_from_name);
        $this->add($mail_fieldset);

        $this->getInputFilter()->add(array(
            'name' => 'locale',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'locale');

        $this->getInputFilter()->add(array(
            'name' => 'mail_from',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'mail_from');

        $this->getInputFilter()->add(array(
            'name' => 'mail_from_name',
            'required' => TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'mail_from_name');

        return $this;
    }

    /**
     * Set config values from database result
     * @param array $data
     */
    public function setValues(array $data)
    {
        foreach($data as $config)
        {
            foreach($this->getFieldsets() as $fieldset)
            {
                if($fieldset->has($config['identifier']))
                {
                    $element = $fieldset->get($config['identifier']);
                    if($element->getAttribute('type') == 'checkbox')
                    {
                        if(!empty($config['value']))
                        {
                            $element->setAttribute('checked', 'checked');
                        }
                    }
                    else
                    {
                        $element->setAttribute('value', $config['value']);
                    }

                    break;
                }
            }
        }
    }
}
