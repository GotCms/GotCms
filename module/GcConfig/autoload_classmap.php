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
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

return array(
    'GcConfig\Controller\ConfigRestController' => __DIR__ . '/src/GcConfig/Controller/ConfigRestController.php',
    'GcConfig\Controller\RoleRestController'   => __DIR__ . '/src/GcConfig/Controller/RoleRestController.php',
    'GcConfig\Controller\UserRestController'   => __DIR__ . '/src/GcConfig/Controller/UserRestController.php',
    'GcConfig\Module'                          => __DIR__ . '/Module.php',
    'GcConfig\Filter\SystemConfig'             => __DIR__ . '/src/GcConfig/Filter/SystemConfig.php',
    'GcConfig\Filter\GeneralConfig'            => __DIR__ . '/src/GcConfig/Filter/GeneralConfig.php',
    'GcConfig\Filter\ServerConfig'             => __DIR__ . '/src/GcConfig/Filter/ServerConfig.php',
    'GcConfig\Filter\Role'                     => __DIR__ . '/src/GcConfig/Filter/Role.php',
    'GcConfig\Filter\User'                     => __DIR__ . '/src/GcConfig/Filter/User.php',
    'GcConfig\Filter\UserForgotPassword'       => __DIR__ . '/src/GcConfig/Filter/UserForgotPassword.php',
);
