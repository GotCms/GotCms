<?php
abstract class Es_Core_Object
{

	protected static $_underscoreCache = array();
	protected $_data = array();
	private $_db = null;

    public function __construct()
    {
        $this->_construct();
    }

    protected function _construct()
    {

    }

    public function getAdapter()
    {
    	if($this->_db === null)
    	{
    		$this->_db = Zend_Registry::get('db');
    	}

    	return $this->_db;
    }

	public function __call($method, $args)
	{
		switch (substr($method, 0, 3))
		{
			case 'get' :
				$key = $this->_underscore(substr($method,3));
				$data = $this->getData($key, isset($args[0]) ? $args[0] : null);
				return $data;
			case 'set' :
				$key = $this->_underscore(substr($method,3));
				$result = $this->setData($key, isset($args[0]) ? $args[0] : null);
				return $result;
			case 'uns' :
				$key = $this->_underscore(substr($method,3));
				$result = $this->unsetData($key);
				return $result;
			case 'has' :
				$key = $this->_underscore(substr($method,3));
				return $this->hasData($key);
		}

		throw new Es_Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
	}

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

	public function getData($key='', $index=null)
	{
		if (''===$key) {
			return $this->_data;
		}

		$default = null;

        // accept a/b/c as ['a']['b']['c']
		if (strpos($key,'/')) {
			$keyArr = explode('/', $key);
			$data = $this->_data;
			foreach ($keyArr as $i=>$k)
			{
				if ($k==='')
				{
					return $default;
				}

				if (is_array($data)) {
					if (!isset($data[$k]))
					{
						return $default;
					}

					$data = $data[$k];
				}
				elseif ($data instanceof Varien_Object)
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
			if (is_array($value)) {
				if (isset($value[$index]))
				{
					return $value[$index];
				}

				return null;
			}
			elseif (is_string($value))
			{
				$arr = explode("\n", $value);
				return (isset($arr[$index]) && (!empty($arr[$index]) || strlen($arr[$index]) > 0)) ? $arr[$index] : null;
			}
			elseif ($value instanceof Es_Core_Object)
			{
				return $value->getData($index);
			}

			return $default;
		}
		return $default;
	}

	public function setData($key, $value = null)
	{
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

	public function hasData($key='')
	{
		if (empty($key) || !is_string($key))
		{
			return !empty($this->_data);
		}

		return array_key_exists($key, $this->_data);
	}

	public function unsetData($key=null)
	{
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
}
