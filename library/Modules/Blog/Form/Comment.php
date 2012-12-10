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
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Blog\Form;

use Gc\Form\AbstractForm,
    Zend\Form\Element,
    Zend\InputFilter\Factory as InputFilterFactory;

/**
 * Comment form
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Form
 */
class Comment extends AbstractForm
{
    /**
     * Init Module form
     * @return void
     */
    public function init()
    {
        $show_email  = new Element\Checkbox('show_email');
        $show_email->setAttribute('label', 'Show email');
        $username    = new Element\Text('username');
        $show_email->setAttribute('label', 'Username');
        $email       = new Element\Text('email');
        $show_email->setAttribute('label', 'Email');
        $message     = new Element\Textarea('message');
        $show_email->setAttribute('label', 'Message');

        $this->add($show_email);
        $this->add($username);
        $this->add($email);
        $this->add($message);

        $input_filter_factory = new InputFilterFactory();
        $input_filter = $input_filter_factory->createInputFilter(array(
            'show_email' => array(
                'name' => 'show_email',
                'required'=> FALSE,
            ),
            'username' => array(
                'name' => 'username',
                'required'=> TRUE,
            ),
            'email' => array(
                'name' => 'email',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'email_address'),
                ),
            ),
            'message' => array(
                'name' => 'message',
                'required'=> TRUE,
            ),
        ));

        $this->setInputFilter($input_filter);
    }
}
