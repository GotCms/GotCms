<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'development-index' => 'Development\Controller\IndexController',
            ),
            'Zend\View\PhpRenderer' => array(
                'parameters' => array(
                    'resolver' => 'Zend\View\TemplatePathStack',
                    'options'  => array(
                        'script_paths' => array(
                            'development' => __DIR__ . '/../views',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
