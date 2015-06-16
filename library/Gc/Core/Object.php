<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 *(at your option) any later version.
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
 * @subpackage Core
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Core;

use Zend\Json\Json;

/**
 * Abstract object, all classes are extends from it to
 * automate accessors, generate xml, json or array.
 *
 * @category   Gc
 * @package    Library
 * @subpackage Core
 * @example if someone want to store data in object he can just do $this->setWantIWant($value);
 * or $this->setData('what_i_whant', $value);
 * and retrieve value with $this->getWhatIWhat(); or $this->getData('what_i_want').
 *
 */
abstract class Object
{
     /**
     * Original data
     *
     * @var array
     */
     protected $origData;

    /**
     * Object attributes
     *
     * @var array
     */
    protected $data = array();

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $underscoreCache = array();

    /**
     * Set Id
     *
     * @param integer $id Id
     *
     * @return \Gc\Core\Object
     */
    protected function setId($id = null)
    {
        return $this->setData('id', $id);
    }

    /**
     * Initialize constructor
     */
    public function __construct()
    {
        $this->init();
    }


    /**
     * Initialize data
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $array Data
     *
     * @return Object
     */
    public function addData(array $array)
    {
        foreach ($array as $index => $value) {
            $this->setData($index, $value);
        }

        return $this;
    }

    /**
     * Overwrite data in the object.
     *
     * $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key   Key
     * @param mixed        $value Value
     *
     * @return \Gc\Core\Object
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = $key;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Unset data from the object.
     *
     * $key can be a string only. Array will be ignored.
     *
     * @param string $key Key
     *
     * @return \Gc\Core\Object
     */
    public function unsetData($key = null)
    {
        if (is_null($key)) {
            $this->data = array();
        } else {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Retrieves data from the object
     *
     * If $key is empty will return all the data as an array
     * Otherwise it will return value of the attribute specified by $key
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member.
     *
     * @param string     $key   key
     * @param string|int $index Index
     *
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        if ('' === $key) {
            return $this->data;
        }

        $default = null;

        // accept a/b/c as ['a']['b']['c']
        // Not  !== false no need '/a/b always return null
        if (strpos($key, '/')) {
            $keyArray = explode('/', $key);
            $data     = $this->data;
            foreach ($keyArray as $i => $k) {
                if ($k === '') {
                    return $default;
                }

                if (is_array($data)) {
                    if (!isset($data[$k])) {
                        return $default;
                    }

                    $data = $data[$k];
                }
            }

            return $data;
        }

        // legacy functionality for $index
        if (isset($this->data[$key])) {
            if (is_null($index)) {
                return $this->data[$key];
            }

            $value = $this->data[$key];
            if (is_array($value)) {
                if (isset($value[$index])) {
                    return $value[$index];
                }

                return null;
            } elseif (is_string($value)) {
                $array = explode(PHP_EOL, $value);
                return(isset($array[$index])
                    &&(!empty($array[$index])
                    || strlen($array[$index]) > 0)) ? $array[$index] : null;
            } elseif ($value instanceof Object) {
                return $value->getData($index);
            }

            return $default;
        }

        return $default;
    }

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key Key
     *
     * @return boolean
     */
    public function hasData($key = '')
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->data);
        }

        return array_key_exists($key, $this->data);
    }

    /**
     * Convert object attributes to array
     *
     * @param array $array array of required attributes
     *
     * @return array
     */
    public function __toArray(array $array = array())
    {
        if (empty($array)) {
            return $this->data;
        }

        $arrayResult = array();
        foreach ($array as $attribute) {
            if (isset($this->data[$attribute])) {
                $arrayResult[$attribute] = $this->data[$attribute];
            } else {
                $arrayResult[$attribute] = null;
            }
        }

        return $arrayResult;
    }

    /**
     * Public wrapper for __toArray
     *
     * @param array $array Data
     *
     * @return array
     */
    public function toArray(array $array = array())
    {
        return $this->__toArray($array);
    }

    /**
     * Convert object attributes to XML
     *
     * @param array   $array      Array of required attributes
     * @param string  $rootName   Name of the root element
     * @param boolean $addOpenTag Insert <?xml>
     * @param boolean $addCdata   Insert CDATA[]
     *
     * @return string
     */
    protected function __toXml(
        array $array = array(),
        $rootName = 'item',
        $addOpenTag = false,
        $addCdata = true
    ) {
        $xml = '';
        if ($addOpenTag) {
            $xml .= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        }

        if (empty($array)) {
            $array = $this->toArray();
        }

        if (!empty($rootName) and !is_numeric($rootName)) {
            $xml .= '<' . $rootName;
            if (isset($array['id'])) {
                $xml .= ' id="' . $array['id'] . '"';
                unset($array['id']);
            }

            $xml .= '>' . PHP_EOL;
        }

        foreach ($array as $fieldName => $fieldValue) {
            if (is_array($fieldValue)) {
                if (!empty($fieldValue)) {
                    $xml .= $this->__toXml($fieldValue, $fieldName);
                    continue;
                }
                $fieldValue = '';
            } elseif (is_object($fieldValue) and method_exists($fieldValue, 'toXml')) {
                $xml .= $fieldValue->toXml(array(), $fieldValue->name);
                continue;
            }

            if ($addCdata === true) {
                $fieldValue = '<![CDATA[' . $fieldValue . ']]>';
                $xml       .= '<' . $fieldName . '>' . $fieldValue . '</' . $fieldName . '>' . PHP_EOL;


            } else {
                $fieldValue = htmlentities($fieldValue);
                $xml       .= '<' . $fieldName . '>' . $fieldValue . '</' . $fieldName . '>' . PHP_EOL;
            }
        }

        if (!empty($rootName) and !is_numeric($rootName)) {
            $xml .= '</' . $rootName . '>' . PHP_EOL;
        }

        return $xml;
    }

    /**
     * Public wrapper for __toXml
     *
     * @param array   $array      Data
     * @param string  $rootName   Root name
     * @param boolean $addOpenTag Insert <?xml>
     * @param boolean $addCdata   Insert CDATA[]
     *
     * @return string
     */
    public function toXml(array $array = array(), $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        return $this->__toXml($array, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * Convert object attributes to JSON
     *
     * @param array $array array of required attributes
     *
     * @return string
     */
    protected function __toJson(array $array = array())
    {
        return Json::encode($this->toArray($array));
    }

    /**
     * Public wrapper for __toJson
     *
     * @param array $array Data
     *
     * @return string
     */
    public function toJson(array $array = array())
    {
        return $this->__toJson($array);
    }

    /**
     * Public wrapper for __toString
     *
     * Will use $format as an template and substitute {{key}} for attributes
     *
     * @param string $format Format
     *
     * @return string
     */
    public function toString($format = '')
    {
        if (empty($format)) {
            $str = implode(', ', $this->getData());
        } else {
            preg_match_all('/\{\{([a-z0-9_]+)\}\}/is', $format, $matches);
            foreach ($matches[1] as $var) {
                $format = str_replace('{{' . $var . '}}', $this->getData($var), $format);
            }

            $str = $format;
        }

        return $str;
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param string $method Method
     * @param array  $args   Arguments
     *
     * @return  mixed|\Gc\Core\Object
     */
    public function __call($method, $args)
    {
        switch(substr($method, 0, 3)) {
            case 'get':
                $key  = $this->underscore(substr($method, 3));
                $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
                return $data;
            case 'set':
                $key    = $this->underscore(substr($method, 3));
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);
                return $result;
            case 'uns':
                $key    = $this->underscore(substr($method, 3));
                $result = $this->unsetData($key);
                return $result;
            case 'has':
                $key = $this->underscore(substr($method, 3));
                return isset($this->data[$key]);
        }

        throw new \Gc\Exception(
            'Invalid method ' . get_class($this) . '::' . $method . '(' . print_r($args, true) . ')'
        );
    }

    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name Name
     *
     * @return string
     */
    protected function underscore($name)
    {
        if (isset(self::$underscoreCache[$name])) {
            return self::$underscoreCache[$name];
        }

        $result                       = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $name));
        self::$underscoreCache[$name] = $result;

        return $result;
    }

    /**
     * Implementation of ArrayAccess::offsetSet()
     *
     * @param string $offset Offset
     * @param mixed  $value  Value
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Implementation of ArrayAccess::offsetExists()
     *
     * @param string $offset Offset
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Implementation of ArrayAccess::offsetUnset()
     *
     * @param string $offset Offset
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Implementation of ArrayAccess::offsetGet()
     *
     * @param string $offset Offset
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * Get Original data
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public function getOrigData($key = null)
    {
        if (is_null($key)) {
            return $this->origData;
        }

        return isset($this->origData[$key]) ? $this->origData[$key] : null;
    }

    /**
     * Set Original data
     *
     * @param string $key  Key
     * @param mixed  $data Data
     *
     * @return \Gc\Core\Object
     */
    public function setOrigData($key = null, $data = null)
    {
        if (is_null($key)) {
            $this->origData = $this->data;
        } else {
            $this->origData[$key] = $data;
        }

        return $this;
    }

    /**
     * Check if data has changed
     *
     * @param string $field Field
     *
     * @return boolean
     */
    public function hasDataChangedFor($field)
    {
        $newdata  = $this->getData($field);
        $origdata = $this->getOrigData($field);

        return $newdata != $origdata;
    }
}
