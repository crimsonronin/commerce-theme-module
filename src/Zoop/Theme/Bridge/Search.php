<?php

namespace Zoop\Theme\Bridge;

use Zoop\Theme\Bridge\Product as ProductBridge;
use \DateTime;

class Search extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        $data = [
            'title' => $legacyData['title'],
            'term' => $legacyData['term']
        ];

        if (!empty($legacyData) && is_array($legacyData['products'])) {
            $productBridge = new ProductBridge;

            foreach ($legacyData['products'] as $key => $product) {
                $productBridge->setData($product);
                $productData = $productBridge->getVariables();

                $key = $this->getKey($key);

                $data['products'][$key] = $productData;
            }
        }

        return $data;
    }

}
