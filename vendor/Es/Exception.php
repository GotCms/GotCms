<?php
namespace Es;

class Exception 
{
    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {
        /*
        * TODO Gc_Core_Exception
        */
        var_dump($msg);
        die();
    }
}
