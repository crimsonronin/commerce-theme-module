<?php

namespace Zoop\Theme\Parser;
use Zoop\Theme\DataModel\AssetInterface;

abstract class AbstractParser
{

    private $asset;
    private $parsedAssets = [];

    public function getAsset()
    {
        return $this->asset;
    }

    public function setAsset(AssetInterface $content)
    {
        $this->asset = $content;
    }

    public function getParsedAssets()
    {
        return $this->parsedAssets;
    }

    public function setParsedAssets($parsedAssets = [])
    {
        $this->parsedAssets = $parsedAssets;
    }

    public function addParsedAsset($key, AssetInterface $parsedAsset)
    {
        $this->parsedAssets[$key] = $parsedAsset;
    }
}
