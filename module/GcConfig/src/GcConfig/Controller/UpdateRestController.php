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

use Gc\Mvc\Controller\RestAction;
use Gc\Media\Info;
use Gc\Version;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Exception;

/**
 * Config controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class UpdateRestController extends RestAction
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'update');

    /**
     * Get update version informations
     *
     * @return array
     */
    public function getList()
    {
        $versionIsLatest = Version::isLatest();
        $latestVersion   = Version::getLatest();
        $datatypesErrors = array();
        $modulesErrors   = array();
        $this->checkVersion(
            $this->getServiceLocator()->get('DatatypesList'),
            'datatype',
            $datatypesErrors
        );
        $this->checkVersion(
            $this->getServiceLocator()->get('ModulesList'),
            'module',
            $modulesErrors
        );

        return array(
            'gitProject'      => file_exists(GC_APPLICATION_PATH . '/.git'),
            'isLatest'        => $versionIsLatest,
            'latestVersion'   => $latestVersion,
            'datatypesErrors' => $datatypesErrors,
            'modulesErrors'   => $modulesErrors
        );
    }

    /**
     * Update cms
     *
     * @param array $data Data tu use
     *
     * @return array
     */
    public function create($data)
    {
        $versionIsLatest = Version::isLatest();
        $updater         = $this->getServiceLocator()->get('CoreUpdater');
        if (!$updater->load($data['adapter']) or $versionIsLatest) {
            return $this->notFoundAction();
        }

        //Fetch content
        if ($updater->update()) {
            //Upgrade cms
            if ($updater->upgrade()) {
                //Update modules
                $latestVersion = Version::getLatest();
                $modules       = $this->getServiceLocator()->get('CustomModules')->getLoadedModules();
                foreach ($modules as $module) {
                    if (method_exists($module, 'update')) {
                        try {
                            $module->update($latestVersion);
                        } catch (Exception $e) {
                            //don't care, modules fault
                        }
                    }
                }

                //Update database
                $configuration = $this->getServiceLocator()->get('Config');
                $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();
                if (!$updater->updateDatabase($configuration, $dbAdapter)) {
                    //Rollback cms
                    $updater->rollback(Version::VERSION);
                } else {
                    $updater->executeScripts();

                    return array(
                        'content' => sprintf('Cms update to %s', $latestVersion),
                        'messages' => $updater->getMessages()
                    );
                }
            }
        }

        return array(
            'errors' => $updater->getMessages()
        );
    }

    /**
     * Check version in info file
     * from $type directory
     *
     * @param array  $directories List of directories
     * @param string $type        Type of directory
     * @param array  &$errors     Insert in this all errors
     *
     * @return void
     */
    protected function checkVersion(array $directories, $type, array &$errors)
    {
        foreach ($directories as $path => $directoryName) {
            if (is_dir($path)) {
                $filename = $path . '/' . $type . '.info';
                $info     = new Info();
                if ($info->fromFile($filename) === true) {
                    $infos = $info->getInfos();
                    if (!empty($infos['cms_version'])) {
                        $this->checkCmsVersion($directoryName, $infos, $errors);
                    }
                }
            }
        }
    }

    /**
     * Check version in info file
     * from $type directory
     *
     * @param string $directoryName Directory name to use
     * @param array  $infos         File info data
     * @param array  &$errors       Insert in this all errors
     *
     * @return void
     */
    protected function checkCmsVersion($directoryName, array $infos, array &$errors)
    {
        preg_match('~(?<operator>[>=]*)(?<version>.+)~', $infos['cms_version'], $matches);
        if (empty($matches['operator'])) {
            if (version_compare(Version::getLatest(), $matches['version']) === 1) {
                $errors[] = basename($directoryName);
            }
        } else {
            if (!version_compare(Version::getLatest(), $matches['version'], $matches['operator'])) {
                $errors[] = $directoryName;
            }
        }
    }
}
