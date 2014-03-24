<?php

namespace Zoop\Theme\Tokenizer;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\Tokenizer\Token\TokenInterface;

class TokenStream
{
    public function __construct()
    {
        $this->tokens = new ArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param ArrayCollection $tokens
     */
    public function setTokens(ArrayCollection $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @param TokenInterface $token
     */
    public function addToken(TokenInterface $token)
    {
        $this->tokens->add($token);
    }
}
