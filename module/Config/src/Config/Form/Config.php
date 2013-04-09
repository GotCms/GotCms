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
 * @package    Config
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Config\Form;

use Gc\Form\AbstractForm;
use Gc\Document;
use Gc\Layout;
use Gc\User\Permission;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Db;
use Zend\Validator\Identical;

/**
 * Config form
 *
 * @category   Gc_Application
 * @package    Config
 * @subpackage Form
 */
class Config extends AbstractForm
{
    /**
     * Initialize form
     *
     * @return void
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
        $name             = new Element\Text('site_name');
        $name->setAttribute('label', 'Site name')
            ->setAttribute('class', 'input-text');

        $is_offline = new Element\Checkbox('site_is_offline');
        $is_offline->setAttribute('label', 'Is offline')
            ->setCheckedValue('1');

        $document_collection = new Document\Collection();
        $document_collection->load(0);
        $offline_document = new Element\Select('site_offline_document');
        $offline_document->setAttribute('label', 'Offline document');
        $offline_document->setValueOptions(array('Select document') + $document_collection->getSelect());

        $layout_collection = new Layout\Collection();
        $layout_not_found  = new Element\Select('site_404_layout');
        $layout_not_found->setAttribute('label', '404 layout');
        $layout_not_found->setValueOptions(array('Select document') + $layout_collection->getSelect());

        $layout_exception  = new Element\Select('site_exception_layout');
        $layout_exception->setAttribute('label', 'Exception layout');
        $layout_exception->setValueOptions(array('Select document') + $layout_collection->getSelect());

        $general_fieldset->add($name);
        $general_fieldset->add($is_offline);
        $general_fieldset->add($offline_document);
        $general_fieldset->add($layout_not_found);
        $general_fieldset->add($layout_exception);
        $this->add($general_fieldset);

        $this->getInputFilter()->add(
            array(
                'name' => 'site_name',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'site_name'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'site_is_offline',
                'required' => false,
            ),
            'site_is_offline'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'site_offline_document',
                'required' => true,
            ),
            'site_offline_document'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'site_404_layout',
                'required' => true,
            ),
            'site_404_layout'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'site_exception_layout',
                'required' => true,
            ),
            'site_exception_layout'
        );

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
        $cookie_domain    = new Element\Text('cookie_domain');
        $cookie_domain->setAttribute('label', 'Cookie domain')
            ->setAttribute('class', 'input-text');

        $cookie_path = new Element\Text('cookie_path');
        $cookie_path->setAttribute('label', 'Cookie path')
            ->setAttribute('class', 'input-text');

        $session_lifetime = new Element\Text('session_lifetime');
        $session_lifetime->setAttribute('label', 'Session lifetime')
            ->setAttribute('class', 'input-text');

        $session_handler = new Element\Select('session_handler');
        $session_handler->setAttribute('label', 'Session handler')
            ->setValueOptions(array('0' => 'Files', '1' => 'Database'));

        $session_fieldset->add($cookie_domain);
        $session_fieldset->add($cookie_path);
        $session_fieldset->add($session_handler);
        $session_fieldset->add($session_lifetime);
        $this->add($session_fieldset);

        //Debug settings
        $debug_fieldset  = new Fieldset('debug');
        $debug_is_active = new Element\Checkbox('debug_is_active');
        $debug_is_active->setAttribute('label', 'Debug is active')
            ->setAttribute('class', 'input-text');

        $debug_fieldset->add($debug_is_active);
        $this->add($debug_fieldset);

        //Debug settings
        $cache_fieldset  = new Fieldset('cache');
        $cache_is_active = new Element\Checkbox('cache_is_active');
        $cache_is_active->setAttribute('label', 'Cache is active')
            ->setAttribute('class', 'input-text');

        $cache_handler = new Element\Select('cache_handler');
        $cache_handler->setAttribute('label', 'Cache handler');
        $handler_whitelist = array('filesystem' => 'FileSystem');
        if (extension_loaded('apc')) {
            $handler_whitelist['apc'] = 'Apc';
        }

        if (extension_loaded('memcached')) {
            $handler_whitelist['memcached'] = 'Memcached';
        }

        $cache_handler->setValueOptions($handler_whitelist);

        $cache_lifetime = new Element\Text('cache_lifetime');
        $cache_lifetime->setAttribute('label', 'Cache lifetime')
            ->setAttribute('class', 'input-text');

        $cache_fieldset->add($cache_is_active);
        $cache_fieldset->add($cache_handler);
        $cache_fieldset->add($cache_lifetime);
        $this->add($cache_fieldset);

        $this->getInputFilter()->add(
            array(
                'name' => 'cookie_domain',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'cookie_domain'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'cookie_path',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'cookie_path'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'session_lifetime',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array('name' => 'digits'),
                ),
            ),
            'session_lifetime'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'session_handler',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'session_handler'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'debug_is_active',
                'required' => false,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'debug_is_active'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'cache_is_active',
                'required' => false,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'cache_is_active'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'cache_lifetime',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array('name' => 'digits'),
                ),
            ),
            'cache_lifetime'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'cache_handler',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'cache_handler'
        );

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
            'fr_FR' => 'Français',
            'en_GB' => 'English',
        );

        $locale_fieldset = new Fieldset('locale');
        $locale          = new Element\Select('locale');
        $locale->setAttribute('label', 'Server locale')
            ->setValueOptions($locale_list);

        $locale_fieldset->add($locale);
        $this->add($locale_fieldset);

        //Mail settings
        $mail_fieldset = new Fieldset('mail');
        $mail_from     = new Element\Text('mail_from');
        $mail_from->setAttribute('label', 'From E-mail')
            ->setAttribute('class', 'input-text');

        $mail_from_name = new Element\Text('mail_from_name');
        $mail_from_name->setAttribute('label', 'From name')
            ->setAttribute('class', 'input-text');

        $mail_fieldset->add($mail_from);
        $mail_fieldset->add($mail_from_name);
        $this->add($mail_fieldset);

        $this->getInputFilter()->add(
            array(
                'name' => 'locale',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'locale'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'mail_from_name',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'mail_from_name'
        );

        $this->getInputFilter()->add(
            array(
                'name' => 'mail_from',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'mail_from'
        );


        return $this;
    }

    /**
     * Set config values from database result
     *
     * @param array $data The data as array will by passed into form field
     *
     * @return void
     */
    public function setValues(array $data)
    {
        foreach ($data as $config) {
            foreach ($this->getFieldsets() as $fieldset) {
                if ($fieldset->has($config['identifier'])) {
                    $fieldset->get($config['identifier'])->setValue($config['value']);
                    break;
                }
            }
        }
    }
}
