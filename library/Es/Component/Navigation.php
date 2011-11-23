<?php
class Es_Component_Navigation
{
    const XML_NAV_HEADER = '<?xml version="1.0" encoding="UTF-8"?><configdata><nav>';
    const XML_NAV_FOOTER = '</nav></configdata>';

    protected $_documents;
    protected $_baseUrl;

    public function __construct()
    {
        $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $documents = new Es_Model_DbTable_Document_Collection();
        $this->_documents = $documents->getDocuments();
    }

    /**
    * @return renderer of navigation
    */
    public function render(Array $documents = NULL, $parent_url = NULL)
    {
        $navigation = '';
        $hasFooter = FALSE;
        if($documents === NULL && !empty($this->_documents))
        {
            $navigation .= self::XML_NAV_HEADER;
            $documents = $this->_documents;
            $hasFooter = TRUE;
        }

        foreach($documents as $document)
        {
            $children = $document->getChildren();
            if($document->canShowInNav() && $document->isPublished())
            {
                $navigation .= '<document-'.$document->getUrlKey().'>';
                $navigation .= '<label>'.$document->getName().'</label>';
                $navigation .= '<uri>'.($parent_url !== NULL ? $parent_url : '').'/'.$document->getUrlKey().'</uri>';
                if(!empty($children) && is_array($children))
                {
                    $navigation .= '<pages>';
                    $navigation .= $this->render($children, $document->getUrlKey());
                    $navigation .= '</pages>';
                }

                $navigation .= '</document-'.$document->getUrlKey().'>';
            }
            else
            {
                if(!empty($children) && is_array($children))
                {
                    $navigation .= $this->render($children);
                }
            }
        }

        if($hasFooter === TRUE)
        {
            $navigation .= self::XML_NAV_FOOTER;
        }

        return $navigation;
    }

    /**
    * @return renderer of navigation
    */
    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch(Exception $e)
        {
            trigger_error($e->getMessage(), E_USER_ERROR);
            return '';
        }
    }
}
