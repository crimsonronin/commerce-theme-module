<?php

namespace Zoop\Theme\Creator;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\DataModel\PrivateTheme as PrivateThemeModel;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Shard\Serializer\Unserializer;

/**
 *
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
abstract class AbstractThemeCreator
{
    //put your code here
    private $theme;
    private $unserializer;
    private $themeStructure;

    /**
     *
     * @param array $assets
     * @param boolean $validate
     */
    protected function setAssets(array $assets, $validate = false)
    {
        if ($validate === false) {
            $this->getTheme()->setAssets($assets);
        } else {
            $themeStructure = $this->getThemeStructure();

            //get the valid assets as compared with the default theme structure
            $structureAsset = $themeStructure->getAssets()->toArray();

            $validatedAssets = $this->getValidAssets($structureAsset, $assets);

            $completedAssets = $this->addMissingAssets($structureAsset, $validatedAssets);

            if (!empty($completedAssets)) {
                $this->getTheme()->setAssets($completedAssets);
            }
        }
    }

    /**
     * 
     * @param ArrayCollection|array $structureAssets
     * @param ArrayCollection|array $assets
     * @param FolderModel $parent
     * @return ArrayCollection
     */
    private function addMissingAssets($structureAssets, $assets, FolderModel $parent = null)
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

            if ($structureAssets[$i] instanceof FolderModel && $foundAsset instanceof FolderModel) {
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
    private function getValidAssets($structureAssets, $assets)
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
                    if ($parent->getWritable()) {
                        $validAsset = $asset;
                    }
                }
            }

            if ($validAsset !== false) {
                if ($validAsset instanceof FolderModel) {
                    $validChildAssets = $this->getValidAssets($structureAssets, $validAsset->getAssets());

                    $validAsset->setAssets($validChildAssets);
                }
                $validAssets[] = $validAsset;
            }
        }

        return new ArrayCollection($validAssets);
    }

    /**
     * 
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
                } elseif ($assets[$i] instanceof FolderModel && $assets[$i]->getName() == $current) {
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
     *
     * @param AssetInterface $from
     * @param AssetInterface $to
     * @return AbstractThemeCreator
     */
    private function copyPermissions(AssetInterface $from, AssetInterface $to)
    {
        $to->setDeletable($from->getDeletable());
        $to->setWritable($from->getWritable());
        return $this;
    }

    /**
     *
     * @return Unserializer
     */
    public function getUnserializer()
    {
        return $this->unserializer;
    }

    /**
     *
     * @param Unserializer $unserializer
     */
    public function setUnserializer(Unserializer $unserializer)
    {
        $this->unserializer = $unserializer;
    }

    /**
     *
     * @return ThemeInterface
     */
    public function getTheme()
    {
        if (empty($this->theme)) {
            $this->setTheme(new PrivateThemeModel);
        }
        return $this->theme;
    }

    /**
     *
     * @param \Zoop\Theme\DataModel\ThemeInterface $theme
     */
    public function setTheme(ThemeInterface $theme)
    {
        $this->theme = $theme;
    }

    /**
     *
     * @return PrivateThemeModel
     */
    public function getThemeStructure()
    {
        return $this->themeStructure;
    }

    /**
     *
     * @param PrivateThemeModel $themeStructure
     */
    public function setThemeStructure(PrivateThemeModel $themeStructure)
    {
        $this->themeStructure = $themeStructure;
    }
}
