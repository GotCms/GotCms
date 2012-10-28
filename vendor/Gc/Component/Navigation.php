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
 * @category    Gc
 * @package     Library
 * @subpackage  Component
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Component;

use Gc\Document,
    Gc\Registry;

/**
 * Create Xml for \Zend\Navigation
 */
class Navigation
{
    /**
     * List of \Gc\Document\Model
     * @var array
     */
    protected $_documents;

    /**
     * Base path for urls
     * @var string
     */
    protected $_basePath = '/';

    /**
     * Request uri
     * @var string
     */
     protected $_requestUri;

    /**
     * Constructor, initialize documents
     * @return void
     */
    public function __construct()
    {
        $documents = new Document\Collection();
        $documents->load(0);
        $this->_documents = $documents->getDocuments();
        $this->_requestUri = Registry::get('Application')->getRequest()->getRequestUri();
    }

    /**
     * Set base path for urls
     * @param string $path
     * @return \Gc\Component\Navigation
     */
    public function setBasePath($path)
    {
        $this->_basePath = $path;
        return $this;
    }

    /**
     * Get base Path
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * Render navigation
     * @param array $documents (set of \Gc\Document\Model)
     * @param string $parent_url
     * @return array
     */
    public function render(array $documents = NULL, $parent_url = NULL)
    {
        $navigation = array();
        $hasFooter = FALSE;
        if($documents === NULL && !empty($this->_documents))
        {
            $documents = $this->_documents;
        }

        foreach($documents as $document)
        {
            $children = $document->getChildren();
            if($document->isPublished())
            {
                $data = array();
                $data['label'] = $document->getName();
                $data['uri'] = $this->getBasePath() . ($parent_url !== NULL ? $parent_url . '/' : '') . $document->getUrlKey();
                $data['visible'] = $document->showInNav();
                $data['active'] = $data['uri'] == $this->_requestUri;

                if(!empty($children) && is_array($children))
                {
                    $data['pages'] = $this->render($children, (empty($parent_url) ? NULL : $parent_url . '/') . $document->getUrlKey());
                }

                $navigation['document-' . $document->getId()] = $data;
            }
        }

        return $navigation;
    }
}
