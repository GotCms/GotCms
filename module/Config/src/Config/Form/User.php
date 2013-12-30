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
use Gc\User\Role\Collection as RoleCollection;
use Zend\Validator\Db;
use Zend\Validator;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * User form
 *
 * @category   Gc_Application
 * @package    Config
 * @subpackage Form
 */
class User extends AbstractForm
{
    /**
     * Initialize User form
     *
     * @return void
     */
    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'email' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array('name' => 'email_address'),
                    ),
                ),
                'login' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array(
                            'name' => 'db\\no_record_exists',
                            'options' => array(
                                'table' => 'user',
                                'field' => 'login',
                                'adapter' => $this->getAdapter(),
                            ),
                        ),
                    ),
                ),
                'lastname' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'firstname' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'user_acl_role_id' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
            )
        );

        $this->setInputFilter($inputFilter);

        $email = new Element\Text('email');
        $email->setLabel('Email')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'email');
        $this->add($email);

        $login = new Element\Text('login');
        $login->setLabel('Login')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'login');
        $this->add($login);

        $password = new Element\Password('password');
        $password->setLabel('Password')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('autocomplete', 'off')
            ->setAttribute('id', 'password');
        $this->add($password);

        $passwordConfirm = new Element\Password('password_confirm');
        $passwordConfirm->setLabel('Password Confirm')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('autocomplete', 'off')
            ->setAttribute('id', 'password_confirm');
        $this->add($passwordConfirm);

        $lastname = new Element\Text('lastname');
        $lastname->setLabel('Lastname')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'lastname');
        $this->add($lastname);

        $firstname = new Element\Text('firstname');
        $firstname->setLabel('Firstname')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'firstname');
        $this->add($firstname);

        $role           = new Element\Select('user_acl_role_id');
        $role->setLabel('Role')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            );
        $roleCollection = new RoleCollection();
        $rolesList      = $roleCollection->getRoles();
        $selectOptions  = array();
        foreach ($rolesList as $roleModel) {
            $selectOptions[$roleModel->getId()] = $roleModel->getName();
        }

        $role->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control');


        $this->add($role);
    }

    /**
     * Set if yes or no password is required when user click on Save
     *
     * @return \Config\Form\User
     */
    public function passwordRequired()
    {
        $filter = $this->getInputFilter();
        $filter->add(
            array(
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'password'
        );

        $filter->add(
            array(
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'password_confirm'
        );

        return $this;
    }
}
