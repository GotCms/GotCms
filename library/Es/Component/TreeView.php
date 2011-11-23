<?php
/**
 * @author Pierre RAMBAUD
 *
 */
class ES_Component_TreeView
{
    protected $_data;

    /**
    * @param array $array
    */
    public function __construct(Array $array)
    {
        $this->_data = $array;
    }


    /**
    * @param array $tab contains objects
    * @return string
    */
    public function render(Array $treeview_data = NULL)
    {
        $html = '<ul';
        if($treeview_data === NULL)
        {
            $treeview_data = $this->_data;
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
                $renderChildren = $this->render($children);
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
            $html = '<a id="'.$iterator->getIterableId().'" href="'.$iterator->getUrl().'">'.$iterator->getName().'</a></span>';
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
