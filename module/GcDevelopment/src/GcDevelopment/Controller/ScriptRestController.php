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
use GcDevelopment\Filter\Script as ScriptFilter;
use Gc\Script;

/**
 * Script controller
 *
 * @category   Gc_Application
 * @package    GcDevelopment
 * @subpackage Controller
 */
class ScriptRestController extends RestAction
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'development', 'permission' => 'script');

    /**
     * List all scripts
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getList()
    {
        $scriptCollection = new Script\Collection();
        $return           = array();
        foreach ($scriptCollection->getAll() as $script) {
            $return[] = $script->toArray();
        }

        return array('scripts' => $return);
    }

    /**
     * Get script
     *
     * @param integer $id Id of the script model
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function get($id)
    {
        $scriptModel = Script\Model::fromId($id);
        if (empty($scriptModel)) {
            return $this->notFoundAction();
        }

        return array('script' => $scriptModel->toArray());
    }

    /**
     * Create script
     *
     * @param array $data Data returns
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function create($data)
    {
        $scriptFilter = new ScriptFilter($this->getServiceLocator()->get('DbAdapter'));
        $scriptFilter->setData($data);
        if ($scriptFilter->isValid()) {
            $scriptModel = new Script\Model();
            $scriptModel->setName($scriptFilter->getValue('name'));
            $scriptModel->setIdentifier($scriptFilter->getValue('identifier'));
            $scriptModel->setDescription($scriptFilter->getValue('description'));
            $scriptModel->setContent($scriptFilter->getValue('content'));
            $scriptModel->save();

            return $scriptModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $scriptFilter->getMessages());
    }

    /**
     * Edit script
     *
     * @param integer $id   Id of the script
     * @param array   $data Data returns
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function update($id, $data)
    {
        $scriptModel = Script\Model::fromId($id);
        if (empty($scriptModel)) {
            return $this->notFoundAction();
        }

        $scriptFilter = new ScriptFilter($this->getServiceLocator()->get('DbAdapter'));
        $scriptFilter->setData($data);
        if ($scriptFilter->isValid()) {
            $scriptModel->setName($scriptFilter->getValue('name'));
            $scriptModel->setIdentifier($scriptFilter->getValue('identifier'));
            $scriptModel->setDescription($scriptFilter->getValue('description'));
            $scriptModel->setContent($scriptFilter->getValue('content'));
            $scriptModel->save();

            return $scriptModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $scriptFilter->getMessages());
    }

    /**
     * Delete Script
     *
     * @param integer $id Id of the script
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function delete($id)
    {
        $script = Script\Model::fromId($id);
        if (!empty($script) and $script->delete()) {
            return array('success' => true, 'content' => 'This script has been deleted.');
        }

        return $this->notFoundAction();
    }
}
