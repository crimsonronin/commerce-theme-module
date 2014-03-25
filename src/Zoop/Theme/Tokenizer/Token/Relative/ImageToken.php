<?php

namespace Zoop\Theme\Tokenizer\Token\Relative;

use Zoop\Theme\Tokenizer\Token\AbstractRelativeFileToken;
use Zoop\Theme\Tokenizer\Token\TokenInterface;
use Zoop\Theme\Tokenizer\Token\ImageTokenInterface;

class ImageToken extends AbstractRelativeFileToken implements TokenInterface, ImageTokenInterface
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
