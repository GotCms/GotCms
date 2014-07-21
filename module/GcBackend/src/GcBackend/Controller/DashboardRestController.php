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
 * @package    GcBackend
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcBackend\Controller;

use Gc\Document\Collection;
use Gc\Mvc\Controller\RestAction;
use Gc\User\Visitor;
use Gc\Version;

/**
 * Index controller for admin module
 *
 * @category   Gc_Application
 * @package    GcBackend
 * @subpackage Controller
 */
class DashboardRestController extends RestAction
{
    /**
     * Display dashboard
     *
     * @return array
     */
    public function getList()
    {
        $data                    = array();
        $data['version']         = Version::VERSION;
        $data['versionIsLatest'] = Version::isLatest();
        $data['versionLatest']   = Version::getLatest();

        $documents                        = new Collection();
        $contentStats                     = array();
        $contentStats['online_documents'] = array(
            'count' => count($documents->getAvailableDocuments()),
            'label' => 'Online documents',
            'route' => 'content',
        );

        $contentStats['total_documents'] = array(
            'count' => count($documents->select()->toArray()),
            'label' => 'Total documents',
            'route' => 'content',
        );

        $data['contentStats'] = $contentStats;

        $visitorModel      = new Visitor();
        $data['userStats'] = array(
            'total_visitors' => array(
                'count' => $visitorModel->getTotalVisitors(),
                'label' => 'Total visitors',
                'route' => 'statistics',
            ),
            'total_visits' => array(
                'count' => $visitorModel->getTotalPageViews(),
                'label' => 'Total page views',
                'route' => 'statistics',
            ),
        );

        $coreConfig                = $this->getServiceLocator()->get('CoreConfig');
        $widgets                   = @unserialize($coreConfig->getValue('dashboard_widgets'));
        $data['dashboardSortable'] = !empty($widgets['sortable']) ? Json::encode($widgets['sortable']) : '{}';
        $data['dashboardWelcome']  = !empty($widgets['welcome']);

        $data['customWidgets'] = array();
        $this->events()->trigger(__CLASS__, 'dashboard', $this, array('widgets' => &$data['customWidgets']));

        return $data;
    }

    /**
     * Save dashboard
     *
     * @param array $data Data returns
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        $coreConfig = $this->getServiceLocator()->get('CoreConfig');
        $config     = @unserialize($coreConfig->getValue('dashboard_widgets'));

        if (empty($config)) {
            $config = array();
        }

        if (!empty($data['dashboard'])) {
            $config['welcome'] = false;
        } else {
            $config['sortable'] = $data;
        }

        $coreConfig->setValue('dashboard_widgets', serialize($config));

        return array('success' => true);
    }
}
