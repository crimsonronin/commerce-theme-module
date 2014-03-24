<?php

namespace Zoop\Theme\Tokenizer\Token;

class TextToken implements TokenInterface
{
    protected $content;

    public function __construct($content)
    {
        $this->setContent($content);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
    
    public function __toString()
    {
        return $this->getContent();
    }
}
