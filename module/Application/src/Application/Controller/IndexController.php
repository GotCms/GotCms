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
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
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
use Exception;

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
     * View filename
     *
     * @var string
     */
    const VIEW_NAME = 'application/index/view-content';

    /**
     * View filename
     *
     * @var string
     */
    const LAYOUT_NAME = 'application/index/layout-content';

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
        $visitor   = new Visitor();
        $session   = $this->getSession();
        $sessionId = $this->getSession()->getDefaultManager()->getId();
        $isAdmin   = $this->getAuth()->hasIdentity();
        $isPreview = ($isAdmin and $this->getRequest()->getQuery()->get('preview') === 'true');

        //Don't log preview
        if (!$isPreview and !$isAdmin) {
            try {
                $session->visitorId = $visitor->getVisitorId($sessionId);
            } catch (Exception $e) {
                //don't care
            }
        }

        $this->events()->trigger('Front', 'preDispatch', null, array('object' => $this));

        if (CoreConfig::getValue('site_is_offline') == 1) {
            //Site is offline
            if (!$isAdmin) {
                $document = Document\Model::fromId(CoreConfig::getValue('site_offline_document'));
                if (empty($document)) {
                    die('Site offline');
                }
            }
        }

        View\Stream::register();
        $templatePathStack = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $templatePathStack->setUseStreamWrapper(true);
        $this->viewPath   = $templatePathStack->resolve(self::VIEW_NAME);
        $this->layoutPath = $templatePathStack->resolve(self::LAYOUT_NAME);

        $path = ltrim($this->getRouteMatch()->getParam('path'), '/');

        $cacheIsEnable = (CoreConfig::getValue('cache_is_active') == 1 and !$isPreview);
        if ($cacheIsEnable) {
            $this->enableCache();
            $cacheKey = ('page'
                . (empty($path) ? '' : '-'
                . preg_replace('/[^a-z0-9_\+\-]+/Di', '_', str_replace('/', '-', strtolower($path)))));
            if ($this->cache->hasItem($cacheKey)) {
                //Retrieve cache value and set data
                $cacheValue = $this->cache->getItem($cacheKey);
                $viewModel  = $cacheValue['view_model'];
                $viewModel->setTemplate(self::VIEW_NAME);
                $viewModel->setVariables($cacheValue['layout_variables']);
                $this->layout()->setVariables($cacheValue['layout_variables']);
                $this->layout()->setTemplate(self::LAYOUT_NAME);
                $layoutContent = $cacheValue['layout_content'];
                $viewContent   = $cacheValue['view_content'];
            }
        }

        if (empty($viewModel)) {
            $viewModel = new ViewModel();
            $viewModel->setTemplate(self::VIEW_NAME);
            $this->layout()->setTemplate(self::LAYOUT_NAME);
        }

        //Cache is disable or cache isn't create
        if (empty($cacheValue)) {
            if (empty($document)) {
                if (empty($path)) {
                    $document = Document\Model::fromUrlKey('');
                } else {
                    $explodePath = $this->explodePath($path);
                    $children    = null;
                    $key         = array();
                    $hasDocument = false;
                    $parentId    = null;

                    foreach ($explodePath as $urlKey) {
                        $document    = null;
                        $documentTmp = null;
                        if ($hasDocument === false) {
                            $documentTmp = Document\Model::fromUrlKey($urlKey, $parentId);
                            //Test for home as parent_id
                            if (empty($documentTmp) and ($homeDocument = Document\Model::fromUrlKey('')) !== false) {
                                $documentTmp  = Document\Model::fromUrlKey($urlKey, $homeDocument->getId());
                            }
                        }

                        if ((is_array($children)
                            and !empty($children)
                            and !in_array($documentTmp, $children)
                            and $children !== null)
                            or $documentTmp === null) {
                            $hasDocument = true;
                        } else {
                            if (empty($documentTmp)) {
                                break;
                            } else {
                                if (!$documentTmp->isPublished()) {
                                    if (!$isPreview) {
                                        break;
                                    }
                                }

                                $document = $documentTmp;
                                $parentId = $document->getId();
                                $children = $document->getChildren();
                            }
                        }
                    }
                }
            }


            $variables = array();
            if (empty($document)) {
                // 404
                $this->getResponse()->setStatusCode(404);
                $layout = Layout\Model::fromId(CoreConfig::getValue('site_404_layout'));
                if (!empty($layout)) {
                    $layoutContent = $layout->getContent();
                } else {
                    $layoutContent = '<?php echo $this->content; ?>';
                }
            } else {
                //Get all tabs of document
                $tabs = $this->loadTabs($document->getDocumentTypeId());
                //get Tabs and Properties to construct property in view
                foreach ($tabs as $tab) {
                    $tabsArray[] = $tab->getName();
                    $properties  = $this->loadProperties(
                        $document->getDocumentTypeId(),
                        $tab->getId(),
                        $document->getId()
                    );
                    foreach ($properties as $property) {
                        $value = $property->getValue();

                        if ($this->isSerialized($value)) {
                            $value = unserialize($value);
                        }

                        $viewModel->setVariable($property->getIdentifier(), $value);
                        $this->layout()->setVariable($property->getIdentifier(), $value);
                        $variables[$property->getIdentifier()] = $value;
                    }
                }

                $variables['currentDocument'] = $document;
                $viewModel->setVariable('currentDocument', $document);
                $this->layout()->setVariable('currentDocument', $document);

                //Set view from database
                $view   = View\Model::fromId($document->getViewId());
                $layout = Layout\Model::fromId($document->getLayoutId());

                $layoutContent = $layout->getContent();
                $viewContent   = $view->getContent();
            }

            if ($cacheIsEnable && !empty($document)) {
                $this->cache->addItem(
                    $cacheKey,
                    array(
                        'view_model' => $viewModel,
                        'layout_variables' => $variables,
                        'layout_content' => $layout->getContent(),
                        'view_content' => !empty($view) ? $view->getContent() : '',
                    )
                );
            }
        }

        file_put_contents($this->layoutPath, $layoutContent);
        if (!empty($viewContent)) {
            file_put_contents($this->viewPath, $viewContent);
        }

        $this->events()->trigger('Front', 'postDispatch');

        return $viewModel;
    }

    /**
     * Load tabs
     *
     * @param integer $documentTypeId Document type id
     *
     * @return Gc\Component\Tab\Collection
     */
    protected function loadTabs($documentTypeId)
    {
        $documentType = DocumentType\Model::fromId($documentTypeId);
        return $documentType->getTabs();
    }


    /**
     * Load properties
     *
     * @param integer $documentTypeId Document type id
     * @param integer $tabId          Tab id
     * @param integer $documentId     Document id
     *
     * @return \Gc\Component\Property\Collection
     */
    protected function loadProperties($documentTypeId, $tabId, $documentId)
    {
        $properties = new Property\Collection();
        $properties->load($documentTypeId, $tabId, $documentId);

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
        $explodePath = explode('/', $path);
        if (preg_match('/\/$/', $path)) {
            array_pop($explodePath);
        }

        return $explodePath;
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
        $cacheTtl     = (int) CoreConfig::getValue('cache_lifetime');
        $cacheHandler = CoreConfig::getValue('cache_handler');

        if (!in_array($cacheHandler, array('apc', 'memcached', 'filesystem'))) {
            $cacheHandler = 'filesystem';
        }

        switch($cacheHandler) {
            case 'memcached':
                $cacheOptions = array(
                    'ttl' => $cacheTtl,
                    'servers' => array(array(
                        'localhost', 11211
                    )),
                );
                break;
            case 'apc':
            default:
                $cacheOptions = array(
                    'ttl' => $cacheTtl,
                );
                break;
        }

        $this->cache = CacheStorage::factory(
            array(
                'adapter' => array(
                    'name' => $cacheHandler,
                    'options' => $cacheOptions,
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
