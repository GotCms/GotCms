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
    Gc\User\Permission,
    Zend\Form\Element,
    Zend\Form\Fieldset,
    Zend\InputFilter\InputFilter,
    Zend\Validator\Db,
    Zend\Validator\Identical,
    Zend\Locale\Locale;

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

        $is_offline = new Element('is_offline');
        $is_offline->setAttribute('label', 'Is offline')
            ->setAttribute('type', 'checkbox');

        $offline_document = new Element('offline_document');
        $offline_document->setAttribute('label', 'Offline document')
            ->setAttribute('type', 'select')
            ->setAttribute('options', array())
            ->setAttribute('class', 'input-text');

        $general_fieldset->add($name);
        $general_fieldset->add($is_offline);
        $this->add($general_fieldset);

        $this->getInputFilter()->add(array(
            'site_name' => array(
                'name' => 'site_name',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'is_offline' => array(
                'name' => 'site_name',
                'required' => TRUE,
            ),
            'offline_document' => array(
                'name' => 'site_name',
                'required' => TRUE,
            ),
        ));

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
        $general_fieldset = new Fieldset('debug');
        $debug_is_active = new Element('debug_is_active');
        $debug_is_active->setAttribute('label', 'Is active')
            ->setAttribute('class', 'input-text');

        $this->add($debug_is_active);

        $this->getInputFilter()->add(array(
            'cookie_domain' => array(
                'name' => 'cookie_domain',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'cookie_path' => array(
                'name' => 'cookie_path',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'session_lifetime' => array(
                'name' => 'session_lifetime',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'session_handler' => array(
                'name' => 'session_handler',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'debug_is_active' => array(
                'name' => 'is_active',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
        ));

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
        $locale_fieldset = new Fieldset('locale');
        $locale = new Element('locale');
        $locale->setAttribute('type', 'select')
            ->setAttribute('label', 'Server locale')
            ->setAttribute('options', Locale::getTranslation());

        $locale_settings->add(array($locale));
        $this->add($locale_settings);

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
            'locale' => array(
                'name' => 'locale',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'mail_from' => array(
                'name' => 'mail_from',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'mail_from_name' => array(
                'name' => 'mail_from_name',
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
        ));

        return $this;
    }
}
