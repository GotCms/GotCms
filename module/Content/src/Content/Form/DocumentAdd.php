<?php
namespace Development\Form;

use Application\Model\Document,
    Application\Model\View,
    Es\Form\AbstractForm,
    Es\Validator,
    Zend\Validator,
    Zend\Form\Element;

class DocumentAdd extends AbstractForm
{
    protected $_document;

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
            ->addValidator(new Validator\Db\NoRecordExists(array('table' => 'documents', 'field' => 'url_key')));

        $document_type_collection = new Es_Model_DbTable_DocumentType_Collection();
        $document_type = new Element\Select('document_type');
        $document_type->addMultiOption('', 'Select document type');
        $document_type->addMultiOptions($document_type_collection->getSelect());

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Create');

        $this->addElements(array($name, $url_key, $document_type, $submit));
    }

    public function load(Document\Model $document, $index)
    {
        $this->_document = $document;
        $this->addDecorators(array('FormElements',array('HtmlTag', array('tag' => 'dl','id' => 'tabs-'.$index))));
        $this->removeDecorator('Fieldset');
        $this->removeDecorator('DtDdWrapper');

        $this->getElement('name')->setValue($document->getName());
        $this->getElement('url_key')->setValue($document->getUrlKey());

        $show_in_nav = new Element\Checkbox('show_in_nav');
        $show_in_nav->setLabel('Show in nav');
        $show_in_nav->setValue($document->showInNav());

        $this->addElement($show_in_nav);

        $views_collection = new Es_Model_DbTable_View_Collection();
        $view = new Element\Select('view');
        $view->addMultiOptions($views_collection->getSelect());
        $view->setValue($document->getViewId());
        $view->setLabel('View');

        $this->addElement($view);

        $layouts_collection = new Es_Model_DbTable_View_Collection();
        $layout = new Element\Select('layout');
        $layout->addMultiOptions($layouts_collection->getSelect());
        $layout->setValue($document->getViewId());
        $layout->setLabel('Layout');

        $this->addElement($layout);

        $this->removeElement('document_type');
        $this->removeElement('submit');
    }
}
