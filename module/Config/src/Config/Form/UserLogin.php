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
    Zend\Validator\Db,
    Zend\Form\Element;

class UserLogin extends AbstractForm
{
    /**
     * Initialize UserLogin form
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->addDecorator('ViewScript', array('viewScript' => 'config-forms/login.phtml'));

        $email = new Element\Text('login');
        $email->setRequired(TRUE)
            ->addValidator('NotEmpty');

        $password  = new Element\Password('password');
        $password->setRequired(TRUE)
            ->addValidator('NotEmpty');

        $redirect  = new Element\Hidden('redirect');

        $this->addElements(array($email, $password, $redirect));
    }
}
