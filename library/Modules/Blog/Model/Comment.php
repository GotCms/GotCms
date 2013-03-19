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
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Blog\Model;

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
        $all_comments = $this->getList(null, null);
        $documents    = array();
        foreach ($all_comments as $key => $comment) {
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
     * @param integer $document_id Document id
     * @param boolean $is_active   Is active
     *
     * @return array
     */
    public function getList($document_id = null, $is_active = true)
    {
        return $this->select(
            function (Select $select) use ($document_id, $is_active) {
                if (!empty($document_id)) {
                    $select->where->equalTo('document_id', $document_id);
                }

                if (!is_null($is_active)) {
                    if ($this->getDriverName() == 'pdo_pgsql') {
                        $select->where->equalTo('is_active', empty($is_active) ? 'false' : 'true');
                    } else {
                        $select->where->equalTo('is_active', (int) $is_active);
                    }
                }

                $select->order('created_at ASC');
            }
        )->toArray();
    }

    /**
     * Add command
     *
     * @param array   $data        Array of comments
     * @param integer $document_id Document id
     *
     * @return boolean
     */
    public function add(array $data, $document_id)
    {
        $mandatory_keys = array('message', 'username', 'email');
        $insert_data    = array();
        foreach ($mandatory_keys as $key) {
            if (empty($data[$key])) {
                return false;
            } else {
                $insert_data[$key] = $data[$key];
            }
        }

        $insert_data['show_email']  = empty($data['show_email']) ? 0 : 1;
        $insert_data['document_id'] = $document_id;
        $insert_data['created_at']  = new Expression('NOW()');
        $this->insert($insert_data);

        return true;
    }
}
