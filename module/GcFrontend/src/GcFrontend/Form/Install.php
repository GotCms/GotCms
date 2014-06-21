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
 * @package    GcFrontend
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcFrontend\Form;

use Gc\Form\AbstractForm;
use Gc\Media\Info;
use Zend\Validator\Db;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;

/**
 * Install form
 *
 * @category   Gc_Application
 * @package    GcFrontend
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
     * @param array $config Configuration
     *
     * @return void
     */
    public function lang($config)
    {
        $lang = new Element\Select('lang');
        $lang->setAttribute('size', 10)
            ->setValueOptions($config['locales'])
            ->setValue('en_GB')
            ->setAttribute('class', 'form-control');

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
            ->setLabel('Driver')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            )
            ->setAttribute('id', 'driver')
            ->setAttribute('class', 'form-control');

        $hostname = new Element\Text('hostname');
        $hostname->setValue('localhost')
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'hostname')
            ->setLabel('Hostname')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            );

        $username = new Element\Text('username');
        $username->setAttribute('type', 'text')
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'username')
            ->setLabel('Username')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            );

        $password = new Element\Password('password');
        $password->setAttribute('class', 'form-control')
            ->setAttribute('id', 'password')
            ->setLabel('Password')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            );

        $dbname = new Element\Text('dbname');
        $dbname->setAttribute('class', 'form-control')
            ->setAttribute('id', 'dbname')
            ->setLabel('Db Name')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            );

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
        $siteName->setLabel('Site name')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            )
            ->setAttribute('id', 'site_name')
            ->setAttribute('class', 'form-control');

        $siteIsOffline = new Element\Checkbox('site_is_offline');
        $siteIsOffline->setLabel('Is offline')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            )
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'is-offline')
            ->setCheckedValue('1');

        $adminEmail = new Element\Text('admin_email');
        $adminEmail->setAttribute('type', 'text')
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'admin_email')
            ->setLabel('Email')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            );

        $adminFirstname = new Element\Text('admin_firstname');
        $adminFirstname->setAttribute('type', 'text')
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'admin_firstname')
            ->setLabel('Firstname')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            );

        $adminLastname = new Element\Text('admin_lastname');
        $adminLastname->setAttribute('type', 'text')
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'admin_lastname')
            ->setLabel('Lastname')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            );

        $adminLogin = new Element\Text('admin_login');
        $adminLogin->setLabel('Login')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            )
            ->setAttribute('id', 'admin_login')
            ->setAttribute('class', 'form-control');

        $adminPassword = new Element\Password('admin_password');
        $adminPassword->setLabel('Admin password')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            )
            ->setAttribute('id', 'admin_password')
            ->setAttribute('class', 'form-control');

        $adminPasswordConfirm = new Element\Password('admin_password_confirm');
        $adminPasswordConfirm->setLabel('Confirm admin password')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            )
            ->setAttribute('id', 'admin_password_confirm')
            ->setAttribute('class', 'form-control');

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
        $template->setLabel('Default template')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label required col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'template')
            ->setAttribute('data', $renderOptions)
            ->setValueOptions($options);

        $copyTranslations = new Element\Checkbox('copy_translations');
        $copyTranslations->setLabel('Copy translations')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'copy-translations')
            ->setValue('1')
            ->setCheckedValue('1');


        $this->add($siteName);
        $this->add($siteIsOffline);
        $this->add($adminEmail);
        $this->add($adminFirstname);
        $this->add($adminLastname);
        $this->add($adminLogin);
        $this->add($adminPassword);
        $this->add($adminPasswordConfirm);
        $this->add($template);
        $this->add($copyTranslations);

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

        $inputFilter->add(
            array(
                'name' => 'copy_translations',
                'required' => false,
            ),
            'copy_translations'
        );
    }
}
