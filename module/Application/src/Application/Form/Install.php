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
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Application\Form;

use Gc\Form\AbstractForm;
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
        $country_available = array(
            'fr_FR' => 'Français',
            'en_GB' => 'English',
        );

        $lang = new Element\Select('lang');
        $lang->setAttribute('size', 10)
            ->setValueOptions($country_available);

        $input_filter = $this->getInputFilter();
        $input_filter->add(
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
        $accept_license = new Element\Checkbox('accept-license');
        $accept_license->setCheckedValue('1')
            ->setUseHiddenElement(false)
            ->setAttribute('id', 'accept-license')
            ->setAttribute('class', 'input-checkbox');

        $input_filter = $this->getInputFilter();
        $input_filter->add(
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

        $this->add($accept_license);
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
            ->setAttribute('label', 'Driver');

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

        $input_filter = $this->getInputFilter();
        $input_filter->add(
            array(
                'name' => 'driver',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'driver'
        );

        $input_filter->add(
            array(
                'name' => 'hostname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'hostname'
        );

        $input_filter->add(
            array(
                'name' => 'username',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'username'
        );

        $input_filter->add(
            array(
                'name' => 'password',
                'required' => false,
            ),
            'password'
        );

        $input_filter->add(
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
        $site_name = new Element\Text('site_name');
        $site_name->setAttribute('label', 'Site name')
            ->setAttribute('class', 'input-text');

        $site_is_offline = new Element\Checkbox('site_is_offline');
        $site_is_offline->setAttribute('label', 'Is offline')
            ->setAttribute('class', 'input-checkbox')
            ->setAttribute('id', 'is-offline')
            ->setCheckedValue('1');

        $admin_email = new Element\Text('admin_email');
        $admin_email->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text')
            ->setAttribute('label', 'Email');

        $admin_firstname = new Element\Text('admin_firstname');
        $admin_firstname->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text')
            ->setAttribute('label', 'Firstname');

        $admin_lastname = new Element\Text('admin_lastname');
        $admin_lastname->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text')
            ->setAttribute('label', 'Lastname');

        $admin_login = new Element\Text('admin_login');
        $admin_login->setAttribute('label', 'Login')
            ->setAttribute('class', 'input-text');

        $admin_password = new Element\Password('admin_password');
        $admin_password->setAttribute('label', 'Admin password')
            ->setAttribute('class', 'input-text');

        $admin_password_confirm = new Element\Password('admin_passowrd_confirm');
        $admin_password_confirm->setAttribute('label', 'Confirm admin password')
            ->setAttribute('class', 'input-text');

        $path     = GC_APPLICATION_PATH . '/data/install/design/';
        $list_dir = glob($path . '*', GLOB_ONLYDIR);
        $options  = array('' => 'Select template');
        foreach ($list_dir as $dir) {
            $dir           = str_replace($path, '', $dir);
            $options[$dir] = $dir;
        }

        $template = new Element\Select('template');
        $template->setAttribute('label', 'Default template');
        $template->setValueOptions($options);


        $this->add($site_name);
        $this->add($site_is_offline);
        $this->add($admin_email);
        $this->add($admin_firstname);
        $this->add($admin_lastname);
        $this->add($admin_login);
        $this->add($admin_password);
        $this->add($admin_password_confirm);
        $this->add($template);


        $input_filter = $this->getInputFilter();
        $input_filter->add(
            array(
                'name' => 'site_name',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'site_name'
        );

        $input_filter->add(
            array(
                'name' => 'admin_firstname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'admin_firstname'
        );

        $input_filter->add(
            array(
                'name' => 'admin_lastname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'admin_lastname'
        );

        $input_filter->add(
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

        $input_filter->add(
            array(
                'name' => 'admin_login',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'admin_login'
        );

        $input_filter->add(
            array(
                'name' => 'site_is_offline',
                'required' => false,
            ),
            'site_is_offline'
        );

        $input_filter->add(
            array(
                'name' => 'admin_password',
                'required' => false,
            ),
            'admin_password'
        );

        $input_filter->add(
            array(
                'name' => 'template',
                'required' => true,
            ),
            'template'
        );
    }
}
