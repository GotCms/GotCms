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
 * @subpackage Blog\Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Blog\Controller;

use Gc\Module\Controller\AbstractController;
use Gc\Document\Model as DocumentModel;
use Modules\Blog\Model;

/**
 * IndexController
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Controller
 */
class IndexController extends AbstractController
{
    /**
     * Fields white list
     *
     * @var array
     */
    protected $whiteList = array(
        'show_email',
        'is_active',
        'username',
        'email',
        'message',
        'document_id',
        'created_at'
    );

    /**
     * Index action, list all documents with comments
     *
     * @return array
     */
    public function indexAction()
    {
        $model = new Model\Comment();
        $document_list = $model->getDocumentList();

        return array('document_list' => $document_list);
    }

    /**
     * List all comment by document id
     *
     * @return array
     */
    public function documentCommentAction()
    {
        $document_id = $this->getRequest()->getQuery()->get('id');
        $document = DocumentModel::fromId($document_id);
        if (empty($document)) {
            return $this->redirect()->toRoute('moduleEdit', array('mc' => 'index', 'ma' => 'index'), array(), true);
        }

        $model = new Model\Comment();
        $comment_list = $model->getList($document_id, null);

        if ($this->getRequest()->isPost()) {
            $comments = $this->getRequest()->getPost()->get('comment');

            foreach ($comments as $comment_id => $data) {
                if (!empty($data['delete'])) {
                    $model->delete(array('id' => $comment_id));
                    continue;
                }

                foreach ($data as $k => $v) {
                    if (!in_array($k, $this->whiteList)) {
                        unset($data[$k]);
                    }
                }

                $data['show_email'] = empty($data['show_email']) ? 0 : 1;
                $data['is_active'] = empty($data['is_active']) ? 0 : 1;
                $model->update($data, array('id' => $comment_id));
            }

            return $this->redirect()->toRoute(
                'moduleEdit',
                array(
                    'mc' => 'index',
                    'ma' => 'document-comment'
                ),
                array(),
                true
            );
        }

        return array('comment_list' => $comment_list, 'document' => $document);
    }
}
