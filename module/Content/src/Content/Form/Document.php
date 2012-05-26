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
    Gc\Form\AbstractForm,
    Gc\Layout,
    Gc\View,
    Zend\Validator,
    Zend\Form\Element,
    Zend\InputFilter\Factory as InputFilterFactory;

class Document extends AbstractForm
{
    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter = $inputFilterFactory->createInputFilter(array(
            'name' => array(
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'url_key' => array(
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                    //, array('name' => 'identifier') @TODO test it
                    array(
                        'name' => 'db\\no_record_exists',
                        'options' => array(
                            'table' => 'document',
                            'field' => 'url_key',
                            'adapter' => $this->getAdapter(),
                        ),
                    ),
                ),
            ),
            'document_type' => array(
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
        ));

        $this->setInputFilter($inputFilter);

        $name = new Element('name');
        $name->setAttribute('label', 'Name')
            ->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text');

        $url_key = new Element('url_key');
        $url_key->setAttribute('label', 'Url key')
            ->setAttribute('type', 'text')
            ->setAttribute('class', 'input-text');

        $document_type_collection = new DocumentType\Collection();
        $document_type = new Element('document_type');
        $document_type->setAttribute('label', 'Document Type')
            ->setAttribute('type', 'select')
            ->setAttribute('options', array('' => 'Select document type') + $document_type_collection->getSelect());

        $parent = new Element('parent');
        $parent->setAttribute('type', 'hidden');

        $submit = new Element('submit');
        $submit->setAttribute('class', 'input-submit')
            ->setAttribute('type', 'submit')
            ->setAttribute('label', 'Save');

        $this->add($name);
        $this->add($url_key);
        $this->add($document_type);
        $this->add($parent);
        $this->add($submit);
    }

    public function load(DocumentModel $document)
    {
        $this->get('name')->setAttribute('value', $document->getName());
        $this->get('url_key')->setAttribute('value', $document->getUrlKey());

        $status = new Element('status');
        $status->setAttribute('type', 'checkbox')
            ->setAttribute('label', 'Publish')
            ->setAttribute('value', $document->getStatus());

        $this->add($status);

        $show_in_nav = new Element('show_in_nav');
        $show_in_nav->setAttribute('type', 'checkbox')
            ->setAttribute('label', 'Show in nav')
            ->setAttribute('value', $document->showInNav());

        $this->add($show_in_nav);

        $views_collection = new View\Collection();
        $view = new Element('view');
        $view->setAttribute('type', 'select')
            ->setAttribute('options', $views_collection->getSelect())
            ->setAttribute('value', $document->getViewId())
            ->setAttribute('label', 'View');

        $this->add($view);

        $layouts_collection = new Layout\Collection();
        $layout = new Element('layout');
        $layout->setAttribute('type', 'select')
            ->setAttribute('options', $layouts_collection->getSelect())
            ->setAttribute('value', $document->getViewId())
            ->setAttribute('label', 'Layout');

        $this->add($layout);
        $this->remove('document_type');
        $this->remove('parent');
        $this->remove('submit');
    }
}
