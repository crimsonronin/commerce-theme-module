<?php

namespace Zoop\Theme\Parser;

interface ParserInterface
{

    public function parse();

    public function setContent($content);

    public function getContent();

    public function getParsedAssets();

    public function setParsedAssets($parsedAssets);

    public function addParsedAsset($key, $parsedAsset);

    public function getParsedContent();

    public function setParsedContent($parsedContent);
}
