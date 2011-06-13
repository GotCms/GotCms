<?php
class Es_Exception
{
	public function __construct($message)
	{
		/*
		 * TODO Es_Core_Exception
		 */
		Zend_Debug::dump($message);
	}
}