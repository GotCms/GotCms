<?php

return array(
    'layout'                => 'layouts/layout.phtml',
    'di' => array(
        'instance' => array(
            'alias' => array(
                'admin-index' => 'Admin\Controller\IndexController',
            ),
            'Zend\View\PhpRenderer' => array(
                'parameters' => array(
                    'resolver' => 'Zend\View\TemplatePathStack',
                    'options'  => array(
                        'script_paths' => array(
                            'admin' => __DIR__ . '/../views',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'routes' => array(
        'admin' => array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route' => '/admin',
                'defaults' => array(
                    'module' => 'admin',
                    'controller' => 'admin-index',
                    'action'     => 'index'
                )
            )
        ),
    ),
);
