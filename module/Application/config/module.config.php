<?php

return array(
    'layout'                => 'layouts/layout.phtml',
    'display_exceptions'    => true,
    'di' => array(
        'instance' => array(
            'alias' => array(
                'index' => 'Application\Controller\IndexController',
                'error' => 'Application\Controller\ErrorController',
                'view'  => 'Zend\View\PhpRenderer',
            ),
            'Zend\View\PhpRenderer' => array(
                'parameters' => array(
                    'resolver' => 'Zend\View\TemplatePathStack',
                    'options'  => array(
                        'script_paths' => array(
                            'application' => __DIR__ .'/../views',
                        ),
                    ),
                ),
            ),
           'Zend\Db\Adapter\Pgsql' => array(
                'parameters' => array(
                    'config' => array(
                        'host' => 'localhost',
                        'username' => 'got',
                        'password' => 'x8maoxfp',
                        'dbname' => 'escms',
                    ),
                ),
            ),
        ),
    ),
    'routes' => array(
        'home' => array(
            'type' => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route'    => '/',
                'defaults' => array(
                    'controller' => 'index',
                    'action'     => 'index',
                ),
            ),
        ),
    ),
);
