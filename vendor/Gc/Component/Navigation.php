<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
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

use Gc\Document;

class Navigation
{
    const XML_NAV_HEADER = '<?xml version="1.0" encoding="UTF-8"?><configdata><nav>';
    const XML_NAV_FOOTER = '</nav></configdata>';

    /**
     * @var array of \Gc\Document\Model
     */
    protected $_documents;

    /**
     * @var string base path for urls
     */
    protected $_basePath;

    /**
     * Constructor, initialize document
     *
     * @return void
     */
    public function __construct()
    {
        $documents = new Document\Collection();
        $documents->load(0);
        $this->_documents = $documents->getDocuments();
    }

    /**
     * Set base path for urls
     *
     * @return Gc\Component\Navigation
     */
    public function setBasePath($path)
    {
        $this->_basePath = $path;
        return $this;
    }

    /**
     * Get base Path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * Render navigation
     * @param array $documents (set of \Gc\Document\Model)
     * @return xml
     */
    public function render(Array $documents = NULL, $parent_url = NULL)
    {
        $navigation = '';
        $hasFooter = FALSE;
        if($documents === NULL && !empty($this->_documents))
        {
            $navigation .= self::XML_NAV_HEADER;
            $documents = $this->_documents;
            $hasFooter = TRUE;
        }

        foreach($documents as $document)
        {
            $children = $document->getChildren();
            if($document->showInNav() && $document->isPublished())
            {
                $navigation .= '<document-'.$document->getUrlKey().'>';
                $navigation .= '<label>'.$document->getName().'</label>';
                $navigation .= '<uri>' . $this->getBasePath() . ($parent_url !== NULL ? $parent_url : '').'/'.$document->getUrlKey().'</uri>';
                if(!empty($children) && is_array($children))
                {
                    $navigation .= '<pages>';
                    $navigation .= $this->render($children, $document->getUrlKey());
                    $navigation .= '</pages>';
                }

                $navigation .= '</document-'.$document->getUrlKey().'>';
            }
            else
            {
                if(!empty($children) && is_array($children))
                {
                    $navigation .= $this->render($children);
                }
            }
        }

        if($hasFooter === TRUE)
        {
            $navigation .= self::XML_NAV_FOOTER;
        }

        return $navigation;
    }

    /**
     * To string method
     * @return xml
     */
    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch(Exception $e)
        {
            trigger_error($e->getMessage(), E_USER_ERROR);
            return '';
        }
    }
}
