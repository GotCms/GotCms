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
 * @subpackage  View\Helper
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Zend\View\Helper\AbstractHelper,
    Gc\Document;

class Documents extends AbstractHelper
{
    /**
     * Returns documents
     * $data can be mixte
     * if data is an array, return all documents
     * if data is numeric, return all documents
     * with parent_id equal to $data
     *
     * @param  integer $data
     * @return array of \Gc\Document\Collection
     */
    public function __invoke($data = NULL)
    {
        $documents = new Document\Collection();
        if(empty($data))
        {
            $elements = $documents->load(0);
        }
        else
        {
            if(is_numeric($data))
            {
                $elements = $documents->load($data);
            }
            elseif(is_array($data))
            {
                $elements = array();
                foreach($data as $document_id)
                {
                    if(empty($document_id) or !is_numeric($document_id))
                    {
                        continue;
                    }

                    $document = Document\Model::fromId($document_id);
                    if(!empty($document))
                    {
                        $elements[] = $document;
                    }
                }
            }
        }

        return $elements;
    }
}
