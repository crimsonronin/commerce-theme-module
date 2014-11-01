<?php

namespace Zoop\Theme\Parser;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\Parser\Node\NodeInterface;
use Zoop\Theme\Parser\Node\CssNode;

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
    
    public function getCssNodes()
    {
        return $this->getNodesByType('Zoop\Theme\Parser\Node\CssNode');
    }
    
    public function getJavascriptNodes()
    {
        return $this->getNodesByType('Zoop\Theme\Parser\Node\JavascriptNode');
    }

    public function getImageNodes()
    {
        return $this->getNodesByType('Zoop\Theme\Parser\Node\ImageNode');
    }

    public function geTextNodes()
    {
        return $this->getNodesByType('Zoop\Theme\Parser\Node\TextNode');
    }
    
    protected function getNodesByType($type)
    {
        $cssNodes = new ArrayCollection;

        foreach ($this->getNodes() as $node) {
            if (is_a($node, $type)) {
                $cssNodes->add($node);
            }
        }

        return $cssNodes;
    }
}
