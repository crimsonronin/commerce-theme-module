<?php

namespace Zoop\Theme\Parser;

class Html extends AbstractFileParser implements ParserInterface
{

    const CSS_KEY = 'css';
    const JAVASCRIPT_KEY = 'javascript';
    const IMAGE_KEY = 'image';

    private $images = [];
    private $imports = [];

    public function parse()
    {
        $chainedParser = new ChainedParser();
        $chainedParser->addParser(self::IMAGE_KEY, $this->getImageParser());
        $chainedParser->addParser(self::CSS_KEY, $this->getCssParser());
        $chainedParser->addParser(self::JAVASCRIPT_KEY, $this->getJavascriptParser());
        $chainedParser->setContent($this->getContent());
        $chainedParser->parse();

        $this->setParsedContent($chainedParser->getParsedContent());
        $this->setParsedAssets($chainedParser->getParsedAssets());
    }

    public function getParsedCssAssets()
    {
        return $this->getParsedAssets()[self::CSS_KEY];
    }

    public function getParsedJavascriptAssets()
    {
        return $this->getParsedAssets()[self::JAVASCRIPT_KEY];
    }

    public function getParsedImageAssets()
    {
        return $this->getParsedAssets()[self::IMAGE_KEY];
    }

    public function getImageParser()
    {
        $parser = new Parser("/\<img.*src\=[\'|\"]([^\"\'\{\}]*)[\'|\"]/");
        return $parser;
    }

    public function getCssParser()
    {
        $parser = new Parser("/\<link.*href\=[\'|\"]([^\"\'\{\}]*)[\'|\"]/");
        return $parser;
    }

    public function getJavascriptParser()
    {
        $parser = new Parser("/\<script.*src\=[\'|\"]([^\"\'\{\}]*)[\'|\"]/");
        return $parser;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setImages($images)
    {
        $this->images = $images;
    }

    public function getImports()
    {
        return $this->imports;
    }

    public function setImports($imports)
    {
        $this->imports = $imports;
    }

    public function compileContent($assets)
    {
        return Compiler::compile($this->getParsedContent(), $assets);
    }

}
