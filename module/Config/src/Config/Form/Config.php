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
        $generalFieldset = new Fieldset('general');
        $name            = new Element\Text('site_name');
        $name->setAttribute('label', 'Site name')
            ->setAttribute('class', 'input-text');

        $isOffline = new Element\Checkbox('site_is_offline');
        $isOffline->setAttribute('label', 'Is offline')
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'site-offiline')
            ->setCheckedValue('1');

        $documentCollection = new Document\Collection();
        $documentCollection->load(0);
        $offlineDocument = new Element\Select('site_offline_document');
        $offlineDocument->setAttribute('label', 'Offline document')
            ->setAttribute('class', 'input-select')
            ->setValueOptions(array('Select document') + $documentCollection->getSelect());

        $layoutCollection = new Layout\Collection();
        $layoutNotFound   = new Element\Select('site_404_layout');
        $layoutNotFound->setAttribute('label', '404 layout')
            ->setAttribute('class', 'input-select')
            ->setValueOptions(array('Select document') + $layoutCollection->getSelect());

        $layoutException = new Element\Select('site_exception_layout');
        $layoutException->setAttribute('label', 'Exception layout')
            ->setAttribute('class', 'input-select')
            ->setValueOptions(array('Select document') + $layoutCollection->getSelect());

        $generalFieldset->add($name);
        $generalFieldset->add($isOffline);
        $generalFieldset->add($offlineDocument);
        $generalFieldset->add($layoutNotFound);
        $generalFieldset->add($layoutException);
        $this->add($generalFieldset);

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
        $sessionFieldset = new Fieldset('session');
        $cookieDomain    = new Element\Text('cookie_domain');
        $cookieDomain->setAttribute('label', 'Cookie domain')
            ->setAttribute('class', 'input-text');

        $cookiePath = new Element\Text('cookie_path');
        $cookiePath->setAttribute('label', 'Cookie path')
            ->setAttribute('class', 'input-text');

        $sessionLifetime = new Element\Text('session_lifetime');
        $sessionLifetime->setAttribute('label', 'Session lifetime')
            ->setAttribute('class', 'input-text');

        $sessionHandler = new Element\Select('session_handler');
        $sessionHandler->setAttribute('label', 'Session handler')
            ->setAttribute('class', 'input-select')
            ->setValueOptions(array('0' => 'Files', '1' => 'Database'));

        $sessionFieldset->add($cookieDomain);
        $sessionFieldset->add($cookiePath);
        $sessionFieldset->add($sessionHandler);
        $sessionFieldset->add($sessionLifetime);
        $this->add($sessionFieldset);

        //Debug settings
        $debugFieldset = new Fieldset('debug');
        $debugIsActive = new Element\Checkbox('debug_is_active');
        $debugIsActive->setAttribute('label', 'Debug is active')
            ->setAttribute('id', 'input-checkbox')
            ->setAttribute('class', 'input-checkbox');

        $debugFieldset->add($debugIsActive);
        $this->add($debugFieldset);

        //Debug settings
        $cacheFieldset = new Fieldset('cache');
        $cacheIsActive = new Element\Checkbox('cache_is_active');
        $cacheIsActive->setAttribute('label', 'Cache is active')
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'cache-active');

        $cacheHandler = new Element\Select('cache_handler');
        $cacheHandler->setAttribute('class', 'input-select')
            ->setAttribute('label', 'Cache handler');
        $handlerWhitelist = array('filesystem' => 'FileSystem');
        if (extension_loaded('apc')) {
            $handlerWhitelist['apc'] = 'Apc';
        }

        if (extension_loaded('memcached')) {
            $handlerWhitelist['memcached'] = 'Memcached';
        }

        $cacheHandler->setValueOptions($handlerWhitelist);

        $cacheLifetime = new Element\Text('cache_lifetime');
        $cacheLifetime->setAttribute('label', 'Cache lifetime')
            ->setAttribute('class', 'input-text');

        $cacheFieldset->add($cacheIsActive);
        $cacheFieldset->add($cacheHandler);
        $cacheFieldset->add($cacheLifetime);
        $this->add($cacheFieldset);

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
        $localeList = array(
            'fr_FR' => 'Français',
            'en_GB' => 'English',
        );

        $localeFieldset = new Fieldset('locale');
        $locale         = new Element\Select('locale');
        $locale->setAttribute('label', 'Server locale')
            ->setAttribute('class', 'input-select')
            ->setValueOptions($localeList);

        $localeFieldset->add($locale);
        $this->add($localeFieldset);

        //Mail settings
        $mailFieldset = new Fieldset('mail');
        $mailFrom     = new Element\Text('mail_from');
        $mailFrom->setAttribute('label', 'From E-mail')
            ->setAttribute('class', 'input-text');

        $mailFromName = new Element\Text('mail_from_name');
        $mailFromName->setAttribute('label', 'From name')
            ->setAttribute('class', 'input-text');

        $mailFieldset->add($mailFrom);
        $mailFieldset->add($mailFromName);
        $this->add($mailFieldset);

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
