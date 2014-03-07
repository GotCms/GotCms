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
 * @category   Gc
 * @package    Library
 * @subpackage Listener
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Listener;

use Gc\Document;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

/**
 * Document Listener
 *
 * @category   Gc
 * @package    Library
 * @subpackage Listener
 */

class DocumentListener extends AbstractListenerAggregate
{
    /**
     * Attach events
     *
     * @param EventManagerInterface $events Event manager
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_ROUTE,
            array($this, 'check'),
            -10
        );
    }

    /**
     * Check for document from route
     *
     * @param EventInterface $event Mvc Event
     *
     * @return void
     */
    public function check(EventInterface $event)
    {
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();
        if ($matchedRouteName === 'cms') {
            $isAdmin   = $event->getApplication()->getServiceManager()->get('Auth')->hasIdentity();
            $isPreview = ($isAdmin and $event->getRequest()->getQuery()->get('preview') === 'true');
            $path      = ltrim($event->getRouteMatch()->getParam('path'), '/');
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

            $event->getApplication()->getServiceManager()->setService('CurrentDocument', $document);
        }
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
}
