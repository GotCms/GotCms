<?php
return array(
    'modules' => array(
        'Application'
        , 'Admin'
        , 'Config'
        , 'Content'
        , 'Development'
        , 'Statistics'
    ),
    'module_listener_options' => array(
        'config_cache_enabled' => false
        , 'cache_dir'            => 'data/cache'
        , 'module_paths' => array(
            './module'
        ),
    ),
    'autoloader' => array(
        'namespaces' => array(
            'Es' => __DIR__ . '/../vendor/Es',
            'Datatypes' => __DIR__ . '/../vendor/Datatypes'
        ),
    ),
);
