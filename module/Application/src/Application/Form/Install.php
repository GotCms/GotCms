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
            'Français' => 'fr_FR',
            'English' => 'en_GB',
        );

        $lang = new Element('lang');
        $lang->setAttribute('size', 10)
            ->setAttribute('type', 'select')
            ->setAttribute('options', $country_available);

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
        $accept_license = new Element('accept-license');
        $accept_license->setAttribute('type', 'checkbox')
            ->setAttribute('checkedValue', '1');

        $input_filter = $this->getInputFilter();
        $inputFilter = $input_filter->add(array(
            'required'=> TRUE,
            'validators' => array(
                array('name' => 'not_empty'),
            ),
        ), 'accept-license');

        $this->add($accept_license);
    }

    public function database()
    {
        $data = array('PostgreSQL' => 'pdo_pgsql', 'MySQL' => 'pdo_mysql');

        $driver = new Element('driver');
        $driver->setAttribute('type', 'select')
            ->setAttribute('options', $data)
            ->setAttribute('label', 'Driver');

        $hostname = new Element('hostname');
        $hostname->setAttribute('type', 'text')
            ->setAttribute('value', 'localhost')
            ->setAttribute('label', 'Hostname');

        $username = new Element('username');
        $username->setAttribute('type', 'text')
            ->setAttribute('label', 'Username');

        $password = new Element('password');
        $password->setAttribute('type', 'password')
            ->setAttribute('label', 'Password');

        $dbname = new Element('dbname');
        $dbname->setAttribute('type', 'text')
            ->setAttribute('label', 'Db Name');

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
        $site_name = new Element('site_name');
        $site_name->setAttribute('type', 'text')
            ->setAttribute('label', 'Site name');

        $site_is_offline = new Element('site_is_offline');
        $site_is_offline->setAttribute('label', 'Is offline')
            ->setAttribute('checkedValue', '1')
            ->setAttribute('type', 'checkbox');

        $admin_email = new Element('admin_email');
        $admin_email->setAttribute('type', 'text')
            ->setAttribute('label', 'Email');

        $admin_login = new Element('admin_login');
        $admin_login->setAttribute('type', 'text')
            ->setAttribute('label', 'Login');

        $admin_password = new Element('admin_password');
        $admin_password->setAttribute('type', 'password')
            ->setAttribute('label', 'Admin password');

        $admin_password_confirm = new Element('admin_passowrd_confirm');
        $admin_password_confirm->setAttribute('type', 'password')
            ->setAttribute('label', 'Confirm admin password');

        $this->add($site_name);
        $this->add($site_is_offline);
        $this->add($admin_email);
        $this->add($admin_login);
        $this->add($admin_password);
        $this->add($admin_password_confirm);


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
    }
}
