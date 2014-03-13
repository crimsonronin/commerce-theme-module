<?php

namespace Zoop\Theme\DataModel;

interface ThemeInterface
{
    public function getId();

    public function getName();

    public function setName($name);

    public function getWriteable();

    public function setWriteable($writeable);

    public function getDeleteable();

    public function setDeleteable($deleteable);

    public function getAssets();

    public function setAssets(array $assets);

    public function addAsset(AssetInterface $asset);
}
