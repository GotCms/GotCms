<?php

class Es_Version
{
	protected $_version = '0.1a';

	public function __toString()
	{
		return $this->_version;
	}
}
