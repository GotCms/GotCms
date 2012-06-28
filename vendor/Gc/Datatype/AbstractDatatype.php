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
 * @category    Gc
 * @package     Library
 * @subpackage  Datatype
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Datatype;

use Gc\Db\AbstractTable,
    Gc\Property,
    Zend\View\Model\ViewModel;

abstract class AbstractDatatype extends AbstractTable
{
    /**
     * @var \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    protected     $_editor;

    /**
     * @var \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    protected     $_prevalueEditor;

    /**
     * @var \Gc\Property\Model
     */
    protected     $_property;

    /**
     * @var integer
     */
    protected     $_document_id;

    /**
     * @var string
     */
    protected     $_name = 'datatype';

    /**
     * Get Datatype Editor
     * @param \Gc\Property\Model $property
     * @return \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    abstract public function getEditor(Property\Model $property);

    /**
     * Get Datatype Prevalue editor
     * @return Gc\Model\DbTable\Datatype\Abstract\PrevalueEditor
     */
    abstract public function getPrevalueEditor();

    public function load($datatype = NULL, $document_id = NULL)
    {
        if(empty($datatype))
        {
            return FALSE;
        }

        $this->setData('datatype_model', $datatype);
        $this->setData('document_id', $document_id);
    }

    /**
     * Return configuration
     * @return mixed
     */
    public function getConfig()
    {
        return $this->getDatatypeModel()->getData('prevalue_value');
    }

    /**
     * Set configuration
     * @param mixed $value
     * @return \Gc\Datatype\AbstractDatatype
     */
    public function setConfig($value)
    {
        $this->getDatatypeModel()->setData('prevalue_value', $value);
        return $this;
    }

    /**
     * @TODO Finish uploadURL
     * Get upload url path
     * @return string
     */
    public function getUploadUrl()
    {
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        return $baseUrl.'/media/upload/document/'.$this->_document_id;
    }

    /**
     * Get a helper by name
     *
     * @param  string $name
     * @return object
     */
    public function getHelper($name)
    {
        $helper_broker = $GLOBALS['application']->getServiceManager()->get('Zend\View\HelperBroker');

        return $helper_broker->load($name);
    }

    /**
     * Get Property Model
     *
     * @return \Gc\Property\Model
     */
    public function getProperty()
    {
        return $this->_property;
    }

    /**
     * get datatype name, construct with datatype name and property_id
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Render template
     *
     * @param string $name
     * @return string
     */
    public function render($name, Array $data = array())
    {
        $renderer = $GLOBALS['application']->getServiceManager()->get('Zend\View\Renderer\PhpRenderer');

        $view_model = new ViewModel();
        $view_model->setTemplate($name);
        $view_model->setVariables($data);
        return $renderer->render($view_model);
    }

    /**
     * Add path in Zend\View\Resolver\TemplatePathStack
     *
     * @param string $name
     * @return \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    public function addPath($dir)
    {
        $renderer = $GLOBALS['application']->getServiceManager()->get('Zend\View\Renderer\PhpRenderer');
        $iterators = $renderer->resolver()->getIterator()->toArray();
        foreach($iterators as $iterator)
        {
            if($iterator instanceof \Zend\View\Resolver\TemplatePathStack)
            {
                $iterator->addPath($dir);
            }
        }

        return $this;
    }
}
