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
    protected $parentId = NULL;
    protected $documentId = NULL;

    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter = $inputFilterFactory->createInputFilter(array(
            'document-name' => array(
                'name' => 'document-name',
                'required'=> TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'document-url_key' => array(
                'name' => 'document-url_key',
                'required'=> FALSE,
                'allow_empty' => TRUE,
                'validators' => array(
                    array('name' => 'regex', 'options' => array(
                        'pattern' => parent::IDENTIFIER_PATTERN
                    )),
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

        $name = new Element\Text('document-name');
        $name->setAttribute('label', 'Name')
            ->setAttribute('id', 'name')
            ->setAttribute('class', 'input-text');

        $url_key = new Element\Text('document-url_key');
        $url_key->setAttribute('label', 'Url key')
            ->setAttribute('id', 'url_key')
            ->setAttribute('class', 'input-text');

        $document_type = new Element\Select('document_type');
        $document_type->setAttribute('label', 'Document Type')
            ->setAttribute('id', 'document_type')
            ->setValueOptions(array('' => 'Select document type'));

        $parent = new Element\Hidden('parent');

        $this->add($name);
        $this->add($url_key);
        $this->add($document_type);
        $this->add($parent);
    }

    /**
     * Check parent validation
     * @return boolean
     */
    public function isValid()
    {
        $parent = $this->get('parent');
        if(!empty($parent))
        {
            $this->parentId = $parent->getValue();
        }

        $condition = sprintf('parent_id = %d', $this->parentId);
        if(!empty($this->documentId))
        {
            $condition .= sprintf(' AND id != %d', $this->documentId);
        }

        $input_filter = $this->getInputFilter();
        $validators = $input_filter->get('document-url_key')->getValidatorChain()->getValidators();

        foreach($validators as $validator)
        {
            if($validator['instance'] instanceof \Zend\Validator\Db\NoRecordExists)
            {
                $validator['instance']->setExclude($condition);
            }
        }

        return parent::isValid();
    }

    /**
     * Load document form from DocumentModel
     * @param DocumentModel $document
     * @return void
     */
    public function load(DocumentModel $document)
    {
        $this->get('document-name')->setValue($document->getName());
        $this->get('document-url_key')->setValue($document->getUrlKey());

        $status = new Element\Checkbox('document-status');
        $status->setAttribute('label', 'Publish')
            ->setCheckedValue(DocumentModel::STATUS_ENABLE)
            ->setAttribute('id', 'status')
            ->setValue($document->getStatus());

        $this->add($status);

        $show_in_nav = new Element\Checkbox('document-show_in_nav');
        $show_in_nav->setAttribute('label', 'Show in nav')
            ->setValue($document->showInNav())
            ->setAttribute('id', 'show_in_nav')
            ->setCheckedValue(1);

        $this->add($show_in_nav);

        $document_type = $document->getDocumentType();
        $views_collection = $document_type->getAvailableViews();
        $select = $views_collection->getSelect();
        if(empty($select))
        {
            $view_model = \Gc\View\Model::fromId($document->getDocumentType()->getDefaultViewId());
            if(!empty($view_model))
            {
                $select = array($view_model->getId() => $view_model->getName());
            }
            else
            {
                $select = array();
            }
        }

        $view = new Element\Select('document-view');
        $view->setValueOptions($select)
            ->setValue((string)$document->getViewId())
            ->setAttribute('id', 'view')
            ->setAttribute('label', 'View');

        $this->add($view);

        $layouts_collection = new Layout\Collection();
        $layout = new Element\Select('document-layout');
        $layout->setValueOptions($layouts_collection->getSelect())
            ->setValue((string)$document->getLayoutId())
            ->setAttribute('id', 'layout')
            ->setAttribute('label', 'Layout');

        $this->add($layout);
        $this->remove('document_type');
        $this->remove('parent');


        $more_information = new Element\Hidden('more_information');
        $more_information->setAttribute('content', '');
        $this->add($more_information);


        $this->parentId = $document->getParentId();
        $this->documentId = $document->getId();

        $this->loadValues($document);
    }
}
