<?php

namespace Zoop\Theme\Linter;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
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
     * @return type
     */
    public function lint(ThemeInterface $theme)
    {
        $themeStructure = $this->getThemeStructure();

        //get the valid assets as compared with the default theme structure
        $structureAsset = $themeStructure->getAssets()->toArray();

        //get all valid assets
        $validatedAssets = $this->getValidAssets($structureAsset, $theme->getAssets());

        //merge the valid assets with the default theme
        return $this->addMissingAssets($structureAsset, $validatedAssets);
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
}
