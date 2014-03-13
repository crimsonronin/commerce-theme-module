<?php

namespace Zoop\Theme\Extension\TokenParser;

use \Twig_TokenParser;
use \Twig_Token;
use Zoop\Theme\Extension\Node\Collection\Get AS CollectionGetNode;

class Get extends Twig_TokenParser
{

    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        $stream->expect(Twig_Token::NAME_TYPE, 'from');
        $seq = $parser->getExpressionParser()->parseExpression();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new CollectionGetNode($seq, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'get';
    }

}
