<?php

namespace Zoop\Theme\Parser\Content;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\DataModel\AssetInterface;

abstract class AbstractContentParser
{
    protected $assets;
    protected $tokenizers;

    /**
     * @return ArrayCollection
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param ArrayCollection $assets
     */
    public function setAssets(ArrayCollection $assets)
    {
        $this->assets = $assets;
    }

    /**
     * @param AssetInterface $asset
     */
    public function addAsset(AssetInterface $asset)
    {
        $this->assets->add($asset);
    }
}
