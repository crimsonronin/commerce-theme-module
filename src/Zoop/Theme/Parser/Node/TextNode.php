<?php

namespace Zoop\Theme\Parser\Node;

class TextNode extends AbstractNode implements NodeInterface
{
    public function __toString()
    {
        return (string) $this->getToken();
    }
}
