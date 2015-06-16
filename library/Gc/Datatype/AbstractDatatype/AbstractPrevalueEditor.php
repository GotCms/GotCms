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

/**
 * Abstract Prevalue Editor class
 * Use for display Prevalue Editor in Manage Development -> Datatypes
 *
 * @category   Gc
 * @package    Library
 * @subpackage Datatype\AbstractDatatype
 */
abstract class AbstractPrevalueEditor extends Object
{
    /**
     * Datatype
     *
     * @var Datatype\AbstractDatatype
     */
    protected $datatype;

    /**
     * Abstract function for save Prevalue Editor
     *
     * @abstract
     * @return void
     */
    abstract public function save();

    /**
     * Abstract function for load Prevalue Editor
     *
     * @abstract
     * @return void
     */
    abstract public function load();

    /**
     * Abstract function for save Editor
     *
     * @param Datatype\AbstractDatatype $datatypeAbstract Datatype
     */
    public function __construct(Datatype\AbstractDatatype $datatypeAbstract)
    {
        $this->datatype = $datatypeAbstract;
        parent::__construct();
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
     * Get current datatype
     *
     * @return Datatype\AbstractDatatype
     */
    public function getDatatype()
    {
        return $this->datatype;
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
