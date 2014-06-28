<?php

namespace Zoop\Theme\Bridge;

class Store extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        $data = [
            'address' => [
                'line1' => $legacyData['address'],
                'line2' => $legacyData[''],
                'city' => $legacyData['city'],
                'state' => $legacyData['state'],
                'zipcode' => $legacyData['postcode'],
                'country' => [
                    'id' => $legacyData['country'],
                    'name' => $legacyData['country_name']
                ]
            ],
            'business' => [
                'name' => $legacyData['business_name'],
                'email' => $legacyData['sales_email'],
            ],
            'currencies' => [
                [
                    'code' => $legacyData['currency_code'],
                    'symbol' => $legacyData['currency_symbol']
                ]
            ],
            'customDomain' => $legacyData['custom_domain'],
            'description' => $legacyData['description'],
            'email' => !empty($legacyData['sales_email']) ? $legacyData['sales_email'] : $legacyData['email'],
            'googleAnalyticsTrackingId' => $legacyData['google_analytics_code'],
            'name' => $legacyData['name'],
            'phoneNumber' => $legacyData['phone_number'],
            'subdomain' => $legacyData['subdomain'],
            'facebook' => $legacyData['facebook_url'],
            'twitter' => $legacyData['twitter_url'],
            'youtube' => $legacyData['youtube_url'],
            'instagram' => $legacyData['instagram_url'],
            'googlePlus' => '',
            'pinterest' => '',
            'regionalTaxationRules' => [
                [
                    'taxationRule' => [
                        'name' => $legacyData['tax_label'],
                        'rate' => $legacyData['tax_rate'],
                        'number' => $legacyData['tax_number'],
                        'isShippingTaxed' => $legacyData['tax_shipping'],
                        'isTaxRemoved' => $legacyData['tax_remove'],
                        'isTaxIncluded' => $legacyData['tax_is_included'],
                    ],
                    'country' => $legacyData['tax_country'],
                    'region' => $legacyData['tax_region'],
                    'zipcode' => $legacyData['tax_postcode'],
                ]
            ],
            'url' => $legacyData['store_url'],
            'imageSets' => $this->parseImages($legacyData['header_image'])
        ];

        if (empty($data['imageSets']) && !empty($legacyData['email_header'])) {
            $data['imageSets'] = $this->parseImages([0 => ['medium' => $legacyData['email_header']]]);
        }

        return $data;
    }
}
