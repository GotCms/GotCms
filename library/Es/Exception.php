<?php
class Es_Exception extends Exception
{
    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {
        /*
        * TODO Es_Core_Exception
        */
        Zend_Debug::dump($msg);
        die();
    }
}
