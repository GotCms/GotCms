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
 * @package    GcDevelopment
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcDevelopment\Controller;

use Gc\Mvc\Controller\RestAction;
use GcDevelopment\Filter\View as ViewFilter;
use Gc\View;
use Zend\Http\Headers;
use ZipArchive;

/**
 * View controller
 *
 * @category   Gc_Application
 * @package    GcDevelopment
 * @subpackage Controller
 */
class ViewRestController extends RestAction
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'development', 'permission' => 'view');

    /**
     * List all views
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getList()
    {
        $viewCollection = new View\Collection();
        $return         = array();
        foreach ($viewCollection->getViews() as $view) {
            $return[] = $view->toArray();
        }

        return array('views' => $return);
    }

    /**
     * Get view
     *
     * @param integer $id Id of the view model
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function get($id)
    {
        $viewModel = View\Model::fromId($id);
        if (empty($viewModel)) {
            return $this->notFoundAction();
        }

        return array('view' => $viewModel->toArray());
    }

    /**
     * Create view
     *
     * @param array $data Data returns
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function create($data)
    {
        $viewFilter = new ViewFilter($this->getServiceLocator()->get('DbAdapter'));
        $viewFilter->setData($data);
        if ($viewFilter->isValid()) {
            $viewModel = new View\Model();
            $viewModel->setName($viewFilter->getValue('name'));
            $viewModel->setIdentifier($viewFilter->getValue('identifier'));
            $viewModel->setDescription($viewFilter->getValue('description'));
            $viewModel->setContent($viewFilter->getValue('content'));
            $viewModel->save();

            return $viewModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $viewFilter->getMessages());
    }

    /**
     * Edit view
     *
     * @param integer $id   Id of the view
     * @param array   $data Data returns
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function update($id, $data)
    {
        $viewModel = View\Model::fromId($id);
        if (empty($viewModel)) {
            return $this->notFoundAction();
        }

        $viewFilter = new ViewFilter($this->getServiceLocator()->get('DbAdapter'));
        $viewFilter->setData($data);
        if ($viewFilter->isValid()) {
            $viewModel->setName($viewFilter->getValue('name'));
            $viewModel->setIdentifier($viewFilter->getValue('identifier'));
            $viewModel->setDescription($viewFilter->getValue('description'));
            $viewModel->setContent($viewFilter->getValue('content'));
            $viewModel->save();

            return $viewModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $viewFilter->getMessages());
    }

    /**
     * Delete View
     *
     * @param integer $id Id of the view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function delete($id)
    {
        $view = View\Model::fromId($id);
        if (!empty($view) and $view->delete()) {
            return array('success' => true, 'content' => 'This view has been deleted.');
        }

        return $this->notFoundAction();
    }
}
