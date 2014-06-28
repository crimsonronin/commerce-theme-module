<?php

namespace Zoop\Theme\Bridge;

class Checkout extends AbstractBridge implements BridgeInterface
{
    protected function parse($legacyData)
    {
        return $legacyData;
    }
}
