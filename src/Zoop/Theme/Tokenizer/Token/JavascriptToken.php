<?php

namespace Zoop\Theme\Tokenizer\Token;

class JavascriptToken extends AbstractFileToken implements TokenInterface
{
    public function __construct($url)
    {
        $this->setUrl($url);
    }

    public function __toString()
    {
        return $this->getUrl();
    }
}
