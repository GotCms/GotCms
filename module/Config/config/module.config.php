<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'config-index' => 'Config\Controller\IndexController',
            ),
            'Zend\View\PhpRenderer' => array(
                'parameters' => array(
                    'resolver' => 'Zend\View\TemplatePathStack',
                    'options'  => array(
                        'script_paths' => array(
                            'config' => __DIR__ . '/../views',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'routes' => array(
        'config' => array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route' => '/config',
                'defaults' => array(
                    'module' => 'config',
                    'controller' => 'config-index',
                    'action'     => 'index'
                )
            )
        ),
    ),
);
