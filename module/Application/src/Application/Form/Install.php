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
 * @package    Application
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Application\Form;

use Gc\Form\AbstractForm;
use Gc\Media\Info;
use Zend\Validator\Db;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\Locale\Locale;

/**
 * Install form
 *
 * @category   Gc_Application
 * @package    Application
 * @subpackage Form
 */
class Install extends AbstractForm
{
    /**
     * Init Install form
     *
     * @return void
     */
    public function init()
    {
        $this->setInputFilter(new InputFilter());
    }

    /**
     * Language form
     *
     * @return void
     */
    public function lang()
    {
        $countryAvailable = array(
            'en_GB' => 'English',
            'fr_FR' => 'Français',
        );

        $lang = new Element\Select('lang');
        $lang->setAttribute('size', 10)
            ->setValueOptions($countryAvailable)
            ->setValue('en_GB')
            ->setAttribute('class', 'input-select');

        $inputFilter = $this->getInputFilter();
        $inputFilter->add(
            array(
                'name' => 'lang',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'lang'
        );

        $this->add($lang);
    }

    /**
     * License form
     *
     * @return void
     */
    public function license()
    {
        $acceptLicense = new Element\Checkbox('accept-license');
        $acceptLicense->setCheckedValue('1')
            ->setUseHiddenElement(false)
            ->setAttribute('id', 'accept-license')
            ->setAttribute('class', 'input-checkbox');

        $inputFilter = $this->getInputFilter();
        $inputFilter->add(
            array(
                'name' => 'accept-license',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array(
                        'name' => 'greaterthan',
                        'options' => array(
                            'min' => 0
                        )
                    ),
                ),
            ),
            'accept-license'
        );

        $this->add($acceptLicense);
    }

    /**
     * Database form
     *
     * @return void
     */
    public function database()
    {
        $data = array(
            'pdo_pgsql' => 'PostgreSQL',
            'pdo_mysql' => 'MySQL'
        );

        $driver = new Element\Select('driver');
        $driver->setValueOptions($data)
            ->setAttribute('label', 'Driver')
            ->setAttribute('class', 'input-select');

        $hostname = new Element\Text('hostname');
        $hostname->setValue('localhost')
            ->setAttribute('class', 'input-text')
            ->setAttribute('label', 'Hostname');

        $username = new Element\Text('username');
        $username->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text')
            ->setAttribute('label', 'Username');

        $password = new Element\Password('password');
        $password->setAttribute('class', 'input-text');
        $password->setAttribute('label', 'Password');

        $dbname = new Element\Text('dbname');
        $dbname->setAttribute('class', 'input-text');
        $dbname->setAttribute('label', 'Db Name');

        $this->add($driver);
        $this->add($hostname);
        $this->add($dbname);
        $this->add($username);
        $this->add($password);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add(
            array(
                'name' => 'driver',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'driver'
        );

        $inputFilter->add(
            array(
                'name' => 'hostname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'hostname'
        );

        $inputFilter->add(
            array(
                'name' => 'username',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'username'
        );

        $inputFilter->add(
            array(
                'name' => 'password',
                'required' => false,
            ),
            'password'
        );

        $inputFilter->add(
            array(
                'name' => 'dbname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'dbname'
        );
    }

    /**
     * Configuration form
     *
     * @return void
     */
    public function configuration()
    {
        $siteName = new Element\Text('site_name');
        $siteName->setAttribute('label', 'Site name')
            ->setAttribute('class', 'input-text');

        $siteIsOffline = new Element\Checkbox('site_is_offline');
        $siteIsOffline->setAttribute('label', 'Is offline')
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'is-offline')
            ->setCheckedValue('1');

        $adminEmail = new Element\Text('admin_email');
        $adminEmail->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text')
            ->setAttribute('label', 'Email');

        $adminFirstname = new Element\Text('admin_firstname');
        $adminFirstname->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text')
            ->setAttribute('label', 'Firstname');

        $adminLastname = new Element\Text('admin_lastname');
        $adminLastname->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text')
            ->setAttribute('label', 'Lastname');

        $adminLogin = new Element\Text('admin_login');
        $adminLogin->setAttribute('label', 'Login')
            ->setAttribute('class', 'input-text');

        $adminPassword = new Element\Password('admin_password');
        $adminPassword->setAttribute('label', 'Admin password')
            ->setAttribute('class', 'input-text');

        $adminPasswordConfirm = new Element\Password('admin_passowrd_confirm');
        $adminPasswordConfirm->setAttribute('label', 'Confirm admin password')
            ->setAttribute('class', 'input-text');

        $path          = GC_APPLICATION_PATH . '/data/install/design/';
        $listDir       = glob($path . '*', GLOB_ONLYDIR);
        $options       = array('' => 'Select template');
        $renderOptions = array();
        foreach ($listDir as $dir) {
            $dir           = str_replace($path, '', $dir);
            $options[$dir] = $dir;
            $info          = new Info();
            $info->fromFile($path . $dir . '/design.info');
            $designInfos = $info->getInfos();
            if (!empty($designInfos)) {
                $renderOptions[$dir] = $info->render();
            }
        }

        $template = new Element\Select('template');
        $template->setAttribute('label', 'Default template')
            ->setAttribute('class', 'input-select')
            ->setAttribute('id', 'template')
            ->setAttribute('data', $renderOptions)
            ->setValueOptions($options);


        $this->add($siteName);
        $this->add($siteIsOffline);
        $this->add($adminEmail);
        $this->add($adminFirstname);
        $this->add($adminLastname);
        $this->add($adminLogin);
        $this->add($adminPassword);
        $this->add($adminPasswordConfirm);
        $this->add($template);


        $inputFilter = $this->getInputFilter();
        $inputFilter->add(
            array(
                'name' => 'site_name',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'site_name'
        );

        $inputFilter->add(
            array(
                'name' => 'admin_firstname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'admin_firstname'
        );

        $inputFilter->add(
            array(
                'name' => 'admin_lastname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'admin_lastname'
        );

        $inputFilter->add(
            array(
                'name' => 'admin_email',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array('name' => 'email_address'),
                ),
            ),
            'admin_email'
        );

        $inputFilter->add(
            array(
                'name' => 'admin_login',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'admin_login'
        );

        $inputFilter->add(
            array(
                'name' => 'site_is_offline',
                'required' => false,
            ),
            'site_is_offline'
        );

        $inputFilter->add(
            array(
                'name' => 'admin_password',
                'required' => false,
            ),
            'admin_password'
        );

        $inputFilter->add(
            array(
                'name' => 'template',
                'required' => true,
            ),
            'template'
        );
    }
}
