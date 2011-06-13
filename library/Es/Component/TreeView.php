<?php
/**
 * @author GoT
 *
 */
class ES_Component_TreeView
{
	protected $_item;
	protected $_baseUrl;

	/**
	 * @param array $tab
	 */
	public function __construct(Array $tab)
	{
		$this->_item = $tab;
		$this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	}


	/**
	 * @param array $tab contains objects
	 * @return string
	 */
	public function render(Array $tab = NULL)
	{
		$html = '<ul';
		if($tab === NULL)
		{
			$tab = $this->_item;
			$html .= ' id="browser" class="treeview filetree"';
		}

		$html .= '>';

		foreach($tab as $iterator)
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
				$icon = 'style="background:url('.$this->_baseUrl.'medias/icon/'.$iterator->getIcon().') no-repeat scroll 0 0;padding-left:20px;"';
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