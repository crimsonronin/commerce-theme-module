<?php

namespace Zoop\Theme\Lexer\Regex;

abstract class AbstractRegex
{
    protected $tokenRegex;

    /**
     * @return string
     */
    public function getTokenRegex()
    {
        return $this->tokenRegex;
    }

    /**
     * @param string $tokenRegex
     */
    public function setTokenRegex($tokenRegex)
    {
        $this->tokenRegex = $tokenRegex;
    }

    public function __toString()
    {
        return $this->getTokenRegex();
    }
}
