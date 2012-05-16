<?php

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                'config-index' => 'Config\Controller\IndexController',
                'config-user' => 'Config\Controller\UserController',
                'config-role' => 'Config\Controller\RoleController',
                'config-rule' => 'Config\Controller\RuleController',
                'config-cms' => 'Config\Controller\CmsController',
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
