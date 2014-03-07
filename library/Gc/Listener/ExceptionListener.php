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
 * @category   Gc
 * @package    Library
 * @subpackage Listener
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Listener;

use Gc\Layout;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

/**
 * Exception Listener
 *
 * @category   Gc
 * @package    Library
 * @subpackage Listener
 */
class ExceptionListener extends AbstractListenerAggregate
{
    /**
     * Attach events
     *
     * @param EventManagerInterface $events Event manager
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            array($this, 'prepare'),
            10
        );
    }

    /**
     * Initialize Render error event
     *
     * @param Event $event Event
     *
     * @return void
     */
    public function prepare($event)
    {
        if ($event->getApplication()->getMvcEvent()->getRouteMatch()->getMatchedRouteName() === 'cms') {
            $coreConfig = $event->getApplication()->getServiceManager()->get('CoreConfig');
            $layout     = Layout\Model::fromId($coreConfig->getValue('site_exception_layout'));
            if (!empty($layout)) {
                $templatePathStack = $event->getApplication()->getServiceManager()->get(
                    'Zend\View\Resolver\TemplatePathStack'
                );

                $event->getViewModel()->setTemplate('layout/' . $layout->getIdentifier());
                if ($coreConfig->getValue('stream_wrapper_is_active')) {
                    $templatePathStack->setUseStreamWrapper(true);
                    file_put_contents('zend.view://layout/' . $layout->getIdentifier(), $layout->getContent());
                }
            }
        }
    }
}
