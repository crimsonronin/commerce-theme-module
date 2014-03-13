<?php

namespace Zoop\Theme\Bridge;

class Page extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        $data['breadcrumbs'] = $legacyData['breadcrumbs'];
        $data['css'] = $legacyData['css'];
        $data['javascript'] = $legacyData['javascript'];
        $data['title'] = $legacyData['head']['title'];
        $data['meta'] = [
            'description' => $legacyData['head']['meta']['description'],
            'openGraph' => $legacyData['meta']['openGraph'],
            'twitter' => $legacyData['meta']['twitter'],
        ];
        $data['url'] = $legacyData['url'];
        $data['body']['class'] = $legacyData['body']['class'];

        $data['googleAnalytics']['id'] = $legacyData['googleAnalytics']['id'];
        $data['googleAnalytics']['pageUrl'] = $legacyData['googleAnalytics']['pageUrl'];

        return $data;
    }
}
