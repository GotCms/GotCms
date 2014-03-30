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
 * @subpackage Component
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Component;

use Gc\Document;
use Gc\Registry;

/**
 * Create Xml for \Zend\Navigation
 *
 * @category   Gc
 * @package    Library
 * @subpackage Component
 */
class Navigation
{
    /**
     * List of \Gc\Document\Model
     *
     * @var array
     */
    protected $documents;

    /**
     * Base path for urls
     *
     * @var string
     */
    protected $basePath = '/';

    /**
     * Request uri
     *
     * @var string
     */
    protected $requestUri;

    /**
     * Use active branch
     *
     * @var boolean
     */
    protected $useActiveBranch;

    /**
     * Constructor, initialize documents
     *
     * @param integer $documentId   Document id
     * @param boolean $activeBranch Use active branch or not
     *
     * @return void
     */
    public function __construct($documentId = 0, $activeBranch = false)
    {
        $documents = new Document\Collection();
        $documents->load($documentId);
        $this->documents       = $documents->getDocuments();
        $this->requestUri      = Registry::get('Application')->getRequest()->getUri()->getPath();
        $this->useActiveBranch = (bool) $activeBranch;
    }


    /**
     * Constructor, initialize documents
     *
     * @param boolean $boolean Set the branch is active or only one page
     *
     * @return mixte
     */
    public function useActiveBranch($boolean = null)
    {
        if ($boolean === null) {
            return $this->useActiveBranch;
        }

        $this->useActiveBranch = (bool) $boolean;
        return $this;
    }

    /**
     * Set base path for urls
     *
     * @param string $path Path
     *
     * @return \Gc\Component\Navigation
     */
    public function setBasePath($path)
    {
        $this->basePath = $path;
        return $this;
    }

    /**
     * Get base Path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Render navigation
     *
     * @param array  $documents Let of \Gc\Document\Model
     * @param string $parentUrl Parent url
     *
     * @return array
     */
    public function render(array $documents = null, $parentUrl = null)
    {
        $navigation = array();
        $documents  = $this->checkDocuments($documents);

        foreach ($documents as $document) {
            if ($document->isPublished()) {
                $data            = array();
                $data['label']   = $document->getName();
                $data['uri']     = $this->getBasePath()
                    . ($parentUrl !== null ? ltrim($parentUrl, '/') . '/' : '')
                    . $document->getUrlKey();
                $data['visible'] = $document->showInNav();
                $data['active']  = $data['uri'] == $this->requestUri;

                $this->renderChildren($document, $parentUrl, $data);

                $navigation['document-' . $document->getId()] = $data;
            }
        }

        return $navigation;
    }

    /**
     * Render children
     *
     * @param Document\Model $document  Document model
     * @param string         $parentUrl Parent url
     * @param array          &$data     Array
     *
     * @return void
     */
    protected function renderChildren($document, $parentUrl, &$data)
    {
        $children = $document->getChildren();
        if (!empty($children) && is_array($children)) {
            $data['pages'] = $this->render(
                $children,
                (empty($parentUrl) ? null : $parentUrl . '/') . $document->getUrlKey()
            );

            if ($this->useActiveBranch()) {
                $data['active'] = ($data['active'] or $this->hasActiveChildren($data['pages']));
            }
        }
    }

    /**
     * Check documents
     *
     * @param mixed $documents List of Document\Model
     *
     * @return array
     */
    protected function checkDocuments($documents)
    {
        if ($documents === null && !is_null($this->documents)) {
            $documents = $this->documents;
        }

        if (!is_array($documents)) {
            return array();
        }

        return $documents;
    }
    /**
     * Check if page has active children
     *
     * @param array $pages List of pages as array
     *
     * @return boolean
     */
    protected function hasActiveChildren($pages)
    {
        foreach ($pages as $page) {
            if (!empty($page['active'])) {
                return true;
            }

            if (!empty($page['pages'])) {
                if ($this->hasActiveChildren($page['pages'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
