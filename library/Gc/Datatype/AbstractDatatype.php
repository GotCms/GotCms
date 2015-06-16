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
use Gc\View\Renderer;
use Gc\Property;
use ReflectionObject;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\View\HelperPluginManager;

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
     * @var \Gc\View\Renderer
     */
    protected $renderer;

    /**
     * Request
     *
     * @var \Zend\Http\PhpEnvironment\Request
     */
    protected $request;

    /**
     * Request
     *
     * @var TreeRouteStack
     */
    protected $router;

    /**
     * Datatypes list
     *
     * @var array
     */
    protected $datatypesList;

    /**
     * Request
     *
     * @var \Zend\View\HelperPluginManager
     */
    protected $helperManager;

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
     * @param Property\Model $property Property
     *
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
     * @return false|string
     */
    public function getInfos()
    {
        $object    = new ReflectionObject($this);
        $directory = dirname($object->getFileName());
        $filename  = $directory . '/datatype.info';
        $info      = new Info();

        if ($info->fromFile($filename) !== true) {
            return false;
        }

        return $info->render();
    }

    /**
     * Load Datatype
     *
     * @param Model   $datatype   Datatype
     * @param integer $documentId Document id
     *
     * @return false|null
     */
    public function load($datatype = null, $documentId = null)
    {
        if (empty($datatype)) {
            return false;
        }

        $this->setData('datatype_model', $datatype);
        $this->setData('document_id', $documentId);
    }

    /**
     * Return configuration
     *
     * @return array
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
     * @param mixed $value Value
     *
     * @return AbstractDatatype
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
     * @param integer $propertyId Property id
     *
     * @return string
     */
    public function getUploadUrl($propertyId)
    {
        return $this->router->assemble(
            array(
                'document_id' => $this->getDocumentId(),
                'property_id' => $propertyId
            ),
            array('name' => 'content/media/upload')
        );
    }

    /**
     * Get a helper by name
     *
     * @param string $name Name
     *
     * @return object
     */
    public function getHelper($name)
    {
        return $this->helperManager->get($name);
    }

    /**
     * Set helper manager
     *
     * @param HelperPluginManager $helperManager Helper manager
     *
     * @return AbstractDatatype
     */
    public function setHelperManager(HelperPluginManager $helperManager)
    {
        $this->helperManager = $helperManager;
        return $this;
    }

    /**
     * Get helper manager
     *
     * @return HelperPluginManager
     */
    public function getHelperManager()
    {
        return $this->helperManager;
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
     * @param \Gc\Property\Model $property Property
     *
     * @return AbstractDatatype
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
     * @param string $name Name
     * @param array  $data Data
     *
     * @return string
     */
    public function render($name, array $data = array())
    {
        if (empty($this->renderer)) {
            $this->renderer = new Renderer();
        }

        return $this->renderer->render($name, $data);
    }

    /**
     * Add path in Zend\View\Resolver\TemplatePathStack
     *
     * @param string $dir Directory
     *
     * @return AbstractDatatype
     */
    public function addPath($dir)
    {
        if (empty($this->renderer)) {
            $this->renderer = new Renderer();
        }

        $this->renderer->addPath($dir);

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

    /**
     * Get request object
     *
     * @return \Zend\Http\PhpEnvironment\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set request object
     *
     * @param Request $request Request
     *
     * @return AbstractDatatype
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get route object
     *
     * @return TreeRouteStack
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Set route object
     *
     * @param TreeRouteStack $router Router
     *
     * @return AbstractDatatype
     */
    public function setRouter(TreeRouteStack $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Get datatypes list object
     *
     * @return array
     */
    public function getDatatypesList()
    {
        return $this->datatypesList;
    }

    /**
     * Set datatypes list object
     *
     * @param array $array Array of datatypes
     *
     * @return AbstractDatatype
     */
    public function setDatatypesList(array $array)
    {
        $this->datatypesList = $array;
        return $this;
    }
}
