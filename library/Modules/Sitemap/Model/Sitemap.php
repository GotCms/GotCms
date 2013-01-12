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
 * @subpackage Sitemap\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Sitemap\Model;

use Gc\Core\Object,
    Gc\Component\IterableInterface,
    Gc\Document\Collection as DocumentCollection,
    Gc\Document\Model as DocumentModel,
    Zend\Db\Sql\Select,
    Zend\Db\Sql\Predicate\Expression;

/**
 * Sitemap comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Sitemap\Model
 */
class Sitemap extends Object
{
    /**
     * Generate Xml accessor
     *
     * @return string
     */
    public function generate()
    {
        $collection = new DocumentCollection();
        $documents = array();
        $rows = $collection->getAvailableDocuments();
        foreach($rows as $row)
        {
            $documents[] = DocumentModel::fromArray((array)$row);
        }

        return $this->_generateXml($documents);
    }

    /**
     * Generate Xml
     *
     * @param array $documents Array with all documents
     * @return string
     */
    protected function _generateXml($documents)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

        $url = 'http';
        if(!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on')
        {
            $url .= 's';
        }

        $url .= '://';
        if($_SERVER['SERVER_PORT'] != '80')
        {
            $url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
        }
        else
        {
            $url .= $_SERVER['SERVER_NAME'];
        }

        foreach($documents as $document)
        {
            if(!$document instanceof IterableInterface)
            {
                continue;
            }

            $xml .= '<url>';
            $xml .= '<loc>' . $url . $document->getUrl() . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($document->getUpdatedAt())) . '</lastmod>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
