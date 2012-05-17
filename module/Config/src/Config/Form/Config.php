<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/gpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Form
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.got-cms.com
 * @license  http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Config\Form;

use Gc\Form\AbstractForm,
    Zend\Validator\Db,
    Zend\Validator\Identical,
    Zend\Form\Element,
    Zend\Form\SubForm,
    Zend\Locale\Locale,
    Gc\User\Permission;

class Config extends AbstractForm
{
    /**
     * Initialize Config form
     * @return void
     */
    public function init()
    {
        $this->setMethod(self::METHOD_POST);
    }

    /**
     * Initialize General sub form
     *
     * @return \Config\Form\Config
     */
    protected function initGeneral()
    {
        //General settings
        $general_settings = new SubForm();
        $name = new Element\Text('site_name');
        $name->setRequired(TRUE)
            ->setLabel('Site name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $is_offline = new Element\Radio('is_offline');
        $is_offline->setLabel('Is offline')
            ->addMultiOptions(array('Yes', 'No'))
            ->setAttrib('class', 'input-text');

        $offline_document = new Element\Select('offline_document');
        $offline_document->setLabel('Offline document')
            ->addMultiOptions(array())
            ->setAttrib('class', 'input-text');

        $general_settings->addElements(array($name, $is_offline));

        $this->addSubForm($general);

        return $this;
    }

    /**
     * Initialize System sub form
     *
     * @return \Config\Form\Config
     */
    public function initSystem()
    {
        //Session settings
        $session_settings = new SubForm();

        $cookie_domain = new Element\Text('cookie_domain');
        $cookie_domain->setRequired(TRUE)
            ->setLabel('Cookie domain')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $cookie_path = new Element\Text('cookie_path');
        $cookie_path->setRequired(TRUE)
            ->setLabel('Cookie path')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $session_lifetime = new Element\Text('session_lifetime');
        $session_lifetime->setRequired(TRUE)
            ->setLabel('Session lifetime')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $session_handler = new Element\Select('session_handler');
        $session_handler->setRequired(TRUE)
            ->setLabel('Session handler')
            ->addMultiOptions(array('Database', 'Files'));

        $session_settings->addElements(array($cookie_domain, $cookie_path, $session_handler, $session_lifetime));
        $this->addSubForm($session_settings, 'session');

        //Debug settings
        $debug_settings = new SubForm();

        $debug_is_active = new Element\Text('is_active');
        $debug_is_active->setRequired(TRUE)
            ->setLabel('Is active')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $debug_settings->addElements(array($debug_is_active));
        $this->addSubForm($debug_settings, 'debug');

        return $this;
    }

    /**
     * Initialize Server sub form
     *
     * @return \Config\Form\Config
     */
    public function initServer()
    {
        //Local settings
        $locale_settings = new SubForm();

        $locale = new Element\Select();
        $locale->setRequired(TRUE)
            ->setLabel('Server locale')
            ->addMultiOptions(Locale::getLocaleList());

        $locale_settings->addElements(array($locale));
        $this->addSubForm($locale_settings, 'locale');

        //Mail settings
        $mail_settings = new SubForm();

        $mail_from = new Element\Text('mail_from');
        $mail_from->setRequired(TRUE)
            ->setLabel('From E-mail')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $mail_from_name = new Element\Text('mail_from_name');
        $mail_from_name->setRequired(TRUE)
            ->setLabel('From name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $mail_settings->addElements(array($mail_from, $mail_from_name));
        $this->addSubForm($mail_settings, 'mail');

        return $this;
    }
}
