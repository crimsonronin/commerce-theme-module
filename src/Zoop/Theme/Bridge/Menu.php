<?php

namespace Zoop\Theme\Bridge;

use Zoop\Theme\Bridge\Collection as CollectionBridge;

class Menu extends AbstractBridge implements BridgeInterface
{
    const TYPE_LINK = 'link';
    const TYPE_COLLECTION = 'collection';
    const TYPE_PRODUCT = 'product';
    const TYPE_PAGE = 'page';
    const TYPE_SEARCH = 'search';
    const TYPE_LINKLIST = 'linklist';

    private static $types = [
        self::TYPE_LINK,
        self::TYPE_COLLECTION,
        self::TYPE_PRODUCT,
        self::TYPE_PAGE,
        self::TYPE_SEARCH,
        self::TYPE_LINKLIST
    ];

    protected function parse($legacyData)
    {
        $data = [];

        if (is_array($legacyData) && !empty($legacyData)) {
            foreach ($legacyData as $key => $legacy) {
                $key = $this->getKey($key);

                $object = $this->parseCollections($legacy);

                $data[$key] = [
                    'name' => $legacy['categoryName'],
                    'naturalSortName' => $legacy['categoryOrderName'],
                    'type' => self::TYPE_COLLECTION,
                    'object' => $object,
                    'url' => $object['url']['relative'],
                    'hidden' => false,
                    'children' => []
                ];
            }
        }

        return $data;
    }

    private function parseCollections($collection)
    {
        $bridge = new CollectionBridge;

        $bridge->setData($collection);

        $data = $bridge->getVariables();

        unset($data['products'], $data['numberOfProducts']);

        return $data;
    }

}
