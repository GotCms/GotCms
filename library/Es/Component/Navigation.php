<?php
class Es_Component_Navigation {
	const XML_NAV_HEADER = '<?xml version="1.0" encoding="UTF-8"?><configdata><nav>';
	const XML_NAV_FOOTER = '</nav></configdata>';
	
	private $_documents_list;
	protected $_baseUrl;
	
	public function __construct() {
		$this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$documents = new Es_Document_Collection();
		$documents_children = $documents->getChildren();
		$this->_documents_list = $documents_children;
	}
	
	/**
	 * @return renderer of navigation
	 */
	public function render(Array $documents_list = null, $parent_url = null) {
		$nav = '';
		$hasFooter = false;
		if($documents_list === null && !empty($this->_documents_list)) {
			$nav .= self::XML_NAV_HEADER;
			$documents_list = $this->_documents_list;
			$hasFooter = true;
		}
        foreach($documents_list as $document_child) {
        	$children = $document_child->getChildren();
        	if($document_child->canShowInNav() && $document_child->isPublished()) {
        		$nav .= '<document-'.$document_child->getDocumentUrlKey().'>';
        		$nav .= '<label>'.$document_child->getName().'</label>';
        		$nav .= '<uri>'.$this->_baseUrl.($parent_url !== null ? $parent_url : '').'/'.$document_child->getDocumentUrlKey().'</uri>';
        		if(!empty($children) && is_array($children)) {
        			$nav .= '<pages>';
        			$nav .= $this->render($children);
        			$nav .= '</pages>';
        		}
        		$nav .= '</document-'.$document_child->getDocumentUrlKey().'>';
        	} else {
        		if(!empty($children) && is_array($children)) {
        			$nav .= $this->render($children);
        		}
        	}
        }
        if($hasFooter === true) {
			$nav .= self::XML_NAV_FOOTER;
        }
echo $nav;
		return $nav;
	}
	
	/**
	 * @return renderer of navigation
	 */
	public function __toString() {
		try {
			return $this->render();
		} catch(Exception $e) {  
			trigger_error($e->getMessage(), E_USER_ERROR);  
			return '';  
		}  
	}
}
