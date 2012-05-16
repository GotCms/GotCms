<?php

namespace Content;

use Gc\Mvc;

class Module extends Mvc\Module
{
    /**
     * Module directory path
     */
    protected $_directory = __DIR__;

    /**
     * Module namespace
     */
    protected $_namespace = __NAMESPACE__;
}
