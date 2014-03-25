<?php

namespace Zoop\Theme\Parser;

use Zoop\Theme\Tokenizer\TokenStream;

interface ParserInterface
{
    public function parse(TokenStream $tokenStream);
}
