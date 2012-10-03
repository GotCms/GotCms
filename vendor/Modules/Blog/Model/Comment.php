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
 * @category Modules
 * @package  Blog\Model
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Modules\Blog\Model;

use Gc\Db\AbstractTable,
    Gc\Document\Model as DocumentModel,
    Zend\Db\Sql\Select;

class Comment extends AbstractTable
{
    protected $_name ='blog_comment';

    public function getDocumentList()
    {
        $all_comments = $this->getList();
        $documents = array();
        foreach($all_comments as $key => $comment)
        {
            if(empty($documents[$comment['document_id']]))
            {
                $document = DocumentModel::fromId($comment['document_id']);
                $documents[$document->getId()] = $document;
            }
        }

        return $documents;
    }

    public function getList($document_id = NULL)
    {
        return $this->select(function(Select $select) use ($document_id)
        {
            if(!empty($document_id))
            {
                $select->where->equalTo('document_id', $document_id);
            }
        });
    }

    public function get($id)
    {
        return $this->select(function(Select $select) use ($id)
        {
            $select->where->equalTo('id', $id);
            $select->join(array('document'), $on);
        });
    }
}
