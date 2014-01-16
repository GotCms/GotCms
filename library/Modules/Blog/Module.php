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

namespace Blog;

use Blog\Model\Comment;
use Gc\Module\AbstractModule;
use Zend\EventManager\EventInterface as Event;

/**
 * Blog module bootstrap
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog
 */
class Module extends AbstractModule
{
    /**
     * Get module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Boostrap
     *
     * @param Event $e Event
     *
     * @return void
     */
    public function onBootstrap(Event $e)
    {
        $this->events()->attach('Admin\Controller\IndexController', 'dashboard', array($this, 'dashboard'));
    }

    /**
     * Display widget dashboard
     *
     * @param \Zend\EventManager\EventInterface $event Event
     *
     * @return void
     */
    public function dashboard(Event $event)
    {
        $commentModel        = new Comment();
        $unactiveCommentList = $commentModel->getList(null, false);
        $activeCommentList   = $commentModel->getList(null, true);

        $widgets = $event->getParam('widgets');

        $widgets['blog']['id']      = 'blog';
        $widgets['blog']['title']   = 'Blog information';
        $widgets['blog']['content'] = $this->addPath(__DIR__ . '/views')->render(
            'dashboard.phtml',
            array(
                'unactiveComments' => count($unactiveCommentList),
                'activeComments'   => count($activeCommentList),
            )
        );

        $event->setParam('widgets', $widgets);
    }

    /**
     * Install module
     *
     * @return boolean
     */
    public function install()
    {
        $pdoResource = $this->getAdapter()->getDriver()->getConnection()->getResource();
        $pdoResource->exec(
            file_get_contents(
                __DIR__ . sprintf('/data/sql/install-%s.sql', str_replace('pdo_', '', $this->getDriverName()))
            )
        );

        return true;
    }

    /**
     * Uninstall module
     *
     * @return boolean
     */
    public function uninstall()
    {
        $pdoResource = $this->getAdapter()->getDriver()->getConnection()->getResource();
        $pdoResource->exec(
            file_get_contents(
                __DIR__ . sprintf('/data/sql/uninstall-%s.sql', str_replace('pdo_', '', $this->getDriverName()))
            )
        );

        return true;
    }
}
