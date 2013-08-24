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
 * @subpackage ActivityLog
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace ActivityLog;

use ActivityLog\Model\Template;
use Gc\Module\AbstractModule;
use Zend\EventManager\EventInterface as Event;
use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\ModuleManager\ModuleManager;

/**
 * Activity log module bootstrap
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage ActivityLog
 */
class Module extends AbstractModule
{
    /**
     * Template model
     *
     * @var Template
     */
    protected $template;

    /**
     * Get module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Boostrap
     *
     * @param Event $e Event
     *
     * @return void
     */
    public function onBootstrap(Event $e)
    {
        $this->template = new Template();
        $templates      = $this->template->getTemplates();
        $application    = $e->getApplication();
        $serviceManager = $application->getServiceManager();

        foreach ($templates as $template) {
            $this->events()->attach(
                $template['event_identifier'],
                $template['event_name'],
                function (Event $event) use ($template, $serviceManager) {
                    $userId = null;
                    $auth   = $serviceManager->get('Auth');
                    if ($auth->hasIdentity()) {
                        $userId = $auth->getIdentity()->getId();
                        $event->setParam('user', $auth->getIdentity()->getName());
                    } else {
                        $remote = new RemoteAddress;
                        $event->setParam('user', $remote->getIpAddress());
                    }

                    $content = $this->template->render($event, $template['template']);
                    $this->template->addEvent($content, $template['id'], $userId);
                }
            );
        }
    }

    /**
     * Update module
     *
     * @param string $latestVersion Latest version
     *
     * @return boolean
     */
    public function update($latestVersion)
    {
        $filename = __DIR__ . sprintf(
            '/data/update/%s/install-%s.sql',
            $latestVersion,
            str_replace('pdo_', '', $this->getDriverName())
        );

        if (file_exists($filename)) {
            $pdoResource = $this->getAdapter()->getDriver()->getConnection()->getResource();
            $pdoResource->exec(
                file_get_contents($filename)
            );
        }

        return true;
    }

    /**
     * Install module
     *
     * @return boolean
     */
    public function install()
    {
        $pdoResource = $this->getAdapter()->getDriver()->getConnection()->getResource();
        $pdoResource->exec(
            file_get_contents(
                __DIR__ . sprintf('/data/sql/install-%s.sql', str_replace('pdo_', '', $this->getDriverName()))
            )
        );

        return true;
    }

    /**
     * Uninstall module
     *
     * @return boolean
     */
    public function uninstall()
    {
        $pdoResource = $this->getAdapter()->getDriver()->getConnection()->getResource();
        $pdoResource->exec(
            file_get_contents(
                __DIR__ . sprintf('/data/sql/uninstall-%s.sql', str_replace('pdo_', '', $this->getDriverName()))
            )
        );

        return true;
    }
}
