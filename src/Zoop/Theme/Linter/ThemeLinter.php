<?php

namespace Zoop\Theme\Linter;

use Doctrine\Common\Collections\ArrayCollection;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\PrivateThemeInterface;
use Zoop\Theme\Helper\FileHelperTrait;
use Zoop\Theme\Helper\ThemeHelperTrait;
use Zoop\Theme\Linter\ThemeLinterInterface;

class ThemeLinter implements ThemeLinterInterface, ServiceLocatorAwareInterface
{
    use FileHelperTrait;
    use ThemeHelperTrait;
    use ServiceLocatorAwareTrait;

    protected $themeStructure;

    /**
     * @param PrivateThemeInterface $themeStructure
     */
    public function __construct(PrivateThemeInterface $themeStructure)
    {
        $this->setThemeStructure($themeStructure);
    }

    /**
     *
     * @param ThemeInterface $theme
     * @return ArrayCollection
     */
    public function lint(ThemeInterface $theme)
    {
        $themeStructure = $this->getThemeStructure();

        //get the valid assets as compared with the default theme structure
        $structureAssets = $themeStructure->getAssets()->toArray();

        //get all valid assets
        $validatedAssets = $this->getValidAssets($structureAssets, $theme->getAssets());

        //merge the valid assets with the default theme
        return $this->addMissingAssets($structureAssets, $validatedAssets);
    }

    /**
     * @return PrivateThemeInterface
     */
    public function getThemeStructure()
    {
        return $this->themeStructure;
    }

    /**
     * @param PrivateThemeInterface $themeStructure
     */
    public function setThemeStructure(PrivateThemeInterface $themeStructure)
    {
        $this->themeStructure = $themeStructure;
    }

    /**
     * 
     * @param FolderModel $structureAssets
     * @param type $assets
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
}
