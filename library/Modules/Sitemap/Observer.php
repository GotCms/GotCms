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

use Gc\Module\AbstractObserver,
    Modules\Sitemap\Model\Sitemap,
    Zend\EventManager\Event;
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
        $this->events()->attach('Gc\Document\Model', 'afterSave', array($this, 'generateSitemap'));
    }

    /**
     * Display widget dashboard
     *
     * @param \Zend\EventManager\Event $event
     * @return void
     */
    public function generateSitemap(Event $event)
    {
        $sitemap_path = GC_MEDIA_PATH . '/sitemap.xml';
        if(file_exists($sitemap_path))
        {
            $document = $event->getParam('object');
            $old_url_key = $document->getUrlKey();
            $document->setUrlKey($document->getOrigData('url_key'));
            $content = file_get_contents($sitemap_path);
            $xml = simplexml_load_string($content);
            $xml->registerXPathNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $obj = $xml->xpath(sprintf('//sm:url[sm:loc="%s"]', $document->getUrl()));
            if(!empty($obj))
            {
                $obj[0]->loc = $document->getUrl();
                $obj[0]->lastmod = $document->getUrl();
                $xml->asXml($sitemap_path);
            }

            $document->setUrlKey($old_url_key);
        }
        else
        {
            $sitemap = new Sitemap();
            file_put_contents($sitemap_path, $sitemap->generate());
        }
    }
}
