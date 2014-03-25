<?php

namespace Zoop\Theme\Parser\Node;

use Zoop\Theme\Tokenizer\Token\TokenInterface;

abstract class AbstractNode
{
    protected $token;

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param TokenInterface $token
     */
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }
}
