<?php

return array(
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
            'Zend\Mvc\Router\RouteStack' => array(
                'parameters' => array()
            )
        ),
    ),
);
