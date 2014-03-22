<?php

namespace Zoop\Theme\DataModel;

use Zoop\Common\DataModel\Image;
use Zoop\Shard\Stamp\DataModel\CreatedOnTrait;
use Zoop\Shard\Stamp\DataModel\UpdatedOnTrait;
use Zoop\Shard\SoftDelete\DataModel\SoftDeleteableTrait;
//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document(collection="Theme")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField("type")
 * @ODM\DiscriminatorMap({
 *     "PrivateTheme"    = "Zoop\Theme\DataModel\PrivateTheme",
 *     "ZoopTheme"       = "Zoop\Theme\DataModel\ZoopTheme",
 *     "SharedTheme"     = "Zoop\Theme\DataModel\SharedTheme"
 * })
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow="*")
 * })
 */
abstract class AbstractTheme
{
    use CreatedOnTrait;
    use UpdatedOnTrait;
    use SoftDeleteableTrait;

    /**
     * @ODM\Id
     */
    protected $id;

    /**
     *
     * @ODM\String
     */
    protected $name;

    /**
     *
     * @ODM\Boolean
     */
    protected $writeable;

    /**
     *
     * @ODM\Boolean
     */
    protected $deleteable;

    /**
     *
     * @ODM\Date
     */
    protected $createdOn;

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
     *          "name"      =   "asc"
     *      }
     * )
     * @Shard\Serializer\Eager
     */
    protected $assets = [];

    /**
     *
     * @ODM\EmbedOne(targetDocument="Zoop\Common\DataModel\Image")
     */
    protected $screenshot;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getWriteable()
    {
        return $this->writeable;
    }

    public function setWriteable($writeable)
    {
        $this->writeable = (bool) $writeable;
    }

    public function getDeleteable()
    {
        return $this->deleteable;
    }

    public function setDeleteable($deleteable)
    {
        $this->deleteable = (bool) $deleteable;
    }

    public function getAssets()
    {
        return $this->assets;
    }

    public function setAssets(array $assets)
    {
        $this->assets = $assets;
    }

    public function addAsset(AssetInterface $asset)
    {
        $this->assets[] = $asset;
    }

    public function getScreenshot()
    {
        return $this->screenshot;
    }

    public function setScreenshot(Image $screenshot)
    {
        $this->screenshot = $screenshot;
    }

}
