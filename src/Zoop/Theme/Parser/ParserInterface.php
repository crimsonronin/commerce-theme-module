<?php

namespace Zoop\Theme\Parser;

use Zoop\Theme\DataModel\AssetInterface;

interface ParserInterface
{
    public function parse();

    public function setAsset(AssetInterface $asset);

    public function getAsset();

    public function getParsedAssets();

    public function setParsedAssets($parsedAssets = []);

    public function addParsedAsset($key, AssetInterface $parsedAsset);
}
