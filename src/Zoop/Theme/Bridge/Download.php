<?php

namespace Zoop\Theme\Bridge;

class Download extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        $data = $legacyData;

        return $data;
    }
}
