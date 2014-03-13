<?php

namespace Zoop\Theme\Parser;

class ChainedParser extends AbstractParser implements ParserInterface
{

    private $parsers = [];
    private $parsedAssets = [];
    private $parsedContent = [];

    public function getParsers()
    {
        return $this->parsers;
    }

    public function setParsers($parsers)
    {
        $this->parsers = $parsers;
        return $this;
    }

    public function addParser($key, Parser $parser)
    {
        $this->parsers[$key] = $parser;
        return $this;
    }

    public function parse()
    {
        $parsers = $this->getParsers();
        $content = $this->getContent();
        /* @var $parser Parser */
        foreach ($parsers as $key => $parser) {
            $parser->setContent($content);
            $parser->parse();

            $this->addParsedAsset($key, $parser->getParsedAssets());
            $content = $parser->getParsedContent();
            $this->setParsedContent($content);
        }
    }

    public function getParsedAssets()
    {
        return $this->parsedAssets;
    }

    public function setParsedAssets($parsedAssets)
    {
        $this->parsedAssets = $parsedAssets;
        return $this;
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
        return $this;
    }

//
//    public function addParsedContent($key, $parsedContent)
//    {
//        $this->parsedContent[$key] = $parsedContent;
//    }
}
