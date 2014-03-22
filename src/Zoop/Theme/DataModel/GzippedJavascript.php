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
class GzippedJavascript extends AbstractFileAsset implements AssetInterface
{
    /**
     *
     * @ODM\String
     */
    protected $src;

    /**
     *
     * @ODM\String
     */
    protected $mime = 'application/javascript';

    public function getSrc()
    {
        return $this->src;
    }

    public function setSrc($src)
    {
        $this->src = $src;
    }
}
