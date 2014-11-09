<?php

namespace Zoop\Theme\Test\Parser;

use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Lexer\Lexer;
use Zoop\Theme\Lexer\Regex;
use Zoop\Theme\Parser\TokenParser;

class TokenParserTest extends AbstractTest
{
    protected static $tempDir;

    public function testParseSimpleContent()
    {
        $relativePath = __DIR__ . '/../Assets';
        $content = file_get_contents($relativePath . '/simple.html');

        $lexer = new Lexer;
        $lexer->setRelativeFilePath($relativePath);
        $lexer->addRegex(new Regex\HtmlCssUrlRegex);
        $lexer->addRegex(new Regex\HtmlImageUrlRegex);
        $lexer->addRegex(new Regex\HtmlJavascriptUrlRegex);

        $tokenStream = $lexer->tokenize($content);

        $parser = new TokenParser;
        $nodeTree = $parser->parse($tokenStream);

        $nodes = $nodeTree->getNodes();
        $this->assertCount(53, $nodes);

        //ensure that there are css nodes
        $cssNodes = $nodeTree->getCssNodes();
        $this->assertCount(2, $cssNodes);

        //ensure that there are js nodes
        $jsNodes = $nodeTree->getJavascriptNodes();
        $this->assertCount(1, $jsNodes);

        //ensure that there are image nodes
        $imgNodes = $nodeTree->getImageNodes();
        $this->assertCount(1, $imgNodes);
    }
}
