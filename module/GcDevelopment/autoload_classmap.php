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
    'GcDevelopment\Controller\IndexController'            =>
        __DIR__ . '/src/GcDevelopment/Controller/IndexController.php',
    'GcDevelopment\Controller\DocumentTypeRestController' =>
        __DIR__ . '/src/GcDevelopment/Controller/DocumentTypeRestController.php',
    'GcDevelopment\Controller\DatatypeRestController'     =>
        __DIR__ . '/src/GcDevelopment/Controller/DatatypeRestController.php',
    'GcDevelopment\Controller\LayoutRestController'       =>
        __DIR__ . '/src/GcDevelopment/Controller/LayoutRestController.php',
    'GcDevelopment\Controller\ViewRestController'         =>
        __DIR__ . '/src/GcDevelopment/Controller/ViewRestController.php',
    'GcDevelopment\Controller\ScriptRestController'       =>
        __DIR__ . '/src/GcDevelopment/Controller/ScriptRestController.php',
    'GcDevelopment\Module'                                =>
        __DIR__ . '/Module.php',
    'GcDevelopment\Filter\Datatype'                       => __DIR__ . '/src/GcDevelopment/Filter/Datatype.php',
    'GcDevelopment\Filter\DocumentType'                   => __DIR__ . '/src/GcDevelopment/Filter/DocumentType.php',
    'GcDevelopment\Filter\Layout'                         => __DIR__ . '/src/GcDevelopment/Filter/Layout.php',
    'GcDevelopment\Filter\Script'                         => __DIR__ . '/src/GcDevelopment/Filter/Script.php',
    'GcDevelopment\Filter\View'                           => __DIR__ . '/src/GcDevelopment/Filter/View.php',
);
