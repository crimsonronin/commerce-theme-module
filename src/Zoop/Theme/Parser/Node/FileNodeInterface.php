<?php

namespace Zoop\Theme\Parser\Node;

use Zoop\Theme\DataModel\AssetInterface;

interface FileNodeInterface
{
    public function setModel(AssetInterface $model);

    public function getModel();
}
