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
 * @subpackage Blog\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Blog\Model;

use Gc\Db\AbstractTable;
use Gc\Document\Model as DocumentModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Blog comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Model
 */
class Comment extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'blog_comment';

    /**
     * Return all documents with comment(s)
     *
     * @return array
     */
    public function getDocumentList()
    {
        $allComments = $this->getList(null, null);
        $documents   = array();
        foreach ($allComments as $key => $comment) {
            if (empty($documents[$comment['document_id']])) {
                $document                      = DocumentModel::fromId($comment['document_id']);
                $documents[$document->getId()] = $document;
            }
        }

        return $documents;
    }

    /**
     * Return all comments in document
     *
     * @param integer        $documentId Document id
     * @param null|boolean   $isActive   Is active
     *
     * @return array
     */
    public function getList($documentId = null, $isActive = true)
    {
        $driverName = $this->getDriverName();
        return $this->select(
            function (Select $select) use ($documentId, $isActive, $driverName) {
                if (!empty($documentId)) {
                    $select->where->equalTo('document_id', $documentId);
                }

                if (!is_null($isActive)) {
                    if ($driverName == 'pdo_pgsql') {
                        $select->where->equalTo('is_active', empty($isActive) ? 'false' : 'true');
                    } else {
                        $select->where->equalTo('is_active', (int) $isActive);
                    }
                }

                $select->order('created_at ASC');
            }
        )->toArray();
    }

    /**
     * Add command
     *
     * @param array   $data       Array of comments
     * @param integer $documentId Document id
     *
     * @return boolean
     */
    public function add(array $data, $documentId)
    {
        $mandatoryKeys = array('message', 'username', 'email');
        $insertData    = array();
        foreach ($mandatoryKeys as $key) {
            if (empty($data[$key])) {
                return false;
            } else {
                $insertData[$key] = $data[$key];
            }
        }

        $insertData['show_email']  = empty($data['show_email']) ? 0 : 1;
        $insertData['document_id'] = $documentId;
        $insertData['created_at']  = new Expression('NOW()');
        $this->insert($insertData);

        return true;
    }
}
