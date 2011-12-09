<?php
/**
 * @author Pierre RAMBAUD
 *
 */
class Es_Component_TreeView
{
    protected $_data;

    /**
    * @param array $array
    */
    public function __construct(Array $treeview_data)
    {
        $this->_data = $treeview_data;
    }


    /**
    * @param array $tab contains objects
    * @return string
    */
    static function render(Array $treeview_data = NULL, $init = TRUE)
    {
        $html = '<ul';
        if($init)
        {
            $html .= ' id="browser" class="treeview filetree"';
        }

        $html .= '>';

        foreach($treeview_data as $iterator)
        {
            $children = $iterator->getChildren();
            $haveChildren = !empty($children);
            $html .= '<li';
            if($haveChildren)
            {
                $class = ' class="closed"';
                $renderChildren = self::render($children, FALSE);
            }
            else
            {
                $renderChildren = "";
                $class = "";
            }

            if(in_array($iterator->getIcon(), array('folder','file')))
            {
                $icon = false;
            }
            else
            {
                $icon = 'style="background:url(medias/icon/'.$iterator->getIcon().') no-repeat scroll 0 0;padding-left:20px;"';
            }

            $html .= $class.'><span class="'.$iterator->getIcon().'" '.($icon !== false ? $icon : '').'>';
            $html .= '<a rel="'.$iterator->getId().'" id="'.$iterator->getIterableId().'" href="'.$iterator->getUrl().'">'.$iterator->getName().'</a></span>';

            $html .='</span>';
            $html .= $renderChildren;
            $html .='</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch(Exception $e)
        {
            trigger_error($e->getMessage(), E_USER_ERROR);
            return FALSE;
        }
    }
}
