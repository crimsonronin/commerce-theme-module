<?php

namespace Zoop\Theme\Test\Lexer;

use Zoop\Theme\Test\BaseTest;
use Zoop\Theme\Lexer\Lexer;
use Zoop\Theme\Lexer\Regex;
use Zoop\Theme\Tokenizer\Token\TextToken;
use Zoop\Theme\Tokenizer\Token\ImageTokenInterface;
use Zoop\Theme\Tokenizer\Token\CssTokenInterface;

class CssLexerTest extends BaseTest
{
    public function testTokenizeCssImageUrl()
    {
        $relativePath = __DIR__ . '/../Assets';
        $content = file_get_contents($relativePath . '/simple.css');

        $lexer = new Lexer;
        $lexer->setRelativeFilePath($relativePath);
        $lexer->addRegex(new Regex\CssImageUrlRegex);

        $tokenStream = $lexer->tokenize($content);

        $this->assertCount(20, $tokenStream->getTokens());

        $textTokens = [];
        $imageTokens = [];
        foreach ($tokenStream->getTokens() as $token) {
            if ($token instanceof TextToken) {
                $textTokens[] = $token;
            } elseif ($token instanceof ImageTokenInterface) {
                $imageTokens[] = $token;
            }
        }

        $this->assertCount(17, $textTokens);
        $this->assertCount(3, $imageTokens);
        $this->assertEquals('zoop1.jpg', (string) $imageTokens[0]);
        $this->assertEquals('zoop2.jpg', (string) $imageTokens[1]);
        $this->assertEquals('zoop3.jpg', (string) $imageTokens[2]);
    }

    public function testTokenizeCssImportUrl()
    {
        $relativePath = __DIR__ . '/../Assets';
        $content = file_get_contents($relativePath . '/simple.css');

        $lexer = new Lexer;
        $lexer->setRelativeFilePath($relativePath);
        $lexer->addRegex(new Regex\CssImportUrlRegex);

        $tokenStream = $lexer->tokenize($content);

        $this->assertCount(18, $tokenStream->getTokens());

        $textTokens = [];
        $importTokens = [];
        foreach ($tokenStream->getTokens() as $token) {
            if ($token instanceof TextToken) {
                $textTokens[] = $token;
            } elseif ($token instanceof CssTokenInterface) {
                $importTokens[] = $token;
            }
        }

        $this->assertCount(16, $textTokens);
        $this->assertCount(2, $importTokens);
        $this->assertEquals('bootstrap1.css', (string) $importTokens[0]);
        $this->assertEquals('bootstrap2.css', (string) $importTokens[1]);
    }

    public function testTokenizeCss()
    {
        $relativePath = __DIR__ . '/../Assets';
        $content = file_get_contents($relativePath . '/simple.css');

        $lexer = new Lexer;
        $lexer->setRelativeFilePath($relativePath);
        $lexer->addRegex(new Regex\CssImportUrlRegex);
        $lexer->addRegex(new Regex\CssImageUrlRegex);

        $tokenStream = $lexer->tokenize($content);

        $this->assertCount(38, $tokenStream->getTokens());

        $textTokens = [];
        $importTokens = [];
        $imageTokens = [];
        foreach ($tokenStream->getTokens() as $token) {
            if ($token instanceof TextToken) {
                $textTokens[] = $token;
            } elseif ($token instanceof CssTokenInterface) {
                $importTokens[] = $token;
            } elseif ($token instanceof ImageTokenInterface) {
                $imageTokens[] = $token;
            }
        }

        $this->assertCount(33, $textTokens);
        $this->assertCount(3, $imageTokens);
        $this->assertCount(2, $importTokens);
        $this->assertEquals('bootstrap1.css', (string) $importTokens[0]);
        $this->assertEquals('bootstrap2.css', (string) $importTokens[1]);
        $this->assertEquals('zoop1.jpg', (string) $imageTokens[0]);
        $this->assertEquals('zoop2.jpg', (string) $imageTokens[1]);
        $this->assertEquals('zoop3.jpg', (string) $imageTokens[2]);
    }
}
