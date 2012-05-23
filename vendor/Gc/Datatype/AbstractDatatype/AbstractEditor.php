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
 * @subpackage  Datatype\AbstractDatatype
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Datatype\AbstractDatatype;

use Gc\Core\Object,
    Gc\Datatype,
    Zend\EventManager\StaticEventManager;

abstract class AbstractEditor extends Object
{
    /**
     * @var AbstractDatatype
     */
    protected $_datatype;

    /**
     * @var \Gc\Property\Model
     */
    protected $_property;

    /**
     * get name of datatype
     * @var string
     */
    protected $_name;

    /**
     * Abstract function for save Editor
     * @abstract
     * @return void
     */
    abstract public function save();

    /**
     * Abstract function for load Editor
     * @abstract
     * @return void
     */
    abstract public function load();

    /**
     * Abstract function for save Editor
     * @param \Gc\Datatype\AbstractDatatype $datatype_abstract
     * @return void
     */
    public function __construct(Datatype\AbstractDatatype $datatype_abstract)
    {
        $this->_datatype = $datatype_abstract;
        $this->_property = $datatype_abstract->getProperty();
        parent::__construct();
    }

    /**
     * Return property value
     * @return string
     */
    protected function getValue()
    {
        return $this->getProperty()->getValue();
    }

    /**
     * Set property value
     * @return \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    protected function setValue($value)
    {
        $this->getProperty()->setValue($value);

        return $this;
    }

    /**
     * Save property value
     * @return boolean
     */
    protected function saveValue()
    {
        $value = $this->getValue();
        if($this->getProperty()->isRequired() and empty($value))
        {
            return FALSE;
        }

        return $this->getProperty()->saveValue();
    }

    /**
     * Save property value
     * @return mixed
     */
    protected function getConfig()
    {
        return @unserialize($this->getDatatype()->getConfig());
    }

    /**
     * Get datatype configuration
     * @return \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    protected function setConfig($value)
    {
        $this->getDatatype()->setParameters($value);

        return $this;
    }

    /**
     * Retrieve helper from $name
     * @param strin $name
     * @return mixte
     */
    public function getHelper($name)
    {
        return $this->getDatatype()->getHelper($name);
    }

    /**
     * Upload dir path
     * @return string
     */

    public function getUploadUrl()
    {
        return $this->getDatatype()->getUploadUrl().'/property/'.$this->getProperty()->getId();
    }

    /**
     * Get datatype name
     * @return string
     */
    public function getName()
    {
        return $this->getDatatype()->getName().$this->getProperty()->getId();
    }

    /**
     * @return \Gc\Property\Model
     */
    public function getProperty()
    {
        return $this->_property;
    }

    /**
     * @return \Gc\Property\Model
     */
    public function getDatatype()
    {
        return $this->_datatype;
    }

    /**
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        return $GLOBALS['application']->getRequest();
    }
}
