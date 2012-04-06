<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'admin-index' => 'Admin\Controller\IndexController',
            ),
            'Zend\View\Resolver\TemplatePathStack' => array(
                'parameters' => array(
                    'paths'  => array(
                        'admin' => __DIR__ . '/../views',
                    ),
                ),
            ),
        ),
    ),
);
