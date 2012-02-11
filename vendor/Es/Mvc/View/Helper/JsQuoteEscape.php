<?php
namespace Es\Mvc\View\Helper;
use Zend\View\Helper\AbstractHelper;
class JsQuoteEscape extends AbstractHelper
{
    public function __invoke($data, $quote = "'")
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
