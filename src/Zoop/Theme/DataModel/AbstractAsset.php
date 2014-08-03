<?php

namespace Zoop\Theme\DataModel;

use Zoop\Shard\Stamp\DataModel\CreatedOnTrait;
use Zoop\Shard\Stamp\DataModel\UpdatedOnTrait;
use Zoop\Shard\SoftDelete\DataModel\SoftDeleteableTrait;
//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document(collection="ThemeAsset")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField("type")
 * @ODM\DiscriminatorMap({
 *     "Css"                   =   "Zoop\Theme\DataModel\Css",
 *     "Folder"                =   "Zoop\Theme\DataModel\Folder",
 *     "CompressCss"           =   "Zoop\Theme\DataModel\GzippedCss",
 *     "CompressJavascript"    =   "Zoop\Theme\DataModel\GzippedJavascript",
 *     "Image"                 =   "Zoop\Theme\DataModel\Image",
 *     "Javascript"            =   "Zoop\Theme\DataModel\Javascript",
 *     "Less"                  =   "Zoop\Theme\DataModel\Less",
 *     "Template"              =   "Zoop\Theme\DataModel\Template"
 * })
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow="*")
 * })
 */
abstract class AbstractAsset
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
     * @ODM\String
     * @ODM\Index
     */
    protected $pathname;

    /**
     *
     * @ODM\String
     */
    protected $path;

    /**
     *
     * @ODM\ReferenceOne(
     *      discriminatorMap={
     *          "Folder"        = "Zoop\Theme\DataModel\Folder",
     *          "PrivateTheme"  = "Zoop\Theme\DataModel\PrivateTheme",
     *          "SharedTheme"   = "Zoop\Theme\DataModel\SharedTheme",
     *          "ZoopTheme"     = "Zoop\Theme\DataModel\ZoopTheme"
     *      },
     *      discriminatorField="type",
     *      inversedBy="assets"
     * )
     * @Shard\Serializer\Lazy
     */
    protected $parent;

    /**
     *
     * @ODM\ReferenceOne(
     *      discriminatorMap={
     *          "PrivateTheme"  = "Zoop\Theme\DataModel\PrivateTheme",
     *          "SharedTheme"   = "Zoop\Theme\DataModel\SharedTheme",
     *          "ZoopTheme"     = "Zoop\Theme\DataModel\ZoopTheme"
     *      },
     *      discriminatorField="type"
     * )
     * @Shard\Serializer\Lazy
     */
    protected $theme;

    /**
     *
     * @ODM\Boolean
     * @Shard\Unserializer\Ignore
     */
    protected $writable = true;

    /**
     *
     * @ODM\Boolean
     * @Shard\Unserializer\Ignore
     */
    protected $deletable = true;

    /**
     *
     * @ODM\Date
     * @Shard\Unserializer\Ignore
     */
    protected $createdOn;

    /**
     *
     * @ODM\Date
     * @Shard\Unserializer\Ignore
     */
    protected $lastModified;

    /**
     *
     * @ODM\Int
     */
    protected $sortBy = 1;

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
        $this->name = mb_convert_encoding($name, 'UTF-8');
    }

    /**
     *
     * @return ThemeInterface|AssetInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     *
     * @param ThemeInterface|AssetInterface $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setTheme(ThemeInterface $theme)
    {
        $this->theme = $theme;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function isDeletable()
    {
        return $this->deletable;
    }

    public function setWritable($writable)
    {
        $this->writable = (bool) $writable;
    }

    public function setDeletable($deletable)
    {
        $this->deletable = (bool) $deletable;
    }

    public function getPathname()
    {
        return $this->pathname;
    }

    public function setPathname($pathname)
    {
        $this->pathname = mb_convert_encoding($pathname, 'UTF-8');
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = mb_convert_encoding($path, 'UTF-8');
    }
}
