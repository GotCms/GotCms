<?php

class Es_View_Helper_JsQuoteEscape extends Zend_View_Helper_Abstract
{
    public function jsQuoteEscape($data, $quote = "'")
    {
        if(is_array($data))
        {
            $result = array();
            foreach ($data as $item)
            {
                $result[] = str_replace($quote, '\\'.$quote, $item);
            }

            return $result;
        }

        return str_replace($quote, '\\'.$quote, $data);
    }
}
