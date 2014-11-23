<?php

namespace Zoop\Theme\Test\Linter;

use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Linter\ThemeLinterInterface;
use Zoop\Theme\DataModel\PrivateThemeInterface;
use Zoop\Theme\DataModel\PrivateTheme;

class ThemeLinterTest extends AbstractTest
{
    /**
     * Should return a theme with the basic theme structure
     */
    public function testLintBlankTheme()
    {
        $theme = $this->getTheme();
        $linter = $this->getThemeLinter();

        $assets = $linter->lint($theme);

        $this->assertCount(15, $assets);

        //assert asset types
    }

    /**
     * Should add missing theme assets, but retain the ones that
     * already exist within the theme
     */
    public function testLintTheme()
    {
        //TODO create a theme with existing assets
        $theme = $this->getTheme();
        $linter = $this->getThemeLinter();

        $assets = $linter->lint($theme);

        $this->assertCount(15, $assets);

        //assert asset types
    }

    /**
     * @return PrivateThemeInterface
     */
    public function getTheme()
    {
        return new PrivateTheme();
    }

    /**
     * @return ThemeLinterInterface
     */
    public function getThemeLinter()
    {
        return $this->getApplicationServiceLocator()
            ->get('zoop.commerce.theme.linter.themelinter');
    }
}
