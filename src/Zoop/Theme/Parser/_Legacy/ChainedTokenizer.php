<?php

namespace Zoop\Theme\Parser;

class ChainedTokenizer
{
    protected $tokenizers;

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
}
