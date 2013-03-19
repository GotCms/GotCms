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
 * @package    Application
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Application\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Core\Config as CoreConfig;
use Gc\Component;
use Gc\Document;
use Gc\DocumentType;
use Gc\Layout;
use Gc\Property;
use Gc\User\Visitor;
use Gc\View;
use Zend\Config\Reader\Xml;
use Zend\Cache\StorageFactory as CacheStorage;
use Zend\Navigation\Navigation;
use Zend\View\Model\ViewModel;

/**
 * Index controller for module Application
 *
 * @category   Gc_Application
 * @package    Application
 * @subpackage Controller
 */
class IndexController extends Action
{
    /**
     * Stream name
     *
     * @var string
     */
    protected $viewStream = 'zend.view';

    /**
     * View filename
     *
     * @var string
     */
    protected $viewName = 'application/index/view-content';

    /**
     * View filename
     *
     * @var string
     */
    protected $layoutName = 'application/index/layout-content';

    /**
     * View path
     *
     * @var string
     */
    protected $viewPath;

    /**
     * View path
     *
     * @var string
     */
    protected $layoutPath;

    /**
     * Cache
     *
     * @var Filesystem
     */
    protected $cache;

    /**
     * Generate frontend from url key
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $visitor    = new Visitor();
        $session    = $this->getSession();
        $session_id = $this->getSession()->getDefaultManager()->getId();
        $is_admin   = $this->getAuth()->hasIdentity();
        $is_preview = ($is_admin and $this->getRequest()->getQuery()->get('preview') === 'true');

        //Don't log preview
        if (!$is_preview and !$is_admin) {
            try {
                $session->visitorId = $visitor->getVisitorId($session_id);
            } catch (\Exception $e) {
                //don't care
            }
        }

        $this->events()->trigger('Front', 'preDispatch', null, array('object' => $this));

        if (CoreConfig::getValue('site_is_offline') == 1) {
            //Site is offline
            if (!$is_admin) {
                $document = Document\Model::fromId(CoreConfig::getValue('site_offline_document'));
                if (empty($document)) {
                    die('Site offline');
                }
            }
        }

        $existed = in_array($this->viewStream, stream_get_wrappers());
        if ($existed) {
            stream_wrapper_unregister($this->viewStream);
        }

        stream_wrapper_register($this->viewStream, 'Gc\View\Stream');
        $template_path_stack = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $template_path_stack->setUseStreamWrapper(true);
        $this->viewPath   = $template_path_stack->resolve($this->viewName);
        $this->layoutPath = $template_path_stack->resolve($this->layoutName);

        $path = $this->getRouteMatch()->getParam('path');

        $cache_is_enable = (CoreConfig::getValue('cache_is_active') == 1 and !$is_preview);
        if ($cache_is_enable) {
            $this->enableCache();
            $cache_key = ('page' . (empty($path) ? '' : '-' . str_replace('/', '-', $path)));
            if ($this->cache->hasItem($cache_key)) {
                //Retrieve cache value and set data
                $cache_value = $this->cache->getItem($cache_key);
                $view_model  = $cache_value['view_model'];
                $view_model->setTemplate($this->viewName);
                $view_model->setVariables($cache_value['layout_variables']);
                $this->layout()->setVariables($cache_value['layout_variables']);
                $this->layout()->setTemplate($this->layoutName);
                $layout_content = $cache_value['layout_content'];
                $view_content   = $cache_value['view_content'];
            }
        }

        //Cache is disable or cache isn't create
        if (empty($cache_value)) {
            if (empty($document)) {
                if (empty($path)) {
                    $document = Document\Model::fromUrlKey('');
                } else {
                    $explode_path = $this->explodePath($path);
                    $children     = null;
                    $key          = array();
                    $has_document = false;
                    $parent_id    = 0;

                    foreach ($explode_path as $url_key) {
                        $document     = null;
                        $document_tmp = null;
                        if ($has_document === false) {
                            $document_tmp = Document\Model::fromUrlKey($url_key, $parent_id);
                        }

                        if ((is_array($children)
                            and !empty($children)
                            and !in_array($document_tmp, $children)
                            and $children !== null)
                            or $document_tmp === null) {
                            $has_document = true;
                        } else {
                            if (!empty($document_tmp)) {
                                if (!$document_tmp->isPublished()) {
                                    if (!$is_preview) {
                                        break;
                                    }
                                }

                                $document  = $document_tmp;
                                $parent_id = $document->getId();
                                $children  = $document->getChildren();
                            }
                        }
                    }
                }
            }

            $view_model = new ViewModel();
            $view_model->setTemplate($this->viewName);
            $this->layout()->setTemplate($this->layoutName);

            if (empty($document)) {
                // 404
                $this->getResponse()->setStatusCode(404);
                $layout = Layout\Model::fromId(CoreConfig::getValue('site_404_layout'));
                if (!empty($layout)) {
                    $layout_content = $layout->getContent();
                } else {
                    $layout_content = '<?php echo $this->content; ?>';
                }
            } else {
                //Get all tabs of document
                $tabs = $this->loadTabs($document->getDocumentTypeId());
                //get Tabs and Properties to construct property in view
                $variables = array();
                foreach ($tabs as $tab) {
                    $tabs_array[] = $tab->getName();
                    $properties   = $this->loadProperties(
                        $document->getDocumentTypeId(),
                        $tab->getId(),
                        $document->getId()
                    );
                    foreach ($properties as $property) {
                        $value = $property->getValue();

                        if ($this->isSerialized($value)) {
                            $value = unserialize($value);
                        }

                        $view_model->setVariable($property->getIdentifier(), $value);
                        $this->layout()->setVariable($property->getIdentifier(), $value);
                        $variables[$property->getIdentifier()] = $value;
                    }
                }

                $variables['currentDocument'] = $document;
                $view_model->setVariable('currentDocument', $document);
                $this->layout()->setVariable('currentDocument', $document);

                //Set view from database
                $view   = View\Model::fromId($document->getViewId());
                $layout = Layout\Model::fromId($document->getLayoutId());

                $layout_content = $layout->getContent();
                $view_content   = $view->getContent();
            }

            if ($cache_is_enable) {
                $this->cache->addItem(
                    $cache_key,
                    array(
                        'view_model' => $view_model,
                        'layout_variables' => $variables,
                        'layout_content' => $layout->getContent(),
                        'view_content' => !empty($view) ? $view->getContent() : '',
                    )
                );
            }
        }

        file_put_contents($this->layoutPath, $layout_content);
        if (!empty($view_content)) {
            file_put_contents($this->viewPath, $view_content);
        }

        $this->events()->trigger('Front', 'postDispatch');

        return $view_model;
    }

    /**
     * Load tabs
     *
     * @param integer $document_type_id Document type id
     *
     * @return Gc\Component\Tab\Collection
     */
    protected function loadTabs($document_type_id)
    {
        $document_type = DocumentType\Model::fromId($document_type_id);
        return $document_type->getTabs();
    }


    /**
     * Load properties
     *
     * @param integer $document_type_id Document type id
     * @param integer $tab_id           Tab id
     * @param integer $document_id      Document id
     *
     * @return \Gc\Component\Property\Collection
     */
    protected function loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Property\Collection();
        $properties->load($document_type_id, $tab_id, $document_id);

        return $properties->getProperties();
    }

    /**
     * Explode path
     *
     * @param string $path Url path
     *
     * @return void
     */
    protected function explodePath($path)
    {
        $explode_path = explode('/', $path);
        if (preg_match('/\/$/', $path)) {
            array_pop($explode_path);
        }

        return $explode_path;
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

    /**
     * Enable cache
     *
     * @return void
     */
    protected function enableCache()
    {
        $cache_ttl     = (int) CoreConfig::getValue('cache_lifetime');
        $cache_handler = CoreConfig::getValue('cache_handler');

        if (!in_array($cache_handler, array('apc', 'memcached', 'filesystem'))) {
            $cache_handler = 'filesystem';
        }

        switch($cache_handler) {
            case 'memcached':
                $cache_options = array(
                    'ttl' => $cache_ttl,
                    'servers' => array(array(
                        'localhost', 11211
                    )),
                );
                break;
            case 'apc':
            default:
                $cache_options = array(
                    'ttl' => $cache_ttl,
                );
                break;
        }

        $this->cache = CacheStorage::factory(
            array(
                'adapter' => array(
                    'name' => $cache_handler,
                    'options' => $cache_options,
                ),
                'plugins' => array(
                    // Don't throw exceptions on cache errors
                    'exception_handler' => array(
                        'throw_exceptions' => false
                    ),
                    'Serializer'
                ),
            )
        );
    }
}
