<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'development-index' => 'Development\Controller\IndexController',
                'development-datatype' => 'Development\Controller\DatatypeController',
                'development-document-type' => 'Development\Controller\DocumentTypeController',
                'development-layout' => 'Development\Controller\LayoutController',
                'development-view' => 'Development\Controller\ViewController',
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
