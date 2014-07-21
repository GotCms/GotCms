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
use GcDevelopment\Filter\Layout as LayoutFilter;
use Gc\Layout;

/**
 * Layout controller
 *
 * @category   Gc_Application
 * @package    GcDevelopment
 * @subpackage Controller
 */
class LayoutRestController extends RestAction
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'development', 'permission' => 'layout');

    /**
     * List all layouts
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getList()
    {
        $layoutCollection = new Layout\Collection();
        $return           = array();
        foreach ($layoutCollection->getLayouts() as $layout) {
            $return[] = $layout->toArray();
        }

        return array('layouts' => $return);
    }

    /**
     * Get layout
     *
     * @param integer $id Id of the layout model
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function get($id)
    {
        $layoutModel = Layout\Model::fromId($id);
        if (empty($layoutModel)) {
            return $this->notFoundAction();
        }

        return array('layout' => $layoutModel->toArray());
    }

    /**
     * Create layout
     *
     * @param array $data Data returns
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function create($data)
    {
        $layoutFilter = new LayoutFilter($this->getServiceLocator()->get('DbAdapter'));
        $layoutFilter->setData($data);
        if ($layoutFilter->isValid()) {
            $layoutModel = new Layout\Model();
            $layoutModel->setName($layoutFilter->getValue('name'));
            $layoutModel->setIdentifier($layoutFilter->getValue('identifier'));
            $layoutModel->setDescription($layoutFilter->getValue('description'));
            $layoutModel->setContent($layoutFilter->getValue('content'));
            $layoutModel->save();

            return $layoutModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $layoutFilter->getMessages());
    }

    /**
     * Edit layout
     *
     * @param integer $id   Id of the layout
     * @param array   $data Data returns
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function update($id, $data)
    {
        $layoutModel = Layout\Model::fromId($id);
        if (empty($layoutModel)) {
            return $this->notFoundAction();
        }

        $layoutFilter = new LayoutFilter($this->getServiceLocator()->get('DbAdapter'));
        $layoutFilter->setData($data);
        if ($layoutFilter->isValid()) {
            $layoutModel->setName($layoutFilter->getValue('name'));
            $layoutModel->setIdentifier($layoutFilter->getValue('identifier'));
            $layoutModel->setDescription($layoutFilter->getValue('description'));
            $layoutModel->setContent($layoutFilter->getValue('content'));
            $layoutModel->save();

            return $layoutModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $layoutFilter->getMessages());
    }

    /**
     * Delete Layout
     *
     * @param integer $id Id of the layout
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function delete($id)
    {
        $layout = Layout\Model::fromId($id);
        if (!empty($layout) and $layout->delete()) {
            return array('success' => true, 'content' => 'This layout has been deleted.');
        }

        return $this->notFoundAction();
    }
}
