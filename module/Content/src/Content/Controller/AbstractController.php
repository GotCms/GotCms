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
 * @package    Content
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Content\Controller;

use Gc\Component;
use Gc\Document\Collection as DocumentCollection;
use Gc\Mvc\Controller\Action;
use Zend\Json\Json;

/**
 * Document controller
 *
 * @category   Gc_Application
 * @package    Content
 * @subpackage Controller
 */
class AbstractController extends Action
{
    /**
     * Initialize Document Controller
     *
     * @return void
     */
    public function init()
    {
        $documents = new DocumentCollection();
        $documents->load(0);

        $this->layout()->setVariable('treeview', Component\TreeView::render(array($documents)));

        $routes = array(
            'edit' => 'content/document/edit',
            'new' => 'content/document/create',
            'delete' => 'content/document/delete',
            'copy' => 'content/document/copy',
            'cut' => 'content/document/cut',
            'paste' => 'content/document/paste',
            'publish' => 'content/document/publish',
            'unpublish' => 'content/document/unpublish',
        );

        $arrayRoutes = array();
        foreach ($routes as $key => $route) {
            $arrayRoutes[$key] = $this->url()->fromRoute($route, array('id' => 'itemId'));
        }

        $this->layout()->setVariable('routes', Json::encode($arrayRoutes));
    }
}
