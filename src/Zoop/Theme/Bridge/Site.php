<?php

namespace Zoop\Theme\Bridge;

class Site extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {

        $data = [
            'css' => $legacyData['css'],
            'dojo' => $legacyData['dojo'],
            'favicon' => $legacyData['favicon'],
            'javascript' => $legacyData['javascript']
        ];

        return $data;
    }
}
