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
            'Zend\View\Resolver\TemplatePathStack' => array(
                'parameters' => array(
                    'paths'  => array(
                        'development' => __DIR__ . '/../views',
                    ),
                ),
            ),
        ),
    ),
);
