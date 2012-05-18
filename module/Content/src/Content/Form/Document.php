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
 * @package  Content
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Content\Form;

use Gc\Document\Model as DocumentModel,
    Gc\DocumentType,
    Gc\View,
    Gc\Form\AbstractForm,
    Zend\Validator,
    Zend\Form\Element;

class Document extends AbstractForm
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);
        $this->setElementsBelongTo('document');

        $name = new Element\Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator(new Validator\NotEmpty());

        $url_key  = new Element\Text('url_key');
        $url_key->setRequired(FALSE)
            ->setLabel('Url key')
            ->setAttrib('class', 'input-text')
            ->addValidator(new Validator\NotEmpty())
            ->addValidator(new Validator\Db\NoRecordExists(array('table' => 'document', 'field' => 'url_key')));

        $document_type_collection = new DocumentType\Collection();
        $document_type = new Element\Select('document_type');
        $document_type->addMultiOption('', 'Select document type');
        $document_type->addMultiOptions($document_type_collection->getSelect());

        $parent = new Element\Hidden('parent');

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Create');

        $this->addElements(array($name, $url_key, $document_type, $parent, $submit));
    }

    public function load(DocumentModel $document, $index)
    {
        $this->addDecorators(array('FormElements',array('HtmlTag', array('tag' => 'dl','id' => 'tabs-'.$index))));
        $this->removeDecorator('Fieldset');
        $this->removeDecorator('DtDdWrapper');

        $this->getElement('name')->setValue($document->getName());
        $this->getElement('url_key')->setValue($document->getUrlKey());

        $status = new Element\Checkbox('status');
        $status->setLabel('Publish');
        $status->setValue($document->getStatus());

        $this->addElement($status);

        $show_in_nav = new Element\Checkbox('show_in_nav');
        $show_in_nav->setLabel('Show in nav');
        $show_in_nav->setValue($document->showInNav());

        $this->addElement($show_in_nav);

        $views_collection = new View\Collection();
        $view = new Element\Select('view');
        $view->addMultiOptions($views_collection->getSelect());
        $view->setValue($document->getViewId());
        $view->setLabel('View');

        $this->addElement($view);

        $layouts_collection = new View\Collection();
        $layout = new Element\Select('layout');
        $layout->addMultiOptions($layouts_collection->getSelect());
        $layout->setValue($document->getViewId());
        $layout->setLabel('Layout');

        $this->addElement($layout);
        $this->removeElement('document_type');
        $this->removeElement('submit');
    }
}
