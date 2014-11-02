<?php

namespace Zoop\Theme\Parser;

use \Exception;
use Zoop\Theme\Parser\Node;
use Zoop\Theme\Tokenizer\TokenStream;
use Zoop\Theme\Tokenizer\Token\TextToken;
use Zoop\Theme\Tokenizer\Token\ImageTokenInterface;
use Zoop\Theme\Tokenizer\Token\CssTokenInterface;
use Zoop\Theme\Tokenizer\Token\JavascriptTokenInterface;

/**
 * The Parser creates a NodeTree from a TokenStream.
 * This can then be used to further manipulate nodes
 * such as saving them to other locations or replacing
 * them completely.
 */
class TokenParser implements TokenParserInterface
{
    public function parse(TokenStream $tokenStream)
    {
        $nodeTree = new NodeTree;

        foreach ($tokenStream->getTokens() as $token) {
            if ($token instanceof TextToken) {
                $node = new Node\TextNode;
                $node->setToken($token);
            } elseif ($token instanceof ImageTokenInterface) {
                $node = new Node\ImageNode;
                $node->setToken($token);
            } elseif ($token instanceof CssTokenInterface) {
                $node = new Node\CssNode;
                $node->setToken($token);
            } elseif ($token instanceof JavascriptTokenInterface) {
                $node = new Node\JavascriptNode;
                $node->setToken($token);
            }

            if ($node instanceof Node\NodeInterface) {
                $nodeTree->addNode($node);
                unset($node);
            }
        }

        return $nodeTree;
    }
}
