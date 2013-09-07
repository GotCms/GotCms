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
        $generalFieldset->setLabel('General');
        $name = new Element\Text('site_name');
        $name->setAttribute('label', 'Site name')
            ->setAttribute('id', 'site_name')
            ->setAttribute('class', 'form-control');
        $generalFieldset->add($name);

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

        $isOffline = new Element\Checkbox('site_is_offline');
        $isOffline->setAttribute('label', 'Is offline')
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'site-offiline')
            ->setCheckedValue('1');
        $generalFieldset->add($isOffline);

        $this->getInputFilter()->add(
            array(
                'name' => 'site_is_offline',
                'required' => false,
            ),
            'site_is_offline'
        );

        $documentCollection = new Document\Collection();
        $documentCollection->load(0);
        $offlineDocument = new Element\Select('site_offline_document');
        $offlineDocument->setAttribute('label', 'Offline document')
            ->setAttribute('class', 'input-select')
            ->setAttribute('id', 'site_offline_document')
            ->setValueOptions(array('Select document') + $documentCollection->getSelect());
        $generalFieldset->add($offlineDocument);

        $this->getInputFilter()->add(
            array(
                'name' => 'site_offline_document',
                'required' => true,
            ),
            'site_offline_document'
        );

        $layoutCollection = new Layout\Collection();
        $layoutNotFound   = new Element\Select('site_404_layout');
        $layoutNotFound->setAttribute('label', '404 layout')
            ->setAttribute('class', 'input-select')
            ->setAttribute('id', 'site_404_layout')
            ->setValueOptions(array('Select document') + $layoutCollection->getSelect());
        $generalFieldset->add($layoutNotFound);

        $this->getInputFilter()->add(
            array(
                'name' => 'site_404_layout',
                'required' => true,
            ),
            'site_404_layout'
        );

        $layoutException = new Element\Select('site_exception_layout');
        $layoutException->setAttribute('label', 'Exception layout')
            ->setAttribute('class', 'input-select')
            ->setAttribute('id', 'site_exception_layout')
            ->setValueOptions(array('Select document') + $layoutCollection->getSelect());
        $generalFieldset->add($layoutException);
        $this->getInputFilter()->add(
            array(
                'name' => 'site_exception_layout',
                'required' => true,
            ),
            'site_exception_layout'
        );

        $this->add($generalFieldset);

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
        $sessionFieldset->setLabel('Session');
        $cookieDomain = new Element\Text('cookie_domain');
        $cookieDomain->setAttribute('label', 'Cookie domain')
            ->setAttribute('id', 'cookie_domain')
            ->setAttribute('class', 'form-control');
        $sessionFieldset->add($cookieDomain);

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

        $cookiePath = new Element\Text('cookie_path');
        $cookiePath->setAttribute('label', 'Cookie path')
            ->setAttribute('id', 'cookie_path')
            ->setAttribute('class', 'form-control');
        $sessionFieldset->add($cookiePath);

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

        $sessionHandler = new Element\Select('session_handler');
        $sessionHandler->setAttribute('label', 'Session handler')
            ->setAttribute('class', 'input-select')
            ->setAttribute('id', 'session_handler')
            ->setValueOptions(array('0' => 'Files', '1' => 'Database'));
        $sessionFieldset->add($sessionHandler);

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

        $sessionPath = new Element\Text('session_path');
        $sessionPath->setAttribute('label', 'Session path')
            ->setAttribute('id', 'session_path')
            ->setAttribute('class', 'form-control');
        $sessionFieldset->add($sessionPath);

        $this->getInputFilter()->add(
            array(
                'name' => 'session_path',
                'required' => false,
            ),
            'session_path'
        );

        $sessionLifetime = new Element\Text('session_lifetime');
        $sessionLifetime->setAttribute('label', 'Session lifetime')
            ->setAttribute('id', 'session_lifetime')
            ->setAttribute('class', 'form-control');
        $sessionFieldset->add($sessionLifetime);

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

        $this->add($sessionFieldset);

        //Debug settings
        $debugFieldset = new Fieldset('debug');
        $debugFieldset->setLabel('Debug');
        $debugIsActive = new Element\Checkbox('debug_is_active');
        $debugIsActive->setAttribute('label', 'Debug is active')
            ->setAttribute('id', 'debug_is_active')
            ->setAttribute('id', 'input-checkbox')
            ->setAttribute('class', 'input-checkbox');
        $debugFieldset->add($debugIsActive);

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

        $this->add($debugFieldset);

        //Debug settings
        $cacheFieldset = new Fieldset('cache');
        $cacheFieldset->setLabel('Cache');
        $cacheIsActive = new Element\Checkbox('cache_is_active');
        $cacheIsActive->setAttribute('label', 'Cache is active')
            ->setAttribute('id', 'cache_is_active')
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'cache-active');
        $cacheFieldset->add($cacheIsActive);

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

        $cacheHandler = new Element\Select('cache_handler');
        $cacheHandler->setAttribute('class', 'input-select')
            ->setAttribute('id', 'cache_handler')
            ->setAttribute('label', 'Cache handler');
        $handlerWhitelist = array('filesystem' => 'FileSystem');
        if (extension_loaded('apc')) {
            $handlerWhitelist['apc'] = 'Apc';
        }

        if (extension_loaded('memcached')) {
            $handlerWhitelist['memcached'] = 'Memcached';
        }

        $cacheHandler->setValueOptions($handlerWhitelist);
        $cacheFieldset->add($cacheHandler);

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

        $cacheLifetime = new Element\Text('cache_lifetime');
        $cacheLifetime->setAttribute('label', 'Cache lifetime')
            ->setAttribute('id', 'cache_lifetime')
            ->setAttribute('class', 'form-control');
        $cacheFieldset->add($cacheLifetime);

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

        $this->add($cacheFieldset);

        return $this;
    }

    /**
     * Initialize Server sub form
     *
     * @param array $config Configuration
     *
     * @return \Config\Form\Config
     */
    public function initServer($config)
    {
        //Local settings
        $localeFieldset = new Fieldset('locale');
        $localeFieldset->setLabel('Locale');
        $locale = new Element\Select('locale');
        $locale->setAttribute('label', 'Server locale')
            ->setAttribute('id', 'locale')
            ->setAttribute('class', 'input-select')
            ->setValueOptions($config['locales']);
        $localeFieldset->add($locale);

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

        $this->add($localeFieldset);

        //Mail settings
        $mailFieldset = new Fieldset('mail');
        $mailFieldset->setLabel('Mail');
        $mailFrom = new Element\Text('mail_from');
        $mailFrom->setAttribute('label', 'From E-mail')
            ->setAttribute('id', 'mail_from')
            ->setAttribute('class', 'form-control');
        $mailFieldset->add($mailFrom);

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

        $mailFromName = new Element\Text('mail_from_name');
        $mailFromName->setAttribute('label', 'From name')
            ->setAttribute('id', 'mail_from_name')
            ->setAttribute('class', 'form-control');
        $mailFieldset->add($mailFromName);

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

        $this->add($mailFieldset);

        //Web settings
        $webFieldset = new Fieldset('web');
        $webFieldset->setLabel('Web');

        $forceBackendSsl = new Element\Checkbox('force_backend_ssl');
        $forceBackendSsl->setAttribute('label', 'Force backend SSL')
            ->setAttribute('id', 'force_backend_ssl')
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'force-backend-ssl');
        $webFieldset->add($forceBackendSsl);

        $this->getInputFilter()->add(
            array(
                'name' => 'force_backend_ssl',
                'required' => false,
            ),
            'force_backend_ssl'
        );

        $forceFrontendSsl = new Element\Checkbox('force_frontend_ssl');
        $forceFrontendSsl->setAttribute('label', 'Force frontend SSL')
            ->setAttribute('id', 'force_frontend_ssl')
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'force-frontend-ssl');
        $webFieldset->add($forceFrontendSsl);

        $this->getInputFilter()->add(
            array(
                'name' => 'force_frontend_ssl',
                'required' => false,
            ),
            'force_frontend_ssl'
        );

        $pathFields = array(
            'Unsecure backend base path'  => 'unsecure_backend_base_path',
            'Unsecure frontend base path' => 'unsecure_frontend_base_path',
            'Secure backend base path' => 'secure_backend_base_path',
            'Secure frontend base path' => 'secure_frontend_base_path',
            'Unsecure cdn base path' => 'unsecure_cdn_base_path',
            'Secure cdn base path' => 'secure_cdn_base_path',
        );

        foreach ($pathFields as $label => $identifier) {
            $field = new Element\Text($identifier);
            $field->setAttribute('label', $label)
                ->setAttribute('id', $identifier)
                ->setAttribute('class', 'form-control');
            $webFieldset->add($field);

            $this->getInputFilter()->add(
                array(
                    'name' => $identifier,
                    'required' => false,
                    'validators' => array(
                        array('name' => 'uri'),
                    ),
                ),
                $identifier
            );
        }

        $this->add($webFieldset);

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
