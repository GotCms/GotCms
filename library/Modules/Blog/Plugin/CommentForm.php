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

namespace Modules\Blog\Plugin;

use Gc\Module\AbstractPlugin;
use Modules\Blog;

/**
 * Blog comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Model
 */
class CommentForm extends AbstractPlugin
{
    /**
     * Form
     *
     * @var \Modules\Blog\Form\Comment
     */
    protected $form;

    /**
     * Invoke form
     *
     * @return void
     */
    public function __invoke()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $this->getForm()->setData($post);
            if ($this->getForm()->isValid()) {
                $comment_table = new Blog\Model\Comment();
                if ($comment_table->add(
                    $this->getForm()->getInputFilter()->getValues(),
                    $this->layout()->currentDocument->getId()
                )
                ) {
                    $this->flashMessenger()->addSuccessMessage('Message sent');
                    return $this->redirect()->toUrl($request->getRequestUri());
                }
            }
        }

        return $this->addPath(__DIR__ . '/../views')->render(
            'plugin/comment-form.phtml',
            array(
                'form' => $this->getForm(),
                'errorMessage' => 'Error',
            )
        );
    }

    /**
     * Set form
     *
     * @return \Modules\Blog\Plugin\Form
     */
    public function getForm()
    {
        if ($this->form === null) {
            $this->form = new Blog\Form\Comment();
        }

        return $this->form;
    }
}
