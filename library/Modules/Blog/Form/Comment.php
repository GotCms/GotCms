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

use Gc\Form\AbstractForm;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\Captcha\Image as CaptchaImage;

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
     *
     * @return void
     */
    public function init()
    {
        $showEmail = new Element\Checkbox('show_email');
        $showEmail->setAttribute('label', 'Show email');
        $showEmail->setAttribute('required', 'required');
        $username = new Element\Text('username');
        $username->setAttribute('label', 'Username');
        $username->setAttribute('required', 'required');
        $email = new Element\Text('email');
        $email->setAttribute('label', 'Email');
        $email->setAttribute('required', 'required');
        $message = new Element\Textarea('message');
        $message->setAttribute('label', 'Message');
        $message->setAttribute('required', 'required');

        $captchaImage = new CaptchaImage(
            array(
                'font' => GC_APPLICATION_PATH . '/data/fonts/arial.ttf',
                'width' => 250,
                'height' => 50,
                'dotNoiseLevel' => 40,
                'lineNoiseLevel' => 3
            )
        );

        $captchaImage->setImgDir(GC_PUBLIC_PATH . '/frontend/tmp');
        $captchaImage->setImgUrl('/frontend/tmp');

        $captcha = new Element\Captcha('captcha');
        $captcha->setAttribute('label', 'Please verify you are human');
        $captcha->setCaptcha($captchaImage);
        $captcha->setAttribute('required', 'required');
        $captcha->setAttribute('id', 'captcha');

        $this->add($showEmail);
        $this->add($username);
        $this->add($email);
        $this->add($message);
        $this->add($captcha);

        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'show_email' => array(
                    'name' => 'show_email',
                    'required' => false,
                ),
                'username' => array(
                    'name' => 'username',
                    'required' => true,
                ),
                'email' => array(
                    'name' => 'email',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'email_address'),
                    ),
                ),
                'message' => array(
                    'name' => 'message',
                    'required' => true,
                ),
                'captcha' => $captcha->getInputSpecification()
            )
        );

        $this->setInputFilter($inputFilter);
    }
}
