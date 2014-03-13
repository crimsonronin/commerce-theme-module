<?php

namespace Zoop\Theme\Parser;

abstract class AbstractParser
{

    private $content;
    private $parsedAssets = [];
    private $parsedContent = [];

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getParsedAssets()
    {
        return $this->parsedAssets;
    }

    public function setParsedAssets($parsedAssets)
    {
        $this->parsedAssets = $parsedAssets;
    }

    public function addParsedAsset($key, $parsedAsset)
    {
        $this->parsedAssets[$key] = $parsedAsset;
    }

    public function getParsedContent()
    {
        return $this->parsedContent;
    }

    public function setParsedContent($parsedContent)
    {
        $this->parsedContent = $parsedContent;
    }

}
