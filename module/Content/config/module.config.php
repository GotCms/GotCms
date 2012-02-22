<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'content-index' => 'Content\Controller\IndexController',
            ),
            'Zend\View\Resolver\TemplatePathStack' => array(
                'parameters' => array(
                    'paths'  => array(
                        'content' => __DIR__ . '/../views',
                    ),
                ),
            ),
        ),
    ),
);
