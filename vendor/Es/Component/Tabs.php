<?php
/**
 * @author Pierre RAMBAUD
 *
 */
namespace Es\Component;

class Tabs
{
    private $_data;

    /**
    * @param array $array
    */
    public function __construct(Array $array)
    {
        $this->_data = $array;
    }

    /**
    * @param array $tabs contains objects
    * @return string
    */
    public function render(Array $tabs = NULL)
    {
        $i = 0;
        $html = '<ul>';
        if($tabs === NULL)
        {
            $tabs = $this->_data;
        }

        $i = 1;
        foreach($tabs as $iterator)
        {
            if(!is_object($iterator))
            {
                $html .= '<li><a href="#tabs-'.$i.'">'.$iterator.'</a></li>';
            }
            else
            {
                $html .= '<li><a href="#tabs-'.$iterator->getId().'">'.$iterator->getName().'</a></li>';
            }

            $i++;
        }

        $html .= '</ul>';

        return $html;
    }

    public function __toString()
    {
        return $this->render();
    }
}
