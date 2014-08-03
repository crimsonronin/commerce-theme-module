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
class GzippedCss extends AbstractFileAsset implements AssetInterface
{
    /**
     *
     * @ODM\String
     */
    protected $href;

    /**
     *
     * @ODM\String
     * @Shard\Unserializer\Ignore
     */
    protected $mime = 'text/css';

    /**
     * @return string
     * 
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param string $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }
}
