<?php

return [
    'modules' => [
        'Zoop\MaggottModule',
        'DoctrineModule',
        'DoctrineMongoODMModule',
        'Zoop\ShardModule',
        'Zoop\GatewayModule',
        'Zoop\GomiModule',
        'Zoop\Api',
        'Zoop\DataModel',
        'Zoop\Common',
        'Zoop\User',
        'Zoop\Theme',
        'Zoop\Test'
    ],
    'module_listener_options' => [
        'config_glob_paths' => [
            __DIR__ . '/test.module.config.php',
        ],
    ],
];
