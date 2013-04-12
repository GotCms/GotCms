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
 * @subpackage Blog
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Blog;

use Gc\Module\AbstractObserver;
use Modules\Blog\Model\Comment;
use Zend\EventManager\Event;

/**
 * Blog module bootstrap
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog
 */
class Observer extends AbstractObserver
{
    /**
     * Boostrap
     *
     * @return void
     */
    public function init()
    {
        $this->events()->attach('Admin\Controller\IndexController', 'dashboard', array($this, 'dashboard'));
    }

    /**
     * Display widget dashboard
     *
     * @param \Zend\EventManager\Event $event Event
     *
     * @return void
     */
    public function dashboard(Event $event)
    {
        $commentModel        = new Comment();
        $unactiveCommentList = $commentModel->getList(null, false);
        $activeCommentList   = $commentModel->getList(null, true);

        $widgets = $event->getParam('widgets');

        $widgets['test']['id']      = 'blog';
        $widgets['test']['title']   = 'Blog information';
        $widgets['test']['content'] = $this->addPath(__DIR__ . '/views')->render(
            'dashboard.phtml',
            array(
                'unactiveComments' => count($unactiveCommentList),
                'activeComments'   => count($activeCommentList),
            )
        );

        $event->setParam('widgets', $widgets);
    }
}
