<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'admin-index' => 'Admin\Controller\IndexController',
                'admin-user' => 'Admin\Controller\UserController',
            ),
            'Zend\View\Renderer\PhpRenderer' => array(
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
);
