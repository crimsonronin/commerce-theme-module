<?php

namespace Zoop\Theme\Test\Creator;

use \Exception;
use \SplFileInfo;
use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Creator\ThemeCreatorImport;

class CreatorTest extends AbstractTest
{
    private $creator;

    public function testInvalidFileImport()
    {
        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/zoop.jpg');

        $this->setExpectedException('Exception');
        $theme = $this->getThemeCreatorImport()->import($uploadedFile);
    }

    public function testInvalidFileSizeImport()
    {
        $creator = $this->getThemeCreatorImport();

        //set lower file limit to 1kb
        $creator->setMaxFileUploadSize(1024);
        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/simple-theme.zip');

        $this->setExpectedException('Exception');
        $theme = $creator->import($uploadedFile);
    }

    public function testValidSimpleThemeImport()
    {
        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/simple-theme.zip');

        $theme = $this->getThemeCreatorImport()->import($uploadedFile);
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

    /**
     * @return ThemeCreatorImport
     */
    private function getThemeCreatorImport()
    {
        if (!isset($this->creator)) {
            $this->creator = $this->getApplicationServiceLocator()
                ->get('zoop.commerce.theme.creator.import');
        }
        return $this->creator;
    }
}
