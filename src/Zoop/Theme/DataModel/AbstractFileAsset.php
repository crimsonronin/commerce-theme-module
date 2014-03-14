<?php

namespace Zoop\Theme\DataModel;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

abstract class AbstractFileAsset extends AbstractAsset
{
    /**
     *
     * @ODM\String
     */
    protected $mime;

    public function getMime()
    {
        return $this->mime;
    }

    public function setMime($mime)
    {
        $this->mime = $mime;
    }
}
