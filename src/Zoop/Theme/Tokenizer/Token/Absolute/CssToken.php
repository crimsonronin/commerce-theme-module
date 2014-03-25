<?php

namespace Zoop\Theme\Tokenizer\Token\Absolute;

use Zoop\Theme\Tokenizer\Token\AbstractAbsoluteFileToken;
use Zoop\Theme\Tokenizer\Token\TokenInterface;
use Zoop\Theme\Tokenizer\Token\CssTokenInterface;

class CssToken extends AbstractAbsoluteFileToken implements TokenInterface, CssTokenInterface
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
