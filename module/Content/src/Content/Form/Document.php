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
                'name' => 'name',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'url_key' => array(
                'name' => 'url_key',
                'required'=> FALSE,
                'allow_empty' => TRUE,
                'validators' => array(
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
        ));

        $this->setInputFilter($inputFilter);

        $name = new Element\Text('name');
        $name->setAttribute('label', 'Name')
            ->setAttribute('id', 'name')
            ->setAttribute('class', 'input-text');

        $url_key = new Element\Text('url_key');
        $url_key->setAttribute('label', 'Url key')
            ->setAttribute('id', 'url_key')
            ->setAttribute('class', 'input-text');

        $document_type_collection = new DocumentType\Collection();
        $document_type = new Element\Select('document_type');
        $document_type->setAttribute('label', 'Document Type')
            ->setAttribute('id', 'document_type')
            ->setAttribute('options', array('' => 'Select document type') + $document_type_collection->getSelect());

        $parent = new Element\Hidden('parent');

        $this->add($name);
        $this->add($url_key);
        $this->add($document_type);
        $this->add($parent);
    }

    public function load(DocumentModel $document)
    {
        $this->get('name')->setValue($document->getName());
        $this->get('url_key')->setValue($document->getUrlKey());

        $status = new Element\Checkbox('status');
        $status->setAttribute('label', 'Publish')
            ->setAttribute('checkedValue', DocumentModel::STATUS_ENABLE)
            ->setAttribute('id', 'status')
            ->setValue($document->getStatus());

        $this->add($status);

        $show_in_nav = new Element\Checkbox('show_in_nav');
        $show_in_nav->setAttribute('label', 'Show in nav')
            ->setValue($document->showInNav())
            ->setAttribute('id', 'show_in_nav')
            ->setAttribute('checkedValue', 1);

        $this->add($show_in_nav);

        $views_collection = new View\Collection();
        $view = new Element\Select('view');
        $view->setAttribute('options', $views_collection->getSelect())
            ->setValue((string)$document->getViewId())
            ->setAttribute('id', 'view')
            ->setAttribute('label', 'View');

        $this->add($view);

        $layouts_collection = new Layout\Collection();
        $layout = new Element\Select('layout');
        $layout->setAttribute('options', $layouts_collection->getSelect())
            ->setValue((string)$document->getLayoutId())
            ->setAttribute('id', 'layout')
            ->setAttribute('label', 'Layout');

        $this->add($layout);
        $this->remove('document_type');
        $this->remove('parent');

        $this->loadValues($document);
    }
}
