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
class ZoopTheme extends AbstractTheme implements ThemeInterface
{
    /**
     *
     * @ODM\Boolean
     * @Shard\Unserializer\Ignore
     */
    protected $writeable = false;

    /**
     *
     * @ODM\Boolean
     * @Shard\Unserializer\Ignore
     */
    protected $deleteable = false;
}
