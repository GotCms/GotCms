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
    Gc\Datatype;

abstract class AbstractPrevalueEditor extends Object
{
    /**
     * @var AbstractDatatype
     */
    protected $_datatype;

    /**
     * @var mixed
     */
    protected $_config;

    /**
     * Abstract function for save Prevalue Editor
     * @abstract
     * @return void
     */
    abstract public function save();

    /**
     * Abstract function for load Prevalue Editor
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
        parent::__construct();
    }

    /**
     * get configuration
     * @return void
     */
    protected function getConfig()
    {
        if(empty($this->_config))
        {
            $this->_config = unserialize($this->getDatatype()->getConfig());
        }

        return $this->_config;
    }

    /**
     * Set Configuration
     * @return \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    protected function setConfig($value)
    {
        $this->getDatatype()->setConfig($value);
        return $this;
    }

    /**
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        return $GLOBALS['application']->getRequest();
    }

    /**
     * @return \Gc\Datatype\AbstractDatatype
     */
    public function getDatatype()
    {
        return $this->_datatype;
    }
}
