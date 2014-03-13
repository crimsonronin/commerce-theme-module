<?php

namespace Zoop\Theme\Bridge;

use Zoop\Theme\Bridge\Collection as CollectionBridge;
use \DateTime;

class Collections extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        $data = [];

        if (!empty($legacyData) && is_array($legacyData)) {
            $collection = new CollectionBridge;

            foreach ($legacyData as $key => $legacy) {
                $collection->setData($legacy);
                $collectionData = $collection->getVariables();
                $key = $this->getKey($key);

                $data[$key] = $collectionData;
            }
        }
        return $data;
    }

}
