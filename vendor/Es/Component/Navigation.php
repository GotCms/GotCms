<?php
/**
 * @author Pierre RAMBAUD
 *
 */
namespace Es\Component;

use Es\Document;

class Navigation
{
    const XML_NAV_HEADER = '<?xml version="1.0" encoding="UTF-8"?><configdata><nav>';
    const XML_NAV_FOOTER = '</nav></configdata>';

    protected $_documents;
    protected $_basePath;

    public function __construct()
    {
        $documents = new Document\Collection();
        $documents->load(0);
        $this->_documents = $documents->getDocuments();
    }

    public function setBasePath($path)
    {
        $this->_basePath = $path;
        return $this;
    }

    public function getBasePath()
    {
        return $this->_basePath;
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
            if($document->showInNav() && $document->isPublished())
            {
                $navigation .= '<document-'.$document->getUrlKey().'>';
                $navigation .= '<label>'.$document->getName().'</label>';
                $navigation .= '<uri>' . $this->getBasePath() . ($parent_url !== NULL ? $parent_url : '').'/'.$document->getUrlKey().'</uri>';
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
