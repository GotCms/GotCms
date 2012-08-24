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
 * @category Gc
 * @package  Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Admin\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Core\Config;

class IndexController extends Action
{
    /**
     * Display dashboard
     * @return void
     */
    public function indexAction()
    {
        $data = array();
        $data['version'] = \Gc\Version::VERSION;
        $data['versionIsLatest'] = \Gc\Version::isLatest();
        $data['versionLatest'] = \Gc\Version::getLatest();

        $content_stats = array();
        $documents = new \Gc\Document\Collection();
        $content_stats['online_documents'] = array(
            'count' => count($documents->getAvailableDocuments()),
            'label' => 'Online documents',
            'route' => 'content',
        );

        $content_stats['total_documents'] = array(
            'count' => count($documents->select()->toArray()),
            'label' => 'Total documents',
            'route' => 'content',
        );

        $data['contentStats'] = $content_stats;
        $widgets = @unserialize(Config::getValue('dashboard-widgets'));
        $data['dashboardSortable'] = !empty($widgets['sortable']) ? \Zend\Json\Json::encode($widgets['sortable']) : '{}';
        $data['dashboardWelcome'] = !empty($widgets['welcome']);

        return $data;
    }

    public function saveDashboardAction()
    {
        $params = $this->getRequest()->getPost()->toArray();

        $config = @unserialize(Config::getValue('dashboard-widgets'));

        if(empty($config))
        {
            $config = array();
        }

        if(!empty($params['dashboard']))
        {
            $config['welcome'] = FALSE;
        }
        else
        {
            $config['sortable'] = $params;
        }

        Config::setValue('dashboard-widgets', serialize($config));

        return $this->_returnJson(array('success' => TRUE));
    }
}
