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
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

return array(
    'Development\Controller\IndexController'            =>
        __DIR__ . '/src/Development/Controller/IndexController.php',
    'Development\Controller\DocumentTypeRestController' =>
        __DIR__ . '/src/Development/Controller/DocumentTypeRestController.php',
    'Development\Controller\DatatypeRestController'     =>
        __DIR__ . '/src/Development/Controller/DatatypeRestController.php',
    'Development\Controller\LayoutRestController'       =>
        __DIR__ . '/src/Development/Controller/LayoutRestController.php',
    'Development\Controller\ViewRestController'         =>
        __DIR__ . '/src/Development/Controller/ViewRestController.php',
    'Development\Controller\ScriptRestController'       =>
        __DIR__ . '/src/Development/Controller/ScriptRestController.php',
    'Development\Module'                                =>
        __DIR__ . '/Module.php',
    'Development\Filter\Datatype'                       => __DIR__ . '/src/Development/Filter/Datatype.php',
    'Development\Filter\DocumentType'                   => __DIR__ . '/src/Development/Filter/DocumentType.php',
    'Development\Filter\Layout'                         => __DIR__ . '/src/Development/Filter/Layout.php',
    'Development\Filter\Script'                         => __DIR__ . '/src/Development/Filter/Script.php',
    'Development\Filter\View'                           => __DIR__ . '/src/Development/Filter/View.php',
);
