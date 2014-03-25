<?php

namespace Zoop\Theme\Parser;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\Parser\Node\NodeInterface;

class NodeTree
{
    protected $nodes;

    public function __construct()
    {
        $this->nodes = new ArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @param ArrayCollection $nodes
     */
    public function setNodes(ArrayCollection $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * @param NodeInterface $node
     */
    public function addNode(NodeInterface $node)
    {
        $this->nodes->add($node);
    }
}
