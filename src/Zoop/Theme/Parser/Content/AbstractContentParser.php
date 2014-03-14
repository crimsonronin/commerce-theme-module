<?php

namespace Zoop\Theme\Parser\Content;

use Zoop\Theme\DataModel\AssetInterface;

abstract class AbstractContentParser
{
    private $asset;
    private $parser;
    private $parsedAssets = [];

    /**
     * @return ChainedParser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param ChainedParser $parser
     */
    public function setParser(ChainedParser $parser)
    {
        $this->parser = $parser;
    }

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
