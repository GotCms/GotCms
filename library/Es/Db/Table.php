<?php

/**
 * Es_Db_Table
 *
 * @category   	Es
 * @package		Es_Db_Table
 * @author	  	RAMBAUD Pierre
 */

class Es_Db_Table extends Zend_Db_Table_Abstract
{
	/**
	 * Object attributes
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Data changes flag (TRUE after setData|unsetData call)
	 * @var $_hasDataChange bool
	 */
	protected $_hasDataChanges = FALSE;

	/**
	* Original data that was loaded
	*
	* @var array
	*/
	protected $_origData;

	/**
	 * Setter/Getter underscore transformation cache
	 *
	 * @var array
	 */
	protected static $_underscoreCache = array();

	/**
	 * Get data change status
	 *
	 * @return bool
	 */
	public function hasDataChanges()
	{
		return $this->_hasDataChanges;
	}

	/**
	 * Set Id
	 * @return Es_Db_Table
	 */
	protected function setId($id = NULL)
	{
		return $this->setData('id', $id);
	}

	/**
	 * Add data to the object.
	 *
	 * Retains previous data in the object.
	 *
	 * @param array $arr
	 * @return Es_Db_Table
	 */
	public function addData(array $arr)
	{
		foreach($arr as $index => $value)
		{
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
	 * @param string|array $key
	 * @param mixed $value
	 * @return Es_Db_Table
	 */
	public function setData($key, $value = NULL)
	{
		$this->_hasDataChanges = TRUE;
		if(is_array($key))
		{
			$this->_data = $key;
		}
		else
		{
			$this->_data[$key] = $value;
		}

		return $this;
	}

	/**
	 * Unset data from the object.
	 *
	 * $key can be a string only. Array will be ignored.
	 *
	 * @param string $key
	 * @return Es_Db_Table
	 */
	public function unsetData($key = NULL)
	{
		$this->_hasDataChanges = TRUE;
		if (is_null($key))
		{
			$this->_data = array();
		}
		else
		{
			unset($this->_data[$key]);
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
	 * @param string $key
	 * @param string|int $index
	 * @return mixed
	 */
	public function getData($key='', $index = NULL)
	{
		if (''===$key)
		{
			return $this->_data;
		}

		$default = NULL;

		// accept a/b/c as ['a']['b']['c']
		if (strpos($key,'/'))
		{
			$keyArr = explode('/', $key);
			$data = $this->_data;
			foreach ($keyArr as $i => $k)
			{
				if ($k==='')
				{
					return $default;
				}

				if (is_array($data))
				{
					if (!isset($data[$k]))
					{
						return $default;
					}

					$data = $data[$k];
				}
				elseif ($data instanceof Es_Object)
				{
					$data = $data->getData($k);
				}
				else
				{
					return $default;
				}
			}

			return $data;
		}

		// legacy functionality for $index
		if (isset($this->_data[$key]))
		{
			if (is_null($index))
			{
				return $this->_data[$key];
			}

			$value = $this->_data[$key];
			if (is_array($value))
			{
				if (isset($value[$index]))
				{
					return $value[$index];
				}

				return NULL;
			}
			elseif (is_string($value))
			{
				$arr = explode("\n", $value);
				return (isset($arr[$index]) && (!empty($arr[$index]) || strlen($arr[$index]) > 0)) ? $arr[$index] : NULL;
			}
			elseif ($value instanceof Es_Object)
			{
				return $value->getData($index);
			}

			return $default;
		}

		return $default;
	}

	/**
	 * Get value from _data array without parse key
	 *
	 * @param   string $key
	 * @return  mixed
	 */
	protected function _getData($key)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : NULL;
	}

	/**
	 * If $key is empty, checks whether there's any data in the object
	 * Otherwise checks if the specified attribute is set.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasData($key='')
	{
		if (empty($key) || !is_string($key))
		{
			return !empty($this->_data);
		}

		return array_key_exists($key, $this->_data);
	}

	/**
	 * Convert object attributes to array
	 *
	 * @param  array $arrAttributes array of required attributes
	 * @return array
	 */
	public function __toArray(array $arrAttributes = array())
	{
		if (empty($arrAttributes))
		{
			return $this->_data;
		}

		$arrRes = array();
		foreach ($arrAttributes as $attribute)
		{
			if (isset($this->_data[$attribute]))
			{
				$arrRes[$attribute] = $this->_data[$attribute];
			}
			else
			{
				$arrRes[$attribute] = NULL;
			}
		}

		return $arrRes;
	}

	/**
	 * Public wrapper for __toArray
	 *
	 * @param array $arrAttributes
	 * @return array
	 */
	public function toArray(array $arrAttributes = array())
	{
		return $this->__toArray($arrAttributes);
	}

	/**
	 * Set required array elements
	 *
	 * @param   array $arr
	 * @param   array $elements
	 * @return  array
	 */
	protected function _prepareArray(&$arr, array $elements=array())
	{
		foreach ($elements as $element) {
			if (!isset($arr[$element])) {
				$arr[$element] = NULL;
			}
		}
		return $arr;
	}

	/**
	 * Convert object attributes to XML
	 *
	 * @param  array $arrAttributes array of required attributes
	 * @param string $rootName name of the root element
	 * @return string
	 */
	protected function __toXml(array $arrAttributes = array(), $rootName = 'item', $addOpenTag=FALSE, $addCdata=TRUE)
	{
		$xml = '';
		if ($addOpenTag)
		{
			$xml.= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		}

		if (!empty($rootName))
		{
			$xml.= '<'.$rootName.'>'."\n";
		}

		$xmlModel = new Es_Simplexml_Element('<node></node>');
		$arrData = $this->toArray($arrAttributes);
		foreach ($arrData as $fieldName => $fieldValue)
		{
			if ($addCdata === TRUE)
			{
				$fieldValue = "<![CDATA[$fieldValue]]>";
			}
			else
			{
				$fieldValue = $xmlModel->xmlentities($fieldValue);
			}

			$xml.= "<$fieldName>$fieldValue</$fieldName>"."\n";
		}

		if (!empty($rootName))
		{

			$xml.= '</'.$rootName.'>'."\n";
		}

		return $xml;
	}

	/**
	 * Public wrapper for __toXml
	 *
	 * @param array $arrAttributes
	 * @param string $rootName
	 * @return string
	 */
	public function toXml(array $arrAttributes = array(), $rootName = 'item', $addOpenTag=FALSE, $addCdata=TRUE)
	{
		return $this->__toXml($arrAttributes, $rootName, $addOpenTag, $addCdata);
	}

	/**
	 * Convert object attributes to JSON
	 *
	 * @param  array $arrAttributes array of required attributes
	 * @return string
	 */
	protected function __toJson(array $arrAttributes = array())
	{
		$arrData = $this->toArray($arrAttributes);
		$json = Zend_Json::encode($arrData);
		return $json;
	}

	/**
	 * Public wrapper for __toJson
	 *
	 * @param array $arrAttributes
	 * @return string
	 */
	public function toJson(array $arrAttributes = array())
	{
		return $this->__toJson($arrAttributes);
	}

	/**
	 * Public wrapper for __toString
	 *
	 * Will use $format as an template and substitute {{key}} for attributes
	 *
	 * @param string $format
	 * @return string
	 */
	public function toString($format='')
	{
		if (empty($format))
		{
			$str = implode(', ', $this->getData());
		}
		else
		{
			preg_match_all('/\{\{([a-z0-9_]+)\}\}/is', $format, $matches);
			foreach ($matches[1] as $var)
			{
				$format = str_replace('{{'.$var.'}}', $this->getData($var), $format);
			}

			$str = $format;
		}

		return $str;
	}

	/**
	 * Set/Get attribute wrapper
	 *
	 * @param   string $method
	 * @param   array $args
	 * @return  mixed
	 */
	public function __call($method, $args)
	{
		switch (substr($method, 0, 3))
		{
			case 'get' :
				//Es_Profiler::start('GETTER: '.get_class($this).'::'.$method);
				$key = $this->_underscore(substr($method,3));
				$data = $this->getData($key, isset($args[0]) ? $args[0] : NULL);
				//Es_Profiler::stop('GETTER: '.get_class($this).'::'.$method);
				return $data;

			case 'set' :
				//Es_Profiler::start('SETTER: '.get_class($this).'::'.$method);
				$key = $this->_underscore(substr($method,3));
				$result = $this->setData($key, isset($args[0]) ? $args[0] : NULL);
				//Es_Profiler::stop('SETTER: '.get_class($this).'::'.$method);
				return $result;

			case 'uns' :
				//Es_Profiler::start('UNS: '.get_class($this).'::'.$method);
				$key = $this->_underscore(substr($method,3));
				$result = $this->unsetData($key);
				//Es_Profiler::stop('UNS: '.get_class($this).'::'.$method);
				return $result;

			case 'has' :
				//Es_Profiler::start('HAS: '.get_class($this).'::'.$method);
				$key = $this->_underscore(substr($method,3));
				//Es_Profiler::stop('HAS: '.get_class($this).'::'.$method);
				return isset($this->_data[$key]);
		}

		throw new Es_Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
	}

	/**
	 * Attribute getter (deprecated)
	 *
	 * @param string $var
	 * @return mixed
	 */

	public function __get($var)
	{
		$var = $this->_underscore($var);
		return $this->getData($var);
	}

	/**
	 * Attribute setter (deprecated)
	 *
	 * @param string $var
	 * @param mixed $value
	 */
	public function __set($var, $value)
	{
		$var = $this->_underscore($var);
		$this->setData($var, $value);
	}

	/**
	 * Converts field names for setters and geters
	 *
	 * $this->setMyField($value) === $this->setData('my_field', $value)
	 * Uses cache to eliminate unneccessary preg_replace
	 *
	 * @param string $name
	 * @return string
	 */
	protected function _underscore($name)
	{
		if (isset(self::$_underscoreCache[$name]))
		{
			return self::$_underscoreCache[$name];
		}

		$result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));

		self::$_underscoreCache[$name] = $result;
		return $result;
	}

	protected function _camelize($name)
	{
		return uc_words($name, '');
	}

	/**
	 * Get object loaded data (original data)
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOrigData($key = NULL)
	{
		if (is_null($key))
		{
			return $this->_origData;
		}

		return isset($this->_origData[$key]) ? $this->_origData[$key] : NULL;
	}

	/**
	 * Initialize object original data
	 *
	 * @param string $key
	 * @param mixed $data
	 * @return Es_Object
	 */
	public function setOrigData($key = NULL, $data = NULL)
	{
		if (is_null($key))
		{
			$this->_origData = $this->_data;
		}
		else
		{
			$this->_origData[$key] = $data;
		}

		return $this;
	}

	/**
	 * Present object data as string in debug mode
	 *
	 * @param mixed $data
	 * @param array $objects
	 * @return string
	 */
	public function debug($data = NULL, &$objects = array())
	{
		if (is_null($data))
		{
			$hash = spl_object_hash($this);
			if (!empty($objects[$hash]))
			{
				return '*** RECURSION ***';
			}
			$objects[$hash] = TRUE;
			$data = $this->getData();
		}

		$debug = array();
		foreach ($data as $key => $value)
		{
			if (is_scalar($value))
			{
				$debug[$key] = $value;
			}
			elseif (is_array($value))
			{
				$debug[$key] = $this->debug($value, $objects);
			}
			elseif ($value instanceof Es_Db_Table)
			{
				$debug[$key.' ('.get_class($value).')'] = $value->debug(NULL, $objects);
			}
		}
		return $debug;
	}

	/**
	 * Implementation of ArrayAccess::offsetSet()
	 *
	 * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->_data[$offset] = $value;
	}

	/**
	 * Implementation of ArrayAccess::offsetExists()
	 *
	 * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
	 * @param string $offset
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->_data[$offset]);
	}

	/**
	 * Implementation of ArrayAccess::offsetUnset()
	 *
	 * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
	 * @param string $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}

	/**
	 * Implementation of ArrayAccess::offsetGet()
	 *
	 * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return isset($this->_data[$offset]) ? $this->_data[$offset] : NULL;
	}
}