<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'content-index' => 'Content\Controller\IndexController',
            ),
            'Zend\View\PhpRenderer' => array(
                'parameters' => array(
                    'resolver' => 'Zend\View\TemplatePathStack',
                    'options'  => array(
                        'script_paths' => array(
                            'content' => __DIR__ . '/../views',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'routes' => array(
        'content' => array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route' => '/content',
                'defaults' => array(
                    'module' => 'content',
                    'controller' => 'content-index',
                    'action'     => 'index'
                )
            )
        ),
    ),
);
