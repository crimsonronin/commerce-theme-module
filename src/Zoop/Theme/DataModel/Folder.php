<?php

namespace Zoop\Theme\DataModel;

use Doctrine\Common\Collections\ArrayCollection;
//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow="*")
 * })
 */
class Folder extends AbstractAsset implements AssetInterface
{
    /**
     * @ODM\ReferenceMany(
     *      targetDocument              =   "Zoop\Theme\DataModel\AbstractAsset",
     *      discriminatorMap={
     *          "Css"                   =   "Zoop\Theme\DataModel\Css",
     *          "Folder"                =   "Zoop\Theme\DataModel\Folder",
     *          "CompressCss"           =   "Zoop\Theme\DataModel\GzippedCss",
     *          "CompressJavascript"    =   "Zoop\Theme\DataModel\GzippedJavascript",
     *          "Image"                 =   "Zoop\Theme\DataModel\Image",
     *          "Javascript"            =   "Zoop\Theme\DataModel\Javascript",
     *          "Less"                  =   "Zoop\Theme\DataModel\Less",
     *          "Template"              =   "Zoop\Theme\DataModel\Template"
     *      },
     *      discriminatorField="type",
     *      mappedBy="parent",
     *      sort={
     *          "sortBy"    =   "asc",
     *          "name"      =   "asc",
     *      }
     * )
     * @Shard\Serializer\Eager
     */
    protected $assets = [];

    /**
     *
     * @ODM\Int
     * @Shard\Unserializer\Ignore
     */
    protected $sortBy = 0;

    public function __construct()
    {
        $this->assets = new ArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param ArrayCollection|array $assets
     */
    public function setAssets($assets)
    {
        if (is_array($assets)) {
            $assets = new ArrayCollection($assets);
        }
        $this->assets = $assets;
    }

    /**
     * @param AssetInterface $asset
     */
    public function addAsset(AssetInterface $asset)
    {
        $this->getAssets()->add($asset);
    }
}
