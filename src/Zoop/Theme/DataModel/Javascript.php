<?php

namespace Zoop\Theme\DataModel;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow="*")
 * })
 */
class Javascript extends AbstractContentAsset implements AssetInterface
{
    /**
     *
     * @ODM\String
     */
    protected $src;

    /**
     *
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     *
     * @param string $src
     */
    public function setSrc($src)
    {
        $this->src = $src;
    }
}
