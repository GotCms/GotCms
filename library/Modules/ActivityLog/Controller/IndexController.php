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
 * @subpackage ActivityLog\Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace ActivityLog\Controller;

use Gc\Module\Controller\AbstractController;
use ActivityLog\Model\Event;
use Zend\View\Model\JsonModel;

/**
 * IndexController
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage ActivityLog\Controller
 */
class IndexController extends AbstractController
{
    /**
     * Index action, list all documents with comments
     *
     * @return array
     */
    public function indexAction()
    {
        $model  = new Event\Collection();
        $events = $model->getEvents();

        return array('events' => $events);
    }

    /**
     * Remove event
     *
     * @return JsonModel
     */
    public function removeEventAction()
    {
        $model   = Event\Model::fromId($this->params()->fromRoute('id'));
        $success = false;
        if (!empty($model)) {
            $model->delete();
            $success = true;
        }

        $jsonModel = new JsonModel();
        $jsonModel->setVariables(array('success' => $success));
        $jsonModel->setTerminal(true);

        return $jsonModel;
    }
}
