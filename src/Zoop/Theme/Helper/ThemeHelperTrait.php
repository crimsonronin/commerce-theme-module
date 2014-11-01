<?php

namespace Zoop\Theme\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Folder;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
trait ThemeHelperTrait
{
    /**
     * @param ArrayCollection|array $structureAssets
     * @param ArrayCollection|array $assets
     * @param Folder $parent
     * @return ArrayCollection
     */
    protected function addMissingAssets($structureAssets, $assets, Folder $parent = null)
    {
        $completedAssets = [];
        for ($i = 0; $i < count($structureAssets); $i++) {
            if (!is_null($parent)) {
                $pathname = substr($structureAssets[$i]->getPathname(), strlen($parent->getPathname() . '/'));
            } else {
                $pathname = $structureAssets[$i]->getPathname();
            }

            $foundAsset = $this->findAsset($assets, $pathname);

            if (!$foundAsset) {
                $foundAsset = $structureAssets[$i];
            }

            if ($structureAssets[$i] instanceof Folder && $foundAsset instanceof Folder) {
                $childStructureAssets = $structureAssets[$i]->getAssets()->toArray();
                $childAssets = $foundAsset->getAssets();

                if (is_object($childAssets)) {
                    $childAssets = $childAssets->toArray();
                }

                //get children
                if (!empty($childStructureAssets)) {
                    $completedChildAssets = $this->addMissingAssets($childStructureAssets, $childAssets, $foundAsset);
                    $foundAsset->setAssets($completedChildAssets);
                } else {
                    $foundAsset->setAssets($childAssets);
                }
            }

            if (!is_null($parent)) {
                $foundAsset->setParent($parent);
            }

            $completedAssets[] = $foundAsset;
        }
        return new ArrayCollection($completedAssets);
    }

    /**
     * Removes all assets that are not in the correct structure.
     * It also sets the correct permissions on assets according to the default
     * structure.
     *
     * @param ArrayCollection|array $structureAssets
     * @param ArrayCollection|array $assets
     * @return ArrayCollection
     */
    protected function getValidAssets($structureAssets, $assets)
    {
        $validAssets = [];
        /* @var $asset AssetInterface */
        foreach ($assets as $asset) {
            $validAsset = false;
            $pathname = $asset->getPathname();

            $structureAsset = $this->findAsset($structureAssets, $pathname);

            if (!empty($structureAsset)) {
                //copy permissions
                $this->copyPermissions($structureAsset, $asset);
                $validAsset = $asset;
            } else {
                //check if the parent perms allow this asset
                $parent = $asset->getParent();
                if (!empty($parent)) {
                    if ($parent->isWritable()) {
                        $validAsset = $asset;
                    }
                }
            }

            if ($validAsset !== false) {
                if ($validAsset instanceof Folder) {
                    $validChildAssets = $this->getValidAssets($structureAssets, $validAsset->getAssets());

                    $validAsset->setAssets($validChildAssets);
                }
                $validAssets[] = $validAsset;
            }
        }

        return new ArrayCollection($validAssets);
    }

    /**
     * @param ArrayCollection|array $assets
     * @param string $pathname
     * @return AssetInterface|boolean
     */
    public function findAsset($assets, $pathname)
    {
        $all = explode("/", $pathname);
        $current = $all[0];

        if (!empty($assets)) {
            for ($i = 0; $i < count($assets); $i++) {
                if ($assets[$i]->getName() == $current && count($all) == 1) {
                    return $assets[$i];
                } elseif ($assets[$i] instanceof Folder && $assets[$i]->getName() == $current) {
                    $childAssets = $assets[$i]->getAssets()->toArray();

                    //remove the first path
                    array_shift($all);

                    $childAsset = $this->findAsset($childAssets, implode('/', $all));
                    if ($childAsset !== false) {
                        return $childAsset;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param AssetInterface $from
     * @param AssetInterface $to
     * @return AbstractThemeCreator
     */
    protected function copyPermissions(AssetInterface $from, AssetInterface $to)
    {
        $to->setDeletable($from->isDeletable());
        $to->setWritable($from->isWritable());
        return $this;
    }
}
