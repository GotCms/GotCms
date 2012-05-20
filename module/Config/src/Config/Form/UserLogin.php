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
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Config\Form;

use Gc\Form\AbstractForm,
    Zend\Form\Element;

class UserLogin extends AbstractForm
{
    /**
     * Initialize UserLogin form
     * @return void
     */
    public function init()
    {
        $email = new Element('login');
        $email->setAttributes(array(
            'required'=> TRUE
            , 'type'=> 'text'
            , 'validators' => array(
                array('name' => 'not_empty')
            )
        ));

        $password  = new Element('password');
        $password->setAttributes(array(
            'required'=> TRUE
            , 'type'=> 'password'
            , 'validators' => array(
                array('name' => 'not_empty')
            )
        ));

        $redirect  = new Element('redirect');
        $email->setAttribute('type', 'hidden');

        $this->add($email);
        $this->add($password);
        $this->add($redirect);
    }
}
