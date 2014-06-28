<?php

namespace Zoop\Theme\Bridge;

class Information extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        $data = $legacyData;

        $data['creditCard'] = $legacyData['credit_card'];
        unset($data['credit_card']);

        return $data;
    }
}
