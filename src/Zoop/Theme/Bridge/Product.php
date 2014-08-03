<?php

namespace Zoop\Theme\Bridge;

use \DateTime;

class Product extends AbstractBridge implements BridgeInterface
{
    protected static $optionKey = 'sizeOption';
    protected static $state = [
        0 => 'inactive',
        1 => 'active',
        2 => 'sold-out',
        3 => 'pre-order',
        4 => 'coming-soon',
        5 => 'on-sale',
        6 => 'deleted'
    ];

    protected function parse($legacyData)
    {
        $skuDefinitions = $this->parseSkuDefinitions(
            $legacyData['options'],
            $this->parseShippingRates($legacyData['shippingRates']),
            $this->parseDimensions($legacyData),
            $legacyData['supplierName']
        );

        $data = [
            'id' => $legacyData['productId'],
            'legacyId' => $legacyData['productId'],
            'name' => $legacyData['productDisplayName'],
            'naturalSortName' => $legacyData['productDisplayName'],
            'slug' => $legacyData['productSafeName'],
            'brand' => [
                'name' => $legacyData['brandName'],
                'slug' => $legacyData['brandNameSlug']
            ],
            'taxonomy' => $legacyData['productType'],
            'collections' => [],
            'description' => $legacyData['productDescription'],
            'metaDescription' => $legacyData['metaDescription'],
            'attributes' => [],
            'options' => $this->parseOptions($legacyData['options']),
            'skuDefinitions' => $skuDefinitions,
            'skuOptionsJavascript' => $this->parseSkuOptionsJavascript($legacyData['productId'], $skuDefinitions),
            'notForIndividualSale' => false,
            'customHtml' => [
                'append' => $legacyData['appendHtml'],
                'prepend' => $legacyData['prependHtml'],
                'invoice' => $legacyData['invoiceMessage']
            ],
            'url' => [
                'absolute' => $legacyData['absoluteUrl'],
                'relative' => $legacyData['relativeUrl']
            ],
            'price' => [
                'full' => $legacyData['fullPrice'],
                'sale' => $legacyData['salePrice'],
                'wholesale' => $legacyData['wholesalePrice'],
                'saleActive' => (boolean) $legacyData['saleActive']
            ],
            'information' => [
                'cart' => $legacyData['cartMessage'],
                'invoice' => $legacyData['invoiceMessage'],
            ],
            'imageSets' => $this->parseImages($legacyData['images']),
            'state' => $this->parseState($legacyData['productStatusId']),
            'type' => [
                'name' => $legacyData['productType'],
            ],
            'suggested' => $this->parseSuggestedProducts($legacyData['suggested']),
            'hidden' => false,
            'canPurchase' => $legacyData['canPurchase'],
            'createdOn' => new DateTime($legacyData['dateAdded']),
            'updatedOn' => new DateTime($legacyData['dateUpdated']),
        ];

        return $data;
    }

    protected function parseSuggestedProducts($legacyProducts)
    {
        $suggested = [];
        if (!empty($legacyProducts)) {
            foreach ($legacyProducts as $legacyProduct) {
                $suggested[] = $this->parse($legacyProduct);
            }
        }

        return $suggested;
    }

    protected function parseState($stateId)
    {
        return self::$state[$stateId];
    }

    protected function parseOptions($legacyOptions)
    {
        $options = [];
        $optionValues = [];
        $type = 'dropdown';

        foreach ($legacyOptions as $legacyOption) {
            $optionValues[] = $legacyOption['optionName'];
        }

        if (in_array('NA', $optionValues) && count($optionValues) == 1) {
            $type = 'hidden';
        }

        //we only allow 1 option at the moment
        $options[self::$optionKey] = [
            'label' => 'Size / Option',
            'type' => $type,
            'required' => true,
            'values' => $optionValues,
            'isSkuSelector' => true,
            'helpMessage' => 'Please select your size / option'
        ];

        return $options;
    }

    protected function parseSkuOptionsJavascript($productId, $skuDefinitions)
    {
        $js = [];
        $js[] = '<script>';
        $js[] = 'var _zoop = _zoop || [];';

        $options = [];
        foreach ($skuDefinitions as $id => $data) {
            $options[] = '{' . $id . ': ' . json_encode($data) . '}';
        }

        $js[] = '_zoop.push(["_setProduct", {' .
            $productId .
            ': {"skuDefinitions": [' .
            implode(',', $options)
            . ']}}]);';

        $js[] = '</script>';

        return implode("\n", $js);
    }

    protected function parseSkuDefinitions($legacyOptions, $legacyShippingRates, $legacyDimensions, $legacySupplier)
    {
        $skuDefinitions = [];
        foreach ($legacyOptions as $legacyOption) {
            $skuDefinitions[$legacyOption['optionId']] = [
                'type' => 'physical',
                'optionMap' => [
                    self::$optionKey => $legacyOption['optionName']
                ],
                'quantity' => $legacyOption['optionQuantity'],
                'suppliers' => [
                    'name' => $legacySupplier,
                    'slug' => null
                ],
                'shipping' => $legacyShippingRates,
                'dimensions' => $legacyDimensions
            ];
        }

        return $skuDefinitions;
    }

    protected function parseShippingRates($legacySippingRates)
    {
        $shippingRates = [];
        foreach ($legacySippingRates as $rate) {
            $shippingRates[] = [
                'country' => $rate['rateShippingCountry'],
                'singleRate' => $rate['rateShippingSinglePrice'],
                'additionalRate' => $rate['rateShippingMultiplePrice']
            ];
        }
        return $shippingRates;
    }

    protected function parseDimensions($legacyData)
    {
        $dimensions = [
            'weight' => $this->getWeight($legacyData),
            'width' => $this->getWidth($legacyData),
            'height' => $this->getHeight($legacyData),
            'depth' => $this->getDepth($legacyData),
        ];

        return $dimensions;
    }
    
    protected function getWeight($legacyData)
    {
        return (float) ($legacyData['productWeight'] == 0) ?
            $legacyData['productTypeWeight'] :
            $legacyData['productWeight'];
    }
    
    protected function getWidth($legacyData)
    {
        return (float) ($legacyData['productWidth'] == 0) ?
            $legacyData['productTypeWidth'] :
            $legacyData['productWidth'];
    }
    
    protected function getHeight($legacyData)
    {
        return (float) ($legacyData['productHeight'] == 0) ?
            $legacyData['productTypeHeight'] :
            $legacyData['productHeight'];
    }
    
    protected function getDepth($legacyData)
    {
        return (float) ($legacyData['productLength'] == 0) ?
            $legacyData['productTypeLength'] :
            $legacyData['productLength'];
    }
}
