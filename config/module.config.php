<?php

return [
    'zoop' => [
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
                                'zoop.commerce.theme.listener.theme.create',
                            ],
                            'delete' => [],
                            'deleteList' => [],
                            'get' => [
                                'zoop.shardmodule.listener.get',
                                'zoop.shardmodule.listener.serialize',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'getList' => [
                                'zoop.shardmodule.listener.getlist',
                                'zoop.shardmodule.listener.serialize',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'options' => [],
                            'patch' => [],
                            'patchList' => [],
                            'update' => [],
                            'replaceList' => [],
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
                'templates' => [
                    __DIR__ . '/../src/view/zoop-legacy/_global',
                    __DIR__ . '/../src/view/zoop-legacy/admin/_global',
                    __DIR__ . '/../src/view/zoop-legacy/admin/_default',
                ]
            ],
            'email' => [
                'templates' => [
                    __DIR__ . '/../src/view/zoop-legacy/_global',
                    __DIR__ . '/../src/view/zoop-legacy/zoop',
                    __DIR__ . '/../src/view/zoop-legacy/storefront/_global',
                    __DIR__ . '/../src/view/zoop-legacy/storefront/_default',
                ]
            ],
            'facebook' => [
                'templates' => [
                    __DIR__ . '/../src/view/zoop-legacy/_global',
                    __DIR__ . '/../src/view/zoop-legacy/facebook/_global',
                    __DIR__ . '/../src/view/zoop-legacy/facebook/_default',
                ]
            ],
            'storefront' => [
                'templates' => [
                    __DIR__ . '/../src/view/zoop-legacy/_global',
                    __DIR__ . '/../src/view/zoop-legacy/storefront/_global',
                    __DIR__ . '/../src/view/zoop-legacy/storefront/_default',
                ]
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'rest' => [
                //this route will look to load a controller
                //service called `shard.rest.<endpoint>`
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/[:endpoint][/:id]',
                    'constraints' => [
                        'endpoint' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id' => '[a-zA-Z][a-zA-Z0-9/_-]+',
                    ],
                ],
                'chain_routes' => [
                    'zoop/commerce/store'
                ]
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
