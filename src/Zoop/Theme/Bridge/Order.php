<?php

namespace Zoop\Theme\Bridge;

use Zoop\Common\Utils\Currency;
use Zoop\Theme\Bridge\Product as ProductBridge;
use \DateTime;

class Order extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        $data = [
            'id' => $legacyData['orderId'],
            'address' => [
                'line1' => $legacyData['orderShippingAddress'],
                'line2' => '',
                'city' => $legacyData['orderShippingCity'],
                'state' => $legacyData['orderShippingState'],
                'zipcode' => $legacyData['orderShippingPostcode'],
                'country' => [
                    'id' => $legacyData['orderShippingCountry'],
                    'name' => $legacyData['orderShippingCountryFull']
                ]
            ],
            'email' => $legacyData['orderEmail'],
            'phone' => $legacyData['orderPhone'],
            'firstName' => $legacyData['orderFirstName'],
            'lastName' => $legacyData['orderLastName'],
            'total' => [
                'shippingPrice' => $legacyData['orderTotalShippingPrice'],
                'productPrice' => $legacyData['orderTotalProductPrice'],
                'productQuantity' => $legacyData['orderLastName'],
                'discountPrice' => $legacyData['orderTotalDiscountPrice'],
                'taxPrice' => $legacyData['orderTotalTax'],
                'orderPrice' => $legacyData['orderTotalPrice'],
                'currency' => [
                    'code' => $legacyData['orderCurrency'],
                    'symbol' => $legacyData['orderCurrencySymbol'],
                    'name' => Currency::getCurrencyName($legacyData['orderCurrency'])
                ],
            ],
            'products' => $this->parseProducts($legacyData['orderProducts']),
            'payment' => [
                'method' => $legacyData['orderPaymentMethod'],
            ],
            'shipping' => [
                'method' => $legacyData['orderShippingMethodName'],
                'trackingNumber' => $legacyData['orderTrackingNumber'],
            ],
            'completedOn' => new DateTime($legacyData['orderDateCompleted']),
        ];

        return $data;
    }

    private function parseProducts($legacyProducts)
    {
        $products = [];

        if (!empty($legacyProducts) && is_array($legacyProducts)) {
            $productBridge = new ProductBridge;
            foreach ($legacyProducts as $legacyProduct) {
                $productBridge->setData($legacyProduct);
                $product = $productBridge->getVariables();

                $product['quantity'] = $legacyProduct['optionQuantity'];
                $product['option'] = $legacyProduct['optionName'];
                $product['name'] = $legacyProduct['productName'];

                $products[] = $product;
            }
        }

        return $products;
    }
}
