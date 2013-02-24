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
 * @category   Gc
 * @package    Library
 * @subpackage Datatype
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Datatype;

use Gc\Db\AbstractTable;
use Gc\Document\Model as DocumentModel;
use Gc\Media\Info;
use Gc\Property;
use Gc\Registry;
use ReflectionObject;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;

/**
 * Abstract Datatype is used to call
 * the prevalue editor and editor.
 *
 * @category   Gc
 * @package    Library
 * @subpackage Datatype
 */
abstract class AbstractDatatype extends AbstractTable
{
    /**
     * Editor
     *
     * @var \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    protected $editor;

    /**
     * Prevalue editor
     *
     * @var \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    protected $prevalueEditor;

    /**
     * Property
     *
     * @var \Gc\Property\Model
     */
    protected $property;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'datatype';

    /**
     * Renderer
     *
     * @var \Zend\View\Renderer\PhpRenderer
     */
    protected $renderer;

    /**
     * Configuration
     *
     * @var mixed
     */
    protected $config;

    /**
     * Check if config changed
     *
     * @var mixed
     */
    protected $configHasChanged = false;

    /**
     * Get Datatype Editor
     *
     * @param Property\Model $property
     * @return \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    abstract public function getEditor(Property\Model $property);

    /**
     * Get Datatype Prevalue editor
     *
     * @return \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    abstract public function getPrevalueEditor();

    /**
     * Return datatype informations
     *
     * @return array
     */
    public function getInfos()
    {
        $object = new ReflectionObject($this);
        $directory = dirname($object->getFileName());
        $filename = $directory . '/datatype.info';
        $info = new Info();

        if ($info->fromFile($filename) !== true) {
            return false;
        }

        return $info->render();
    }

    /**
     * Load Datatype
     *
     * @param Model $datatype
     * @param integer $document_id
     * @return mixed
     */
    public function load($datatype = null, $document_id = null)
    {
        if (empty($datatype)) {
            return false;
        }

        $this->setData('datatype_model', $datatype);
        $this->setData('document_id', $document_id);
    }

    /**
     * Return configuration
     *
     * @return void
     */
    public function getConfig()
    {
        if (empty($this->config) or $this->configHasChanged) {
            $config = $this->getDatatypeModel()->getData('prevalue_value');
            if (!is_string($config) or !preg_match('/^(i|s|a|o|d)(.*);/si', $config)) {
                $this->config = $config;
            } else {
                $this->config = unserialize($config);
            }

            $this->configHasChanged = false;
        }

        return $this->config;
    }

    /**
     * Set configuration
     *
     * @param mixed $value
     * @return \Gc\Datatype\AbstractDatatype
     */
    public function setConfig($value)
    {
        $this->getDatatypeModel()->setPrevalueValue($value);
        $this->configHasChanged = true;
        return $this;
    }

    /**
     * Get upload url path
     *
     * @param integer $property_id
     * @return string
     */
    public function getUploadUrl($property_id)
    {
        $router = Registry::get('Application')->getMvcEvent()->getRouter();

        return $router->assemble(
            array(
                'document_id' => $this->getDocumentId(),
                'property_id' => $property_id
            ),
            array('name' => 'mediaUpload')
        );
    }

    /**
     * Get a helper by name
     *
     * @param  string $name
     * @return object
     */
    public function getHelper($name)
    {
        if ($this->getHelperBroker() === null) {
            $this->setHelperBroker(Registry::get('Application')->getServiceManager()->get('viewhelpermanager'));
        }

        return $this->getHelperBroker()->get($name);
    }

    /**
     * Get Property Model
     *
     * @return \Gc\Property\Model
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set Property Model
     *
     * @param \Gc\Property\Model $property
     * @return \Gc\Datatype\AbstractDatatype
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Get datatype name, construct with datatype name and property_id
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Render template
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    public function render($name, array $data = array())
    {
        $this->checkRenderer();
        $view_model = new ViewModel();
        $view_model->setTemplate($name);
        $view_model->setVariables($data);

        return $this->renderer->render($view_model);
    }

    /**
     * Add path in Zend\View\Resolver\TemplatePathStack
     *
     * @param string $dir
     * @return \Gc\Datatype\AbstractDatatype
     */
    public function addPath($dir)
    {
        $this->checkRenderer();
        $this->renderer->resolver()->addPath($dir);

        return $this;
    }

    /**
     * Check renderer, create if not exists
     * Copy helper plugin manager from application service manager
     *
     * @return \Gc\Datatype\AbstractDatatype
     */
    protected function checkRenderer()
    {
        if (is_null($this->renderer)) {
            $this->renderer = new PhpRenderer();
            $renderer = Registry::get('Application')->getServiceManager()->get('Zend\View\Renderer\PhpRenderer');
            $this->renderer->setHelperPluginManager(clone $renderer->getHelperPluginManager());
        }

        return $this;
    }

    /**
     * Retrieve document
     *
     * @return \Gc\Document\Model
     */
    public function getDocument()
    {
        if ($this->getData('document') === null) {
            $this->setData('document', DocumentModel::fromId($this->getDocumentId()));
        }

        return $this->getData('document');
    }
}
