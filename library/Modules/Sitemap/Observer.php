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
use SimpleXMLElement;

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
        $document = $event->getParam('object');
        if (!$document->isPublished()) {
            $this->removeElement($event);
            return;
        }

        $sitemap = new Sitemap();
        $xml     = $this->getXml($sitemap);
        $request = $this->serviceManager->get('Request');
        if ($xml !== null) {
            $oldUrlKey = $document->getUrlKey();
            $document->setUrlKey($document->getOrigData('url_key'));
            $obj = $this->getDoc($xml, $request->getBasePath(), $document->getUrl());
            $document->setUrlKey($oldUrlKey);
            $lastmod  = date('Y-m-d\TH:i:s\Z', strtotime($document->getUpdatedAt()));
            $location = '<![CDATA[' . $request->getBasePath() . $document->getUrl() . ']]>';
            if (!empty($obj)) {
                $obj[0]->loc     = $location;
                $obj[0]->lastmod = $lastmod;
            } else {
                $url        = $xml->addChild('url');
                $loc        = $url->addChild('loc', $location);
                $lastmod    = $url->addChild('lastmod', $lastmod);
                $changefreq = $url->addChild('changefreq', 'weekly');
                $priority   = $url->addChild('priority', '0.5');
            }

            $xml->asXml($sitemap->getFilePath());
        } else {
            file_put_contents($sitemap->getFilePath(), $sitemap->generate($request));
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
        $xml     = $this->getXml($sitemap);
        if ($xml !== null) {
            $request   = $this->serviceManager->get('Request');
            $document  = $event->getParam('object');
            $oldUrlKey = $document->getUrlKey();
            $document->setUrlKey($document->getOrigData('url_key'));
            $obj = $this->getDoc($xml, $request->getBasePath(), $document->getUrl());

            if (!empty($obj)) {
                unset($obj);
                $xml->asXml($sitemap->getFilePath());
            }

            $document->setUrlKey($oldUrlKey);
        }
    }

    /**
     * Get xml
     *
     * @param Sitemap $sitemap Sitemap model
     *
     * @return void|false|SimpleXMLElement
     */
    protected function getXml(Sitemap $sitemap)
    {
        if (file_exists($sitemap->getFilePath())) {
            $content = file_get_contents($sitemap->getFilePath());
            $xml     = simplexml_load_string($content);
            $xml->registerXPathNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');

            return $xml;
        }
    }

    /**
     * Get document
     *
     * @param SimpleXMLElement $xml      Xml class
     * @param string           $basePath Base path
     * @param string           $url      Url
     *
     * @return false|array
     */
    protected function getDoc(SimpleXMLElement $xml, $basePath, $url)
    {
        return $xml->xpath(
            sprintf(
                '//sm:url[sm:loc="%s%s"]',
                $basePath,
                $url
            )
        );
    }
}
