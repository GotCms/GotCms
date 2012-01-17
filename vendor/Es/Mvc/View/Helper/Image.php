<?php

class Es_View_Helper_Image extends Zend_View_Helper_Abstract
{
    public function image($link)
    {
        $fc = Zend_Controller_Front::getInstance();
        $baseUrl = $fc->getBaseUrl();

        return $baseUrl.'/images/'.$link;
    }
}
