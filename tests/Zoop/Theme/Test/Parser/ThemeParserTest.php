<?php

namespace Zoop\Theme\Test\Parser;

use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Parser\ThemeParserInterface;
use Zoop\Theme\DataModel\PrivateThemeInterface;
use Zoop\Theme\DataModel\PrivateTheme;

class ThemeParserTest extends AbstractTest
{
    public function testParseSimpleContent()
    {
        $theme = $this->getTheme();
        $themeParser = $this->getThemeParser();

        //parse the theme
        $themeParser->parse($theme);
        $assets = $theme->getAssets();

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
     * @return ThemeParserInterface
     */
    public function getThemeParser()
    {
        return $this->getApplicationServiceLocator()
            ->get('zoop.commerce.theme.parser.themeparser');
    }
}
