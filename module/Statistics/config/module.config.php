<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'statistics-index' => 'Statistics\Controller\IndexController',
            ),
            'Zend\View\Resolver\TemplatePathStack' => array(
                'parameters' => array(
                    'paths'  => array(
                        'statistics' => __DIR__ . '/../views',
                    ),
                ),
            ),
        ),
    ),
);
