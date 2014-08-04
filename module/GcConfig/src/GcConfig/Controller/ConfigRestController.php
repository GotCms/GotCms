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
 * @package    GcConfig
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcConfig\Controller;

use GcConfig\Filter\AbstractConfigFilter;
use Gc\Core\Config as CoreConfig;
use Gc\Mvc\Controller\RestAction;

/**
 * Config controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class ConfigRestController extends RestAction
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'config');

    /**
     * List all roles
     *
     * @return array
     */
    public function getList()
    {
        $configFilter = $this->getConfigFilter();
        $coreConfig   = $this->getServiceLocator()->get('CoreConfig');
        return array('configs' => $this->getConfigFields($coreConfig, $configFilter));
    }

    /**
     * Get user
     *
     * @param integer $id Id of the user
     *
     * @return array
     */
    public function get($id)
    {
        $configFilter = $this->getConfigFilter();
        if (!$configFilter->has($id)) {
            return $this->notFoundAction();
        }

        return array('config' => $this->getServiceLocator()->get('CoreConfig')->get($id));
    }

    /**
     * Edit user
     *
     * @param integer $id   Id of the user
     * @param array   $data Data to use
     *
     * @return array
     */
    public function update($id, $data)
    {
        $configFilter = $this->getConfigFilter();
        if (!$configFilter->has($id)) {
            return $this->notFoundAction();
        }

        $coreConfig = $this->getServiceLocator()->get('CoreConfig');
        $configFilter->setValues($coreConfig->getValues());
        $configFilter->addData($data);
        if ($configFilter->isValid()) {
            $values = $configFilter->getValues();
            foreach ($values as $key => $value) {
                $coreConfig->setValue($key, $value);
            }

            return array('configs' => $this->getConfigFields($coreConfig, $configFilter));
        }

        return array('content' => 'Invalid data', 'errors' => $configFilter->getMessages());
    }


    /**
     * Get config fileds
     *
     * @param CoreConfig           $coreConfig   Core configuration
     * @param AbstractConfigFilter $configFilter Config filter
     *
     * @return array
     */
    public function getConfigFields(CoreConfig $coreConfig, AbstractConfigFilter $configFilter)
    {
        $return = array();
        foreach ($coreConfig->getValues() as $value) {
            if ($configFilter->has($value['identifier'])) {
                $return[] = $value;
            }
        }

        return $return;
    }

    /**
     * Get config filter class
     *
     * @return \Gc\InputFilter\AbstractInputFilter
     */
    public function getConfigFilter()
    {
        $class = sprintf('GcConfig\\Filter\\%sConfig', ucfirst($this->getRouteMatch()->getParam('type')));
        return new $class;
    }
}
