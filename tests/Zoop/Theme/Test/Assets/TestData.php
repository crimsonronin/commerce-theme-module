<?php

namespace Zoop\Theme\Test\Assets;

use Zoop\Shard\Serializer\Unserializer;
use Zoop\Store\DataModel\Store;

class TestData
{
    const DOCUMENT_STORE = 'Zoop\Store\DataModel\Store';

    /**
     * @param Unserializer $unserializer
     * @return Store
     */
    public static function createStore(Unserializer $unserializer)
    {
        $data = self::getJson('Store');
        return $unserializer->fromJson($data, self::DOCUMENT_STORE);
    }

    protected static function getJson($fileName)
    {
        return file_get_contents(__DIR__ . '/' . $fileName . '.json');
    }
}
