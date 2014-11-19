<?php

namespace Zoop\Theme\Test\Parser;

use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Parser\ThemeParserInterface;
use Zoop\Theme\DataModel\PrivateThemeInterface;

class ThemeParserTest extends AbstractTest
{
    public function testParseSimpleContent()
    {
        $theme = $this->getTheme();
        $themeParser = $this->getThemeParser();
        $themeParser->parse($theme);
    }
    
    /**
     * @return PrivateThemeInterface
     */
    public function getTheme()
    {
        return $this->getApplicationServiceLocator()
            ->get('zoop.commerce.theme.structure');
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
