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
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Sitemap
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Sitemap;

use Gc\Module\AbstractObserver;
use Gc\Registry;
use Modules\Sitemap\Model\Sitemap;
use Zend\EventManager\Event;

/**
 * Sitemap module bootstrap
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Sitemap
 */
class Observer extends AbstractObserver
{
    /**
     * Boostrap
     *
     * @return void
     */
    public function init()
    {
        $this->events()->attach('Gc\Document\Model', 'afterSave', array($this, 'addElement'));
        $this->events()->attach('Gc\Document\Model', 'afterDelete', array($this, 'removeElement'));
    }

    /**
     * Generate xml on save
     *
     * @param \Zend\EventManager\Event $event Event
     *
     * @return void
     */
    public function addElement(Event $event)
    {
        $sitemap = new Sitemap();
        if (file_exists($sitemap->getFilePath())) {
            $document = $event->getParam('object');
            if ($document->hasDataChangedFor('url_key')) {
                $oldUrlKey = $document->getUrlKey();
                $document->setUrlKey($document->getOrigData('url_key'));
                $content = file_get_contents($sitemap->getFilePath());
                $xml     = simplexml_load_string($content);
                $xml->registerXPathNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');
                $obj = $xml->xpath(
                    sprintf(
                        '//sm:url[sm:loc="%s%s"]',
                        Registry::get('Application')->getRequest()->getBasePath(),
                        $document->getUrl()
                    )
                );
                if (!empty($obj)) {
                    $obj[0]->loc     = $document->getUrl();
                    $obj[0]->lastmod = $document->getUrl();
                    $xml->asXml($sitemap->getFilePath());
                }

                $document->setUrlKey($oldUrlKey);
            }
        } else {
            file_put_contents($sitemap->getFilePath(), $sitemap->generate());
        }
    }

    /**
     * Remove element on delete
     *
     * @param \Zend\EventManager\Event $event Event
     *
     * @return void
     */
    public function removeElement(Event $event)
    {
        $sitemap = new Sitemap();
        if (file_exists($sitemap->getFilePath())) {
            $document  = $event->getParam('object');
            $oldUrlKey = $document->getUrlKey();
            $document->setUrlKey($document->getOrigData('url_key'));
            $content = file_get_contents($sitemap->getFilePath());
            $xml     = simplexml_load_string($content);
            $xml->registerXPathNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $obj = $xml->xpath(
                sprintf(
                    '//sm:url[sm:loc="%s%s"]',
                    Registry::get('Application')->getRequest()->getBasePath(),
                    $document->getUrl()
                )
            );
            if (!empty($obj)) {
                unset($obj);
                $xml->asXml($sitemap->getFilePath());
            }

            $document->setUrlKey($oldUrlKey);
        }
    }
}
