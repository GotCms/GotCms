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
 * @subpackage Datatype\AbstractDatatype
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Datatype\AbstractDatatype;

use Gc\Core\Object;
use Gc\Datatype;
use Gc\Registry;
use Zend\EventManager\StaticEventManager;

/**
 * Abstract Editor class
 * Use for display Editor in Manage Content
 *
 * @category   Gc
 * @package    Library
 * @subpackage Datatype\AbstractDatatype
 */
abstract class AbstractEditor extends Object
{
    /**
     * Datatype
     *
     * @var AbstractDatatype
     */
    protected $datatype;

    /**
     * Property model
     *
     * @var \Gc\Property\Model
     */
    protected $property;

    /**
     * Get name of datatype
     *
     * @var string
     */
    protected $name;

    /**
     * Configuration
     *
     * @var mixed
     */
    protected $config;

    /**
     * Abstract function for save Editor
     *
     * @abstract
     * @return void
     */
    abstract public function save();

    /**
     * Abstract function for load Editor
     *
     * @abstract
     * @return void
     */
    abstract public function load();

    /**
     * Abstract function for save Editor
     *
     * @param Datatype\AbstractDatatype $datatypeAbstract Datatype
     *
     * @return void
     */
    public function __construct(Datatype\AbstractDatatype $datatypeAbstract)
    {
        $this->datatype = $datatypeAbstract;
        $this->property = $datatypeAbstract->getProperty();
        parent::__construct();
    }

    /**
     * Return property value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getProperty()->getValue();
    }

    /**
     * Set property value
     *
     * @param mixed $value Value
     *
     * @return \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    public function setValue($value)
    {
        $this->getProperty()->setValue($value);

        return $this;
    }

    /**
     * Get configuration
     *
     * @return void
     */
    public function getConfig()
    {
        return $this->getDatatype()->getConfig();
    }

    /**
     * Set Configuration
     *
     * @param mixed $value Value
     *
     * @return \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    public function setConfig($value)
    {
        $this->getDatatype()->setConfig($value);
        return $this;
    }

    /**
     * Upload dir path
     *
     * @return string
     */
    public function getUploadUrl()
    {
        return $this->getDatatype()->getUploadUrl($this->getProperty()->getId());
    }

    /**
     * Get datatype name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getDatatype()->getName() . $this->getProperty()->getId();
    }

    /**
     * Return property model
     *
     * @return \Gc\Property\Model
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Get datatype
     *
     * @return \Gc\Datatype\AbstractDatatype
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * Get request object
     *
     * @return \Zend\Http\PhpEnvironment\Request
     */
    public function getRequest()
    {
        return $this->getDatatype()->getRequest();
    }

    /**
     * Get helper manager object
     *
     * @return \Zend\View\HelperPluginManager
     */
    public function getHelperManager()
    {
        return $this->getDatatype()->getHelperManager();
    }

    /**
     * Get router object
     *
     * @return \Zend\Mvc\Router\Http\TreeRouteStack
     */
    public function getRouter()
    {
        return $this->getDatatype()->getRouter();
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
        return $this->getDatatype()->render($name, $data);
    }

    /**
     * Add path in Zend\View\Resolver\TemplatePathStack
     *
     * @param string $dir Directory
     *
     * @return \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    public function addPath($dir)
    {
        $this->getDatatype()->addPath($dir);

        return $this;
    }

    /**
     * Retrieve helper from $name
     *
     * @param string $name Name
     *
     * @return mixed
     */
    public function getHelper($name)
    {
        return $this->getDatatype()->getHelper($name);
    }
}
