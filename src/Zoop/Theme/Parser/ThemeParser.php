<?php

namespace Zoop\Theme\Parser;

use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\Parser\ThemeParserInterface;
use Zoop\Theme\Lexer\LexerInterface;
use Zoop\Theme\Linter\ThemeLinterInterface;
use Zoop\Theme\Parser\TokenParserInterface;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class ThemeParser implements ThemeParserInterface
{
    protected $assetParser;
    protected $contentParser;
    protected $lexer;
    protected $themeLinter;
    protected $tokenParser;
    protected $canLint = true;
    protected $canParseContent = false;
    protected $canParseAssets = false;

    public function __construct(
        $assetParser,
        $contentParser,
        LexerInterface $lexer,
        ThemeLinterInterface $themeLinter,
        TokenParserInterface $tokenParser
    ) {
        $this->setAssetParser($assetParser);
        $this->setContentParser($contentParser);
        $this->setLexer($lexer);
        $this->setThemeLinter($themeLinter);
        $this->setTokenParser($tokenParser);
    }

    /**
     * {@inheritdoc}
     */
    public function parse(ThemeInterface $theme)
    {
        // lint theme
        if ($this->canLint()) {
            $assets = $this->getThemeLinter()
                ->lint($theme);
            $theme->setAssets($assets);
        }

        // parse content
        if ($this->canParseContent()) {

        }

        // parse assets
        if ($this->canParseAssets()) {

        }

        // compile
    }

    /**
     * @param array $assets
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function parseEmbeddedAssets(array $assets)
    {
        $lexer = $this->getLexer();
        $parser = $this->getParser();

        foreach ($assets as $asset) {
            if ($asset instanceof ContentAssetInterface) {
                $tokenStream = $lexer->tokenize($asset->getContent());

                $nodeTree = $parser->parse($tokenStream);

                $cssNodes = $nodeTree->getCssNodes();
                $jsNodes = $nodeTree->getJavascriptNodes();
                $imgNodes = $nodeTree->getImageNodes();
            }
        }
    }

    /**
     * @return ThemeLinterInterface
     */
    public function getThemeLinter()
    {
        return $this->themeLinter;
    }

    /**
     * @param ThemeLinterInterface $themeLinter
     */
    public function setThemeLinter(ThemeLinterInterface $themeLinter)
    {
        $this->themeLinter = $themeLinter;
    }

    /**
     * @return LexerInterface
     */
    public function getLexer()
    {
        return $this->lexer;
    }

    /**
     * @param LexerInterface $lexer
     */
    public function setLexer(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    public function getContentParser()
    {
        return $this->contentParser;
    }

    public function setContentParser($contentParser)
    {
        $this->contentParser = $contentParser;
    }

    public function getAssetParser()
    {
        return $this->assetParser;
    }

    public function setAssetParser($assetParser)
    {
        $this->assetParser = $assetParser;
    }

    /**
     * @return TokenParserInterface
     */
    public function getTokenParser()
    {
        return $this->tokenParser;
    }

    /**
     * @param TokenParserInterface $tokenParser
     */
    public function setTokenParser(TokenParserInterface $tokenParser)
    {
        $this->tokenParser = $tokenParser;
    }

    /**
     * @return boolean
     */
    public function canLint()
    {
        return (boolean) $this->canLint;
    }

    /**
     * @return boolean
     */
    public function canParseContent()
    {
        return (boolean) $this->canParseContent;
    }

    /**
     * @return boolean
     */
    public function canParseAssets()
    {
        return (boolean) $this->canParseAssets;
    }

    /**
     * @param boolean $canLint
     */
    public function setCanLint($canLint)
    {
        $this->canLint = $canLint;
    }

    /**
     * @param boolean $canParseContent
     */
    public function setCanParseContent($canParseContent)
    {
        $this->canParseContent = (boolean) $canParseContent;
    }

    /**
     * @param boolean $canParseAssets
     */
    public function setCanParseAssets($canParseAssets)
    {
        $this->canParseAssets = (boolean) $canParseAssets;
    }
}
