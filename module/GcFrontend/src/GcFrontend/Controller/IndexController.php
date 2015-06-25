<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcFrontend\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Document;
use Gc\Layout;
use Gc\Property;
use Gc\View;
use Zend\View\Model\ViewModel;
use Exception;

/**
 * Index controller for module Application
 *
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Controller
 */
class IndexController extends Action
{
    /**
     * View filename
     *
     * @var string
     */
    const VIEW_PATH = 'application/index/view-content';

    /**
     * View filename
     *
     * @var string
     */
    const LAYOUT_PATH = 'application/index/layout-content';

    /**
     * Generate frontend from url key
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $coreConfig = $this->getServiceLocator()->get('CoreConfig');

        $viewModel = new ViewModel();
        $this->events()->trigger('Front', 'preDispatch', $this, array('viewModel' => $viewModel));

        if ($coreConfig->getValue('site_is_offline') == 1) {
            $isAdmin = $this->getServiceLocator()->get('Auth')->hasIdentity();
            if (!$isAdmin) {
                $document = Document\Model::fromId($coreConfig->getValue('site_offline_document'));
                if (empty($document)) {
                    $viewModel->setTemplate('gc-frontend/site-is-offline');
                    $viewModel->setTerminal(true);
                    return $viewModel;
                }
            }
        }

        try {
            $document = $this->getServiceLocator()->get('CurrentDocument');
        } catch (Exception $e) {
            //Don't care, page is just not found
        }

        $variables = array();
        if (empty($document)) {
            // 404
            $this->getResponse()->setStatusCode(404);
            $layout = Layout\Model::fromId($coreConfig->getValue('site_404_layout'));
            if (empty($layout)) {
                $viewModel->setTerminal(true);
            }
        } else {
            //Load properties from document id
            $properties = new Property\Collection();
            $properties->load(null, null, $document->getId());

            foreach ($properties->getProperties() as $property) {
                $value = $property->getValue();

                if ($this->isSerialized($value)) {
                    $value = unserialize($value);
                }

                $viewModel->setVariable($property->getIdentifier(), $value);
                $this->layout()->setVariable($property->getIdentifier(), $value);
                $variables[$property->getIdentifier()] = $value;
            }

            //Set view from database
            $view   = $document->getView();
            $layout = $document->getLayout();
        }

        if (!empty($layout)) {
            $this->layout()->setTemplate('layout/' . $layout->getIdentifier());
        }

        if (!empty($view)) {
            $viewModel->setTemplate('view/' . $view->getIdentifier());
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->params()->fromQuery('terminate_layout') or $this->params()->fromPost('terminate_layout')) {
                $viewModel->setTerminal(true);
            }
        }

        $this->events()->trigger('Front', 'postDispatch', $this, array('viewModel' => $viewModel));

        return $viewModel;
    }

    /**
     * Defined is can unserialize string
     *
     * @param string $string String
     *
     * @return boolean
     */
    protected function isSerialized($string)
    {
        if (trim($string) == '') {
            return false;
        }

        if (preg_match('/^(i|s|a|o|d|N)(.*);/si', $string)) {
            return true;
        }

        return false;
    }
}
