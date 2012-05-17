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
 * @package  Development
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.got-cms.com
 * @license  http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Development\Form;

use Gc\Form\AbstractForm,
    Gc\Validator,
    Zend\Validator\Db,
    Zend\Form\Element;

class Layout extends AbstractForm
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);
        $this->setElementsBelongTo('layout');

        $name = new Element\Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator(new Db\NoRecordExists(array(
                'table' => 'layout'
                , 'field' => 'name')
                )
            );

        $identifier  = new Element\Text('identifier');
        $identifier->setRequired(TRUE)
            ->setLabel('Identifier')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator(new Validator\Identifier())
            ->addValidator(new Db\NoRecordExists(array(
                'table' => 'layout'
                , 'field' => 'identifier')
                )
            );

        $description  = new Element\Text('description');
        $description->setLabel('Description')
            ->setAttrib('class', 'input-text');

        $content  = new Element\Textarea('content');
        $content->setLabel('Content');

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Add');


        $this->addElements(array($name, $identifier, $description, $content, $submit));
    }
}
