<?php

namespace Zoop\Theme\Test\Creator;

use \SplFileInfo;
use Zoop\Theme\Test\AbstractTest;

class CreatorTest extends AbstractTest
{
    public function testInvalidFileImport()
    {
        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/zoop.jpg');

        $this->setExpectedException('Exception');
        $theme = self::getThemeCreatorImport()->import($uploadedFile);
    }

    public function testInvalidFileSizeImport()
    {
        $creator = self::getThemeCreatorImport();
        $creator->setMaxFileUploadSize(1024);

        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/complex-theme.zip');

        $this->setExpectedException('Exception');
        $theme = $creator->import($uploadedFile);
    }

    public function testValidSimpleThemeImport()
    {
        $creator = self::getThemeCreatorImport();
        $creator->setMaxFileUploadSize(1024 * 1024 * 20);

        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/simple-theme.zip');

        $theme = $creator->import($uploadedFile);
        $assets = $theme->getAssets();

        $this->assertInstanceOf('Zoop\Theme\DataModel\AbstractTheme', $theme);
        $this->assertEquals('simple-theme', $theme->getName());
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

    public function testValidComplexThemeImport()
    {
        $creator = $this->getApplicationServiceLocator()
                ->get('zoop.commerce.theme.creator.import');
        /* @var $creator \Zoop\Theme\Creator\ThemeCreatorImport */

        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/complex-theme.zip');

        $theme = $creator->import($uploadedFile);
        $assets = $theme->getAssets();

        $this->assertInstanceOf('Zoop\Theme\DataModel\AbstractTheme', $theme);
        $this->assertEquals('complex-theme', $theme->getName());
        $this->assertCount(15, $assets);
        $this->assertInstanceOf('Zoop\Theme\DataModel\AbstractAsset', $assets[0]);

        //maybe need some more tests to traverse the child assets
    }
}
