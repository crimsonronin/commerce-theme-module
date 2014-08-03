<?php

namespace Zoop\Theme\DataModel;

interface ThemeInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return boolean
     */
    public function isWriteable();

    /**
     * @param boolean $writeable
     */
    public function setWriteable($writeable);

    /**
     * @return boolean
     */
    public function isDeleteable();

    /**
     * @param boolean $deleteable
     */
    public function setDeleteable($deleteable);

    /**
     * @return ArrayCollection
     */
    public function getAssets();

    /**
     * @param ArrayCollection|array $stores
     */
    public function setAssets($assets);

    /**
     * @param AssetInterface $asset
     */
    public function addAsset(AssetInterface $asset);
}
