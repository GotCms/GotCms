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
 * @package    GcContent
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

use Gc\Document\Model as DocumentModel;

return array(
    'controllers' => array(
        'invokables' => array(
            'ContentRest'     => 'GcContent\Controller\IndexController',
            'DocumentRest'    => 'GcContent\Controller\DocumentRest',
            'MediaRest'       => 'GcContent\Controller\MediaRestController',
            'TranslationRest' => 'GcContent\Controller\TranslationRestController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'content' => __DIR__ . '/../views',
        ),
    ),
    'router' => array(
        'routes' => array(
            'content' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin/content',
                    'defaults' =>
                    array (
                        'module'     => 'gccontent',
                        'controller' => 'ContentRest',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'translation' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/translation[/:id]',
                            'defaults' =>
                            array (
                                'module'     => 'gccontent',
                                'controller' => 'TranslationRest',
                            ),
                        ),
                    ),
                )
            ),
        ),
    )
);
