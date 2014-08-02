<?php

return [
    'zoop' => [
        'api' => [
            'endpoints' => [
                'asset',
                'themes',
                'themes/import',
            ]
        ],
        'shard' => [
            'manifest' => [
                'commerce' => [
                    'models' => [
                        'Zoop\Theme\DataModel' => __DIR__ . '/../src/Zoop/Theme/DataModel',
                    ],
                ],
            ],
            'rest' => [
                'manifest' => 'commerce',
                'cache_control' => [
                    'no_cache' => true
                ],
                'rest' => [
                    'themes' => [
                        'property' => 'id',
                        'class' => 'Zoop\Theme\DataModel\AbstractTheme',
                        'listeners' => [
                            'create' => [
                                'zoop.commerce.theme.listener.theme.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.commerce.theme.listener.theme.create',
                                'zoop.commerce.theme.listener.theme.flush',
                                'zoop.shardmodule.listener.location',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'delete' => [
                                'zoop.commerce.theme.listener.theme.delete',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'deleteList' => [],
                            'get' => [
                                'zoop.shardmodule.listener.get',
                                'zoop.api.listener.cors',
                                'zoop.commerce.theme.listener.theme.serialize',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'getList' => [
                                'zoop.shardmodule.listener.getlist',
                                'zoop.api.listener.cors',
                                'zoop.commerce.theme.listener.theme.serialize',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'options' => [
                                'zoop.api.listener.options',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'patch' => [
                                'zoop.shardmodule.listener.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.idchange',
                                'zoop.shardmodule.listener.patch',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'patchList' => [],
                            'replaceList' => [],
                            'update' => [
                                'zoop.shardmodule.listener.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.idchange',
                                'zoop.shardmodule.listener.update',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                        ],
                    ],
                    'themesimport' => [
                        'property' => 'id',
                        'class' => 'Zoop\Theme\DataModel\AbstractTheme',
                        'listeners' => [
                            'create' => [
                                'zoop.commerce.theme.listener.theme.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.commerce.theme.listener.theme.create',
                                'zoop.commerce.theme.listener.theme.flush',
                                'zoop.shardmodule.listener.location',
                                'zoop.commerce.theme.listener.theme.import.prepareviewmodel',
                            ],
                            'delete' => [],
                            'deleteList' => [],
                            'get' => [],
                            'getList' => [],
                            'options' => [
                                'zoop.api.listener.options',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'patch' => [],
                            'patchList' => [],
                            'replaceList' => [],
                            'update' => [],
                        ],
                    ],
                ],
            ],
        ],
        'theme' => [
            'temp_dir' => __DIR__ . '/../data/temp',
            'template_dir' => __DIR__ . '/../src/view/zoop-legacy',
            'max_file_upload_size' => (1024 * 1024 * 20), // 20MB
            'excludes' => [
                'css' => [
                    '/js/ie8/respond.proxy.gif'
                ],
                'javascript' => [
                    '/js/ie8/html5shiv.js',
                    '/js/ie8/respond.js',
                    '/js/ie8/respond.proxy.js',
                ],
            ],
            'admin' => [
                'templates' => []
            ],
            'email' => [
                'templates' => []
            ],
            'facebook' => [
                'templates' => []
            ],
            'storefront' => [
                'templates' => []
            ]
        ],
    ],
    'controllers' => [
        'invokables' => [
            'zoop.commerce.theme.controller.admin.theme' => 'Zoop\Theme\Controller\ThemeController',
            'zoop.commerce.theme.controller.admin.asset' => 'Zoop\Theme\Controller\AssetController',
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'zoop.commerce.theme.listener.theme.create' => 'Zoop\Theme\Controller\ThemeCreateListener',
            'zoop.commerce.theme.listener.theme.delete' => 'Zoop\Theme\Controller\ThemeDeleteListener',
            'zoop.commerce.theme.listener.theme.flush' => 'Zoop\Theme\Controller\ThemeFlushListener',
            'zoop.commerce.theme.listener.theme.serialize' => 'Zoop\Theme\Controller\ThemeSerializeListener',
            'zoop.commerce.theme.listener.theme.unserialize' => 'Zoop\Theme\Controller\ThemeUnserializeListener',
            'zoop.commerce.theme.listener.theme.import.prepareviewmodel' => 'Zoop\Theme\Controller\ThemeImportPrepareViewModelListener',
            'zoop.commerce.theme.parser.css' => 'Zoop\Theme\Parser\Css',
            'zoop.commerce.theme.private' => 'Zoop\Theme\DataModel\PrivateTheme',
            'zoop.commerce.theme.shared' => 'Zoop\Theme\DataModel\SharedTheme',
            'zoop.commerce.theme.zoop' => 'Zoop\Theme\DataModel\ZoopTheme',
        ],
        'factories' => [
            //controllers
            'zoop.commerce.theme.active' => 'Zoop\Theme\Service\ActiveThemeFactory',
            'zoop.commerce.theme.assetmanager' => 'Zoop\Theme\Service\AssetManagerFactory',
            'zoop.commerce.theme.creator.import' => 'Zoop\Theme\Service\Creator\ThemeCreatorImportFactory',
            'zoop.commerce.theme.manager' => 'Zoop\Theme\Service\ThemeManagerFactory',
            'zoop.commerce.theme.serializer.asset.unserializer' => 'Zoop\Theme\Service\Serializer\AssetUnserializerFactory',
            'zoop.commerce.theme.structure' => 'Zoop\Theme\Service\ThemeStructureFactory',
            'zoop.commerce.theme.template.admin' => 'Zoop\Theme\Service\AdminTemplateFactory',
            'zoop.commerce.theme.template.email' => 'Zoop\Theme\Service\EmailTemplateFactory',
            'zoop.commerce.theme.template.storefront' => 'Zoop\Theme\Service\StorefrontTemplateFactory',
            'zoop.commerce.theme.template.facebook' => 'Zoop\Theme\Service\FacebookTemplateFactory',
            'zoop.commerce.theme.template.legacy' => 'Zoop\Theme\Service\LegacyTemplateFactory',
            'zoop.commerce.theme.validator' => 'Zoop\Theme\Service\ValidatorFactory',
        ],
    ],
];
