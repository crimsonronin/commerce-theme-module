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
    'service_manager' => [
        'invokables' => [
            'zoop.commerce.theme.parser.css' => 'Zoop\Theme\Parser\Css',
            'zoop.commerce.theme.private' => 'Zoop\Theme\DataModel\PrivateTheme',
            'zoop.commerce.theme.shared' => 'Zoop\Theme\DataModel\SharedTheme',
            'zoop.commerce.theme.zoop' => 'Zoop\Theme\DataModel\ZoopTheme',
        ],
        'factories' => [
            //controllers
            'zoop.commerce.controller.admin.theme' => 'Zoop\Theme\Service\ThemeControllerFactory',
            'zoop.commerce.controller.admin.theme.asset' => 'Zoop\Theme\Service\AssetControllerFactory',
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
