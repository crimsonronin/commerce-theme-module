<?php

namespace Zoop\Theme\Test\Creator;

use Zoop\Theme\Test\BaseTest;
use Zoop\Theme\DataModel\Css;

class ContentParserTest extends BaseTest
{

    public function testParseCss()
    {
        $css = new Css;
        $content = '';
        $expectedContent = '';

        $parser = new CssParser();
        $parser->addAsset($css);

        $parser->tokenize();

        $assets = $parser->getTokenizedAssets();
        $this->assertCount(2, $assets);

        //then we save them to s3
        //this replaces the tokens with the saved assets
        $parser->parse();

        $this->assertEquals($expectedContent, $css->getContent());
    }

}
