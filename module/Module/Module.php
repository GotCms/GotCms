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
 * @category   Gc_Application
 * @package    Module
 * @subpackage Module
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */
namespace Module;

use Gc\Mvc;
use Zend\EventManager\EventInterface;
use Zend\Filter;
use Zend\Mvc\MvcEvent;

/**
 * Module Statistics.
 *
 * @category   Gc_Application
 * @package    Module
 * @subpackage Module
 */
class Module extends Mvc\Module
{
    /**
     * Module directory path
     *
     * @var string
     */
    protected $directory = __DIR__;

    /**
     * Module namespace
     *
     * @var string
     */
    protected $namespace = __NAMESPACE__;

    /**
     * On boostrap event
     *
     * @param EventInterface $event Event
     *
     * @return void
     */
    public function onBootstrap(EventInterface $event)
    {
        $application = $event->getApplication();
        $application->getEventManager()->attach(
            MvcEvent::EVENT_DISPATCH,
            array($this, 'loadMenu'),
            -10
        );
    }

    /**
     * Load menu if module has view with name "menu.phtml"
     *
     * @param EventInterface $event Event
     *
     * @return void
     */
    public function loadMenu(EventInterface $event)
    {
        if ($route = $event->getRouter()->getRoute('module')->match($event->getRequest())) {
            if ($route->getParam('module') === 'module') {
                return;
            }

            $filter = new Filter\Word\CamelCaseToSeparator;
            $filter->setSeparator('-');
            $filterChain = new Filter\FilterChain();
            $filterChain->attach($filter)
                ->attach(new Filter\StringToLower());
            $template = $filterChain->filter($route->getParam('module')) . '/menu';
            $target   = $event->getTarget();
            $resolver = $event
                ->getApplication()
                ->getServiceManager()
                ->get('Zend\View\Resolver\TemplatePathStack');

            $navigation = $target->getServiceLocator()->get('navigation');
            $navigation->findByRoute('module')->addPage(
                array(
                    'label' => $route->getParam('module'),
                    'route' => $event->getRouteMatch()->getMatchedRouteName(),
                    'active' => true,
                )
            );

            if (false !== $resolver->resolve($template)) {
                $target->layout()->setVariable('moduleMenu', $template);
            }
        }
    }
}
