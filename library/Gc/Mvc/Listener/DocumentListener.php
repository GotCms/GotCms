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
 * @subpackage Mvc\Listener
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\Listener;

use Exception;
use Gc\Document;
use Gc\User\Visitor;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container as SessionContainer;
use Zend\Validator\AbstractValidator;

/**
 * Document Listener
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Listener
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
            array($this, 'onRoute'),
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
    public function onRoute(EventInterface $event)
    {
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();
        if ($matchedRouteName !== 'cms') {
            return;
        }

        $serviceManager = $event->getApplication()->getServiceManager();
        $isAdmin        = $serviceManager->get('Auth')->hasIdentity();
        $isPreview      = ($isAdmin and $event->getRequest()->getQuery()->get('preview') === 'true');
        $path           = ltrim($event->getRouteMatch()->getParam('path'), '/');
        if (empty($path)) {
            $document = Document\Model::fromUrlKey('');
        } else {
            $document = $this->findDocument($path, $isPreview);
        }

        $this->logVisitor($isPreview, $isAdmin);
        if (empty($document) or (!$document->isPublished() and !$isPreview)) {
            $serviceManager->setService(
                'CurrentDocument',
                false
            );
        } else {
            $translator = $serviceManager->get('MvcTranslator');
            $translator->setLocale($this->getLocale($document));
            AbstractValidator::setDefaultTranslator($translator);

            $serviceManager->setService(
                'CurrentDocument',
                $document
            );
        }
    }

    /**
     * Find document from request uri
     *
     * @param string  $path      Path from request uri
     * @param boolean $isPreview Is the current page is a preview
     *
     * @return mixed
     */
    protected function findDocument($path, $isPreview)
    {
        $explodePath = $this->explodePath($path);
        $documentTmp = null;
        $children    = null;
        $hasDocument = false;
        $parentId    = null;

        foreach ($explodePath as $urlKey) {
            if ($hasDocument === false) {
                $documentTmp = Document\Model::fromUrlKey($urlKey, $parentId);
                if (empty($documentTmp) and
                    $parentId === null and
                    ($homeDocument = Document\Model::fromUrlKey('')) !== false
                ) {
                    $documentTmp = Document\Model::fromUrlKey($urlKey, $homeDocument->getId());
                }
            }

            if (is_array($children) and !in_array($documentTmp, $children)) {
                $hasDocument = true;
            } else {
                if (!empty($documentTmp) and ($documentTmp->isPublished() or $isPreview)) {
                    $parentId = $documentTmp->getId();
                    $children = $documentTmp->getChildren();
                }
            }
        }

        return $documentTmp;
    }

    /**
     * Log visitor informations
     *
     * @param boolean $isPreview Is the current page is a preview
     * @param boolean $isAdmin   Is an admin is connected
     *
     * @return void
     */
    protected function logVisitor($isPreview, $isAdmin)
    {
        if (!$isPreview and !$isAdmin) {
            try {
                $visitor   = new Visitor();
                $session   = new SessionContainer();
                $sessionId = $session->getDefaultManager()->getId();

                $session->visitorId = $visitor->getVisitorId($sessionId);
            } catch (Exception $e) {
                //don't care
            }
        }
    }

    /**
     * Get locale according to the locale specified in the document or its parent.
     *
     * @param Document\Model $document Document
     *
     * @return void
     */
    protected function getLocale($document)
    {
        $locale = null;
        if (!$document->hasLocale()) {
            $parent = $document->getParent();
            if ($parent) {
                $locale = $this->getLocale($parent);
            }
        } else {
            $locale = $document->getLocale();
        }

        return $locale;
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
