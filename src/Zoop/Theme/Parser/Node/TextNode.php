<?php

namespace Zoop\Theme\Parser\Node;

class TextNode extends AbstractNode implements NodeInterface
{
    public function compile()
    {
        return (string) $this->getToken();
    }
}
