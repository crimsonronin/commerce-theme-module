<?php

return [
    'collection' => [
        'id' => '1',
        'categoryId' => '1',
        'categoryName' => 'The Test Category',
        'categoryUrlSlug' => 'the-test-category',
        'categoryOrderName' => 'Test Category, The',
        'categoryDescription' => 'This is a test category',
        'categoryAppendHtml' => '<b>Stuff before a category</b>',
        'categoryPrependHtml' => '<p>Stuff after a category</p>',
        'absoluteUrl' => 'http://apple.zoopcommerce.com/category/the-test-category',
        'categoryUrl' => '/category/the-test-category',
        'categoryDepth' => '1',
        'categoryDisplay' => true,
        'images' => [
            [
                'fileAlt' => 'Test image',
                'fileType' => 'images/png',
                'fileExt' => 'png',
                'fileHeight' => 100,
                'fileWidth' => 100,
                'fileSrc' => 'http://zoopcommerce.com/images/test.png'
            ]
        ],
        'numberOfProducts' => 10,
        'products' => [],
        'categoryChildren' => [
            [
                'id' => '2',
                'categoryId' => '2',
                'categoryName' => 'Sub Category',
                'categoryUrlSlug' => 'sub-category',
                'categoryOrderName' => 'Sub Category',
                'categoryDescription' => 'This is a test sub category',
                'categoryAppendHtml' => '<b>Stuff before a sub category</b>',
                'categoryPrependHtml' => '<p>Stuff after a sub category</p>',
                'absoluteUrl' => 'http://apple.zoopcommerce.com/category/the-test-category/sub-category',
                'categoryUrl' => '/category/the-test-category/sub-category',
                'categoryDepth' => '2',
                'categoryDisplay' => true,
                'images' => [
                    [
                        'fileAlt' => 'Test image',
                        'fileType' => 'images/png',
                        'fileExt' => 'png',
                        'fileHeight' => 100,
                        'fileWidth' => 100,
                        'fileSrc' => 'http://zoopcommerce.com/images/test.png'
                    ]
                ],
                'numberOfProducts' => 1,
                'products' => [],
                'categoryOrder' => 1,
                'dateAdded' => '2014-02-01 00:00',
                'dateUpdated' => '2014-02-02 00:00',
            ],
        ],
        'categoryOrder' => 1,
        'dateAdded' => '2014-01-01 00:00',
        'dateUpdated' => '2014-01-02 00:00',
    ]
];
