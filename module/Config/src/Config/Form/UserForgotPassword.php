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
use Zend\Validator\Db;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;

/**
 * User forgot password form
 *
 * @category   Gc_Application
 * @package    Config
 * @subpackage Form
 */
class UserForgotPassword extends AbstractForm
{
    /**
     * Initialize UserForgotPassword form
     *
     * @return void
     */
    public function init()
    {
        $this->setInputFilter(new InputFilter());
    }

    /**
     * Initialize UserForgotPassword email form
     *
     * @return void
     */
    public function initEmail()
    {
        $filter = $this->getInputFilter();
        $filter->add(
            array(
                'email' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
            ),
            'email'
        );

        $this->add(new Element\Text('email'));
    }
    /**
     * Initialize UserForgotPassword reset form
     *
     * @return void
     */
    public function initResetForm()
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

        $this->add(new Element\Text('password'));
        $this->add(new Element\Text('password_confirm'));
    }
}
