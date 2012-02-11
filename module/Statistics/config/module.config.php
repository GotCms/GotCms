<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'statistics-index' => 'Statistics\Controller\IndexController',
            ),
            'Zend\View\PhpRenderer' => array(
                'parameters' => array(
                    'resolver' => 'Zend\View\TemplatePathStack',
                    'options'  => array(
                        'script_paths' => array(
                            'statistics' => __DIR__ . '/../views',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
