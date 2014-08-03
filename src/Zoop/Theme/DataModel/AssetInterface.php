<?php

namespace Zoop\Theme\DataModel;

interface AssetInterface
{
    public function getId();

    public function getName();

    public function setName($name);

    public function getParent();

    public function setParent($parent);

    public function isWritable();

    public function isDeletable();

    public function setWritable($writable);

    public function setDeletable($deletable);

    public function getPath();

    public function setPath($path);

    public function getPathName();

    public function setPathName($pathname);

    public function getTheme();

    public function setTheme(ThemeInterface $theme);
}
