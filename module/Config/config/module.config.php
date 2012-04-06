<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'config-index' => 'Config\Controller\IndexController',
                'config-user' => 'Config\Controller\UserController',
            ),
            'Zend\View\Resolver\TemplatePathStack' => array(
                'parameters' => array(
                    'paths'  => array(
                        'config' => __DIR__ . '/../views',
                    ),
                ),
            ),
        ),
    ),
);
