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
 * @category   Gc_Application
 * @package    Content
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Content\Form;

use Gc\Datatype;
use Gc\Document\Model as DocumentModel;
use Gc\DocumentType;
use Gc\Form\AbstractForm;
use Gc\Property;
use Zend\Form as ZendForm;
use Zend\ServiceManager\ServiceManager;

/**
 * Document form
 *
 * @category   Gc_Application
 * @package    Content
 * @subpackage Form
 */
class Document extends AbstractForm
{
    /**
     * Properties cache
     *
     * @var array
     */
    protected $properties = array();

    /**
     * Tabs cache
     *
     * @var array
     */
    protected $tabs = array();

    /**
     * Initialize document form
     *
     * @param string $url Url for the action
     *
     * @return void
     */

    public function init($url = null)
    {
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('class', 'relative');
        $this->setAttribute(
            'action',
            $url
        );
    }


    /**
     * Load tabs from document type
     *
     * @param integer $documentTypeId Document type id
     *
     * @return \Gc\Tab\Collection
     */
    public function loadTabs($documentTypeId)
    {
        if (empty($this->tabs[$documentTypeId])) {
            $documentType = DocumentType\Model::fromId($documentTypeId);
            $tabs         = $documentType->getTabs();

            $this->tabs[$documentTypeId] = $tabs;
        }

        return $this->tabs[$documentTypeId];
    }

    /**
     * Load properties from document type, tab and document
     *
     * @param integer $documentTypeId Document type id
     * @param integer $tabId          Tab id
     * @param integer $documentId     Document id
     *
     * @return \Gc\Property\Collection
     */
    public function loadProperties($documentTypeId, $tabId, $documentId)
    {
        $propertyName = sprintf('%d-%d-%d', $documentTypeId, $tabId, $documentId);
        if (empty($this->properties[$propertyName])) {
            $properties = new Property\Collection();
            $properties->load($documentTypeId, $tabId, $documentId);

            $this->properties[$propertyName] =  $properties->getProperties();
        }

        return $this->properties[$propertyName];
    }

    /**
     * Load properties from document type, tab and document
     *
     * @param integer        $documentTypeId Document type id
     * @param DocumentModel  $document       Document model
     * @param ServiceManager $serviceLocator Service manager
     *
     * @return array
     */
    public function load($documentTypeId, DocumentModel $document, ServiceManager $serviceLocator)
    {
        $tabs      = $this->loadTabs($documentTypeId);
        $tabsArray = array();

        $idx = 1;
        foreach ($tabs as $tab) {
            $tabsArray[] = $tab->getName();
            $properties  = $this->loadProperties($documentTypeId, $tab->getId(), $document->getId());

            $fieldset = new ZendForm\Fieldset('tabs-' . $idx);
            foreach ($properties as $property) {
                AbstractForm::addContent(
                    $fieldset,
                    Datatype\Model::loadEditor($serviceLocator, $property)
                );
            }

            $this->add($fieldset);
            $idx++;
        }

        $formDocumentAdd = new DocumentInformation();
        $formDocumentAdd->load($document);
        $formDocumentAdd->setAttribute('name', 'tabs-' . $idx);
        $this->add($formDocumentAdd);

        return $tabsArray;
    }
}
