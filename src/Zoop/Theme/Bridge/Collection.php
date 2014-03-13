<?php

namespace Zoop\Theme\Bridge;

use Zoop\Theme\Bridge\Product as ProductBridge;
use \DateTime;

class Collection extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        $data = [
            'id' => $legacyData['id'],
            'legacyId' => $legacyData['categoryId'],
            'name' => $legacyData['categoryName'],
            'slug' => $legacyData['categoryUrlSlug'],
            'naturalSortName' => $legacyData['categoryOrderName'],
            'description' => $legacyData['categoryDescription'],
            'customHtml' => [
                'append' => $legacyData['categoryAppendHtml'],
                'prepend' => $legacyData['categoryPrependHtml']
            ],
            'imageSets' => $this->parseImages($legacyData['images']),
            'url' => [
                'absolute' => $legacyData['absoluteUrl'],
                'relative' => $legacyData['categoryUrl']
            ],
            'children' => [],
            'depth' => $legacyData['categoryDepth'],
            'hidden' => empty($legacyData['categoryDisplay']),
            'imageSets' => $this->parseImages($legacyData['images']),
            'numberOfProducts' => $legacyData['numberOfProducts'],
            'products' => $this->parseProducts($legacyData['products']),
//            'order' => $legacyData['categoryOrder'],
            'createdOn' => new DateTime($legacyData['dateAdded']),
            'updatedOn' => new DateTime($legacyData['dateUpdated']),
        ];

        if (!empty($legacyData['categoryChildren']) && is_array($legacyData['categoryChildren'])) {
            foreach ($legacyData['categoryChildren'] as $key => $category) {
                $data['children'][$this->getKey($key)] = $this->parse($category);
            }
        }

        return $data;
    }

    private function parseProducts($legacyPoducts)
    {
        $products = [];

        if (!empty($legacyPoducts) && is_array($legacyPoducts)) {
            $productBridge = new ProductBridge;
            foreach ($legacyPoducts as $legacyProduct) {
                $productBridge->setData($legacyProduct);
                $products[] = $productBridge->getVariables();
            }
        }

        return $products;
    }

}
