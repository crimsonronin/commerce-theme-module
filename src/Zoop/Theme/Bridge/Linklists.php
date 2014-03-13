<?php

namespace Zoop\Theme\Bridge;

use Zoop\Theme\Bridge\Product as ProductBridge;
use \DateTime;

class Linklists extends AbstractBridge implements BridgeInterface
{
    public function __construct()
    {

    }

    protected function parse($legacyData)
    {
        $links = [];
        foreach ($legacyData as $link) {
            $links[] = [
                'title' => $link['linkTitle'],
                'url' => $link['linkUrl']
            ];
        }

        return [
            'mainNavigation' => $links
        ];
    }

}
