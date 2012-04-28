<?php
namespace Gc;

class Exception
{
    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {
        /*
        * TODO Gc\Core\Exception
        */
        var_dump($msg);
        die();
    }
}
