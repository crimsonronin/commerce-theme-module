<?php

namespace Zoop\Theme\Parser\Content;

/**
 * This class accepts a CSS model and parses it
 * looking for additional CSS file references or Images.
 * 
 * We can then process those files separately to ensure
 * that all content lives on our CDN
 *
 * @category   CategoryName
 * @package    Zoop Commerce Theme
 * @author     Josh Stuart <josh.stuart@zoopcommerce.com>
 * 
 * @copyright  Zoop Pty Ltd
 */
class Css extends AbstractContentParser implements ParserInterface
{

    private $images = [];
    private $imports = [];

    public function __construct()
    {
        $chainedParser = new ChainedParser();

        $chainedParser->addParser($this->getImageParser());
        $chainedParser->setContent($this->getContent());
        
        $this->setParser($chainedParser);
    }

    public function parse()
    {
        $this->getParser()->parse();
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
