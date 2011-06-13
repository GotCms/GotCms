<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_Component_Tabs {
	private $_item;
	
	/**
	 * @param array $tab
	 */
	public function __construct(Array $tab) {
		$this->_item = $tab;
	}
	

	/**
	 * @param array $tab contains objects
	 * @return string
	 */
	public function render(Array $tab = NULL) {
		$i = 0;
		$html = '<ul>';
		if($tab === NULL) {
			$tab = $this->_item;
		} 
		$i = 1;
		foreach($tab as $iterator) {
			if(!is_object($iterator)) {
				$html .= '<li>
						<a href="#tabs-'.$i.'">'.$iterator.'</a>
					</li>';
			} else {
				$html .= '<li>
						<a href="#tabs-'.$iterator->getId().'">'.$iterator->getName().'</a>
					</li>';
			}
			$i++;
		}
		$html .= '</ul>';
		return $html;
	}
	
	public function __toString() {
		return $this->render();
	}
}