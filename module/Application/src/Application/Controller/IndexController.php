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
use Gc\Document;
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
    const VIEW_PATH = 'application/index/view-content';

    /**
     * View filename
     *
     * @var string
     */
    const LAYOUT_PATH = 'application/index/layout-content';

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
        $sessionId  = $this->getSession()->getDefaultManager()->getId();
        $isAdmin    = $this->getServiceLocator()->get('Auth')->hasIdentity();
        $isPreview  = ($isAdmin and $this->getRequest()->getQuery()->get('preview') === 'true');
        $coreConfig = $this->getServiceLocator()->get('CoreConfig');

        //Don't log preview
        if (!$isPreview and !$isAdmin) {
            try {
                $session->visitorId = $visitor->getVisitorId($sessionId);
            } catch (Exception $e) {
                //don't care
            }
        }

        $viewModel = new ViewModel();
        $this->events()->trigger('Front', 'preDispatch', null, array('object' => $this, 'viewModel' => $viewModel));

        if ($coreConfig->getValue('site_is_offline') == 1) {
            //Site is offline
            if (!$isAdmin) {
                $document = Document\Model::fromId($coreConfig->getValue('site_offline_document'));
                if (empty($document)) {
                    $viewModel->setTemplate('application/site-is-offline');
                    $viewModel->setTerminal(true);
                    return $viewModel;
                }
            }
        }

        $path = ltrim($this->getRouteMatch()->getParam('path'), '/');

        $cacheIsEnable = ($coreConfig->getValue('cache_is_active') == 1 and !$isPreview);
        if ($cacheIsEnable) {
            $this->enableCache();
            $cacheKey = ('page'
                . (empty($path) ? '' : '-'
                . $this->toCacheKey($path)));
            if ($this->cache->hasItem($cacheKey)) {
                //Retrieve cache value and set data
                $cacheValue = $this->cache->getItem($cacheKey);
                $viewModel  = $cacheValue['view_model'];
                $view       = $cacheValue['view'];
                $layout     = $cacheValue['layout'];
                $viewModel->setVariables($cacheValue['layout_variables']);
                $this->layout()->setVariables($cacheValue['layout_variables']);
            }
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
                                $documentTmp = Document\Model::fromUrlKey($urlKey, $homeDocument->getId());
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

                $variables['currentDocument'] = $document;
                $viewModel->setVariable('currentDocument', $document);
                $this->layout()->setVariable('currentDocument', $document);

                //Set view from database
                $view   = View\Model::fromId($document->getViewId());
                $layout = Layout\Model::fromId($document->getLayoutId());
            }

            if ($cacheIsEnable && !empty($document)) {
                $this->cache->setItem(
                    $cacheKey,
                    array(
                        'view_model'       => $viewModel,
                        'layout_variables' => $variables,
                        'layout'           => $layout,
                        'view'             => $view,
                    )
                );
            }
        }

        if ($coreConfig->getValue('stream_wrapper_is_active')) {
            View\Stream::register();
            if (!empty($layout)) {
                file_put_contents('zend.view://layout/' . $layout->getIdentifier(), $layout->getContent());
            }

            if (!empty($view)) {
                file_put_contents('zend.view://view/' . $view->getIdentifier(), $view->getContent());
            }
        }

        if (!empty($layout)) {
            $this->layout()->setTemplate('layout/' . $layout->getIdentifier());
        }

        if (!empty($view)) {
            $viewModel->setTemplate('view/' . $view->getIdentifier());
        }

        $this->events()->trigger('Front', 'postDispatch', null, array('object' => $this, 'viewModel' => $viewModel));

        return $viewModel;
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
        $coreConfig   = $this->getServiceLocator()->get('CoreConfig');
        $cacheTtl     = (int) $coreConfig->getValue('cache_lifetime');
        $cacheHandler = $coreConfig->getValue('cache_handler');

        if (!in_array($cacheHandler, array('apc', 'memcached', 'filesystem'))) {
            $cacheHandler = 'filesystem';
        }

        switch($cacheHandler) {
            case 'memcached':
                $cacheOptions = array(
                    'ttl'       => $cacheTtl,
                    'namespace' => $this->toCacheKey($coreConfig->getValue('site_name')),
                    'servers'   => array(array(
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

    /**
     * Convert string to cache key
     *
     * @param string $string String
     *
     * @return string
     */
    protected function toCacheKey($string)
    {
        return preg_replace('/[^a-z0-9_\+\-]+/Di', '_', str_replace('/', '-', strtolower($string)));
    }
}
