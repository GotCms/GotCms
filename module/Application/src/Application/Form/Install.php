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
 * @category Form
 * @package  Application
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Application\Form;

use Gc\Form\AbstractForm,
    Zend\Validator\Db,
    Zend\Form\Element,
    Zend\InputFilter\InputFilter,
    Zend\Locale\Locale;

class Install extends AbstractForm
{
    /**
     * Init Install form
     * @return void
     */
    public function init()
    {
        $this->setInputFilter(new InputFilter());
    }

    public function lang()
    {
        $country_available = array(
            'fr_FR' => 'Français',
            'en_GB' => 'English',
        );

        $lang = new Element\Select('lang');
        $lang->setAttribute('size', 10)
            ->setValueOptions($country_available);

        $input_filter = $this->getInputFilter();
        $inputFilter = $input_filter->add(array(
            'name' => 'lang',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'lang');

        $this->add($lang);
    }

    public function license()
    {
        $accept_license = new Element\Checkbox('accept-license');
        $accept_license->setCheckedValue('1');
        $accept_license->setUseHiddenElement(FALSE);

        $input_filter = $this->getInputFilter();
        $inputFilter = $input_filter->add(array(
            'name' => 'accept-license',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
                array(
                    'name' => 'greaterthan',
                    'options' => array(
                        'min' => 0
                    )
                ),
            ),
        ), 'accept-license');

        $this->add($accept_license);
    }

    public function database()
    {
        $data = array(
            'pdo_pgsql' => 'PostgreSQL',
            'pdo_mysql' => 'MySQL'
        );

        $driver = new Element\Select('driver');
        $driver->setValueOptions($data)
            ->setAttribute('label', 'Driver');

        $hostname = new Element\Text('hostname');
        $hostname->setValue('localhost')
            ->setAttribute('label', 'Hostname');

        $username = new Element\Text('username');
        $username->setAttribute('type', 'text')
            ->setAttribute('label', 'Username');

        $password = new Element\Password('password');
        $password->setAttribute('label', 'Password');

        $dbname = new Element\Text('dbname');
        $dbname->setAttribute('label', 'Db Name');

        $this->add($driver);
        $this->add($hostname);
        $this->add($dbname);
        $this->add($username);
        $this->add($password);

        $input_filter = $this->getInputFilter();
        $inputFilter = $input_filter->add(array(
            'name' => 'driver',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'driver');

        $inputFilter = $input_filter->add(array(
            'name' => 'hostname',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'hostname');

        $inputFilter = $input_filter->add(array(
            'name' => 'username',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'username');

        $inputFilter = $input_filter->add(array(
            'name' => 'password',
            'required'=> FALSE,
        ), 'password');

        $inputFilter = $input_filter->add(array(
            'name' => 'dbname',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'dbname');
    }

    public function configuration()
    {
        $site_name = new Element\Text('site_name');
        $site_name->setAttribute('label', 'Site name');

        $site_is_offline = new Element\Checkbox('site_is_offline');
        $site_is_offline->setAttribute('label', 'Is offline')
            ->setCheckedValue('1');

        $admin_email = new Element\Text('admin_email');
        $admin_email->setAttribute('type', 'text')
            ->setAttribute('label', 'Email');

        $admin_login = new Element\Text('admin_login');
        $admin_login->setAttribute('label', 'Login');

        $admin_password = new Element\Password('admin_password');
        $admin_password->setAttribute('label', 'Admin password');

        $admin_password_confirm = new Element\Password('admin_passowrd_confirm');
        $admin_password_confirm->setAttribute('label', 'Confirm admin password');

        $path = GC_APPLICATION_PATH . '/data/install/templates/';
        $list_dir = glob($path.'*', GLOB_ONLYDIR);
        $options = array('' => 'Select template');
        foreach($list_dir as $dir)
        {
            $dir = str_replace($path, '', $dir);
            $options[$dir] = $dir;
        }

        $template = new Element\Select('template');
        $template->setAttribute('label', 'Default template');
        $template->setValueOptions($options);


        $this->add($site_name);
        $this->add($site_is_offline);
        $this->add($admin_email);
        $this->add($admin_login);
        $this->add($admin_password);
        $this->add($admin_password_confirm);
        $this->add($template);


        $input_filter = $this->getInputFilter();
        $inputFilter = $input_filter->add(array(
            'name' => 'site_name',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'site_name');

        $inputFilter = $input_filter->add(array(
            'name' => 'admin_email',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
                array('name' => 'email_address'),
            ),
        ), 'admin_email');

        $inputFilter = $input_filter->add(array(
            'name' => 'admin_login',
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'admin_login');

        $inputFilter = $input_filter->add(array(
            'name' => 'site_is_offline',
            'required'=> FALSE,
        ), 'site_is_offline');

        $inputFilter = $input_filter->add(array(
            'name' => 'admin_password',
            'required'=> FALSE,
        ), 'admin_password');

        $inputFilter = $input_filter->add(array(
            'name' => 'template',
            'required'=> TRUE,
        ), 'template');
    }
}
