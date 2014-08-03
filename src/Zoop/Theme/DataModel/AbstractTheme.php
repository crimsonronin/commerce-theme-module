<?php

namespace Zoop\Theme\DataModel;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @Shard\Unserializer\Ignore
     */
    protected $writeable;

    /**
     *
     * @ODM\Boolean
     * @Shard\Unserializer\Ignore
     */
    protected $deleteable;

    /**
     *
     * @ODM\Date
     * @Shard\Unserializer\Ignore
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
    protected $assets;

    /**
     *
     * @ODM\EmbedOne(targetDocument="Zoop\Common\DataModel\Image")
     */
    protected $screenshot;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isWriteable()
    {
        return $this->writeable;
    }

    /**
     * @param boolean $writeable
     */
    public function setWriteable($writeable)
    {
        $this->writeable = (bool) $writeable;
    }

    /**
     * @return boolean
     */
    public function isDeleteable()
    {
        return $this->deleteable;
    }

    /**
     * @param boolean $deleteable
     */
    public function setDeleteable($deleteable)
    {
        $this->deleteable = (bool) $deleteable;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssets()
    {
        if (empty($this->assets)) {
            $this->assets = new ArrayCollection;
        }
        return $this->assets;
    }

    /**
     * @param ArrayCollection|array $stores $assets
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

    /**
     *
     * @return Image
     */
    public function getScreenshot()
    {
        return $this->screenshot;
    }

    /**
     *
     * @param Image $screenshot
     */
    public function setScreenshot(Image $screenshot)
    {
        $this->screenshot = $screenshot;
    }
}
