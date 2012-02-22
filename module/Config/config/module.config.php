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
);
