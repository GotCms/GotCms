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
 * @subpackage Mvc\View
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\View;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\JsonModel;

/**
 * Create json model listener to force all controller to return json
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\View
 */
class CreateJsonModelListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'createJsonModelFromArray'),  -80);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'createJsonModelFromNull'),   -80);
    }

    /**
     * Inspect the result, and cast it to a JsonModel if an assoc array is detected
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function createJsonModelFromArray(MvcEvent $e)
    {
        $model = new JsonModel($e->getResult());
        $e->setResult($model);
    }

    /**
     * Inspect the result, and cast it to a JsonModel if null is detected
     *
     * @param MvcEvent $e
     * @return void
    */
    public function createJsonModelFromNull(MvcEvent $e)
    {
        $result = $e->getResult();
        if (null !== $result) {
            return;
        }

        $model = new JsonModel;
        $e->setResult($model);
    }
}
