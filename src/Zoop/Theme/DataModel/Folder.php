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
     */
    protected $sortBy = 0;

    public function getAssets()
    {
        return $this->assets;
    }

    public function setAssets(array $assets)
    {
        $this->assets = $assets;
    }

    public function addAsset($asset)
    {
        $this->assets[] = $asset;
    }

}
