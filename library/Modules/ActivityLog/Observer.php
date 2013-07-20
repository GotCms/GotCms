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
 * @subpackage ActivityLog
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\ActivityLog;

use Gc\Module\AbstractObserver;
use Modules\ActivityLog\Model\Template;
use Zend\EventManager\Event;

/**
 * Activity log module bootstrap
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage ActivityLog
 */
class Observer extends AbstractObserver
{
    /**
     * Template model
     *
     * @var Template
     */
    protected $template;

    /**
     * Boostrap
     *
     * @return void
     */
    public function init()
    {
        $this->template = new Template();
        $templates      = $this->template->getTemplates();

        foreach ($templates as $template) {
            $this->events()->attach(
                $template['event_identifier'],
                $template['event_name'],
                function (Event $event) use ($template) {
                    $userId = null;
                    $auth   = $this->getServiceManager()->get('Auth');
                    if ($auth->hasIdentity()) {
                        $userId = $auth->getIdentity()->getId();
                        $event->setParam('user', $auth->getIdentity());
                    }

                    $content = $this->template->render($event, $template['template']);
                    $this->template->addEvent($content, $template['id'], $userId);
                }
            );
        }
    }
}
