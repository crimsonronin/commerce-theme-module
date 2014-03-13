<?php

namespace Zoop\Theme\Test\Creator;

use \SplFileInfo;
use Zoop\Theme\Test\BaseTest;

class CreatorTest extends BaseTest
{
    public function testSimpleThemeImport()
    {
        $creator = $this->getApplicationServiceLocator()
            ->get('zoop.commerce.theme.creator.import');
        /* @var $creator \Zoop\Theme\Creator\ThemeCreatorImport */

        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/Simple.zip');

        $theme = $creator->create($uploadedFile);
        $assets = $theme->getAssets();

        $this->assertInstanceOf('Zoop\Theme\DataModel\AbstractTheme', $theme);
        $this->assertCount(15, $assets);
        $this->assertInstanceOf('Zoop\Theme\DataModel\AbstractAsset', $assets[0]);

        //check the index content which should be the only non-empty file
        /* @var $asset \Zoop\Theme\DataModel\AbstractAsset */
        foreach ($assets as $asset) {
            if ($asset->getName() === 'index.html') {
                $this->assertInstanceOf('Zoop\Theme\DataModel\Template', $asset);
                $this->assertNotEmpty($asset->getContent());
            }
        }
    }
}
