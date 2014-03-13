<?php

namespace Zoop\Theme\Parser;

class Css extends AbstractFileParser implements ParserInterface
{

    private $images = [];
    private $imports = [];

    public function parse()
    {
        $chainedParser = new ChainedParser();
        $chainedParser->addParser('images', $this->getImageParser());
        $chainedParser->setContent($this->getContent());
        $chainedParser->parse();

        $this->setParsedContent($chainedParser->getParsedContent());
        $this->setParsedAssets($chainedParser->getParsedAssets());
    }

    public function getImageParser()
    {
        $parser = new Parser("/url\([\'|\"]*(.*)[\'|\"]*\)/");
        return $parser;
    }

    public function getImportParser()
    {
        $parser = new Parser("/\@import\([\'|\"]*(.*)[\'|\"]*\)/");
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

}
