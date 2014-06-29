<?php

namespace Zoop\Theme\Test\Serializer;

use \Exception;
use \SplFileInfo;
use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Serializer\Asset\Unserializer;

class UnserializerTest extends AbstractTest
{
    private $assetUnserializer;

    public function testCssFile()
    {
        $file = new SplFileInfo(__DIR__ . '/../Assets/bootstrap.css');
        $asset = $this->getAssetUnserializer()->fromFile($file);

        $this->assertInstanceOf('Zoop\Theme\DataModel\Css', $asset);
        $this->assertEquals($asset->getName(), 'bootstrap.css');
        $this->assertNotEmpty($asset->getContent());
    }

    public function testCssGzipFile()
    {
        $file = new SplFileInfo(__DIR__ . '/../Assets/bootstrap.min.css');
        $asset = $this->getAssetUnserializer()->fromFile($file);

        $this->assertInstanceOf('Zoop\Theme\DataModel\GzippedCss', $asset);
        $this->assertEquals($asset->getName(), 'bootstrap.min.css');
        $this->assertEquals($asset->getMime(), 'text/css');
    }

    public function testJavascriptFile()
    {
        $file = new SplFileInfo(__DIR__ . '/../Assets/jquery-2.1.0.js');
        $asset = $this->getAssetUnserializer()->fromFile($file);

        $this->assertInstanceOf('Zoop\Theme\DataModel\Javascript', $asset);
        $this->assertEquals($asset->getName(), 'jquery-2.1.0.js');
        $this->assertNotEmpty($asset->getContent());
    }

    public function testJavascriptGzipFile()
    {
        $file = new SplFileInfo(__DIR__ . '/../Assets/jquery-2.1.0.min.js');
        $asset = $this->getAssetUnserializer()->fromFile($file);

        $this->assertInstanceOf('Zoop\Theme\DataModel\GzippedJavascript', $asset);
        $this->assertEquals($asset->getName(), 'jquery-2.1.0.min.js');
        $this->assertEquals($asset->getMime(), 'application/javascript');
    }

    public function testLessFile()
    {
        $file = new SplFileInfo(__DIR__ . '/../Assets/bootstrap.less');
        $asset = $this->getAssetUnserializer()->fromFile($file);

        $this->assertInstanceOf('Zoop\Theme\DataModel\Less', $asset);
        $this->assertEquals($asset->getName(), 'bootstrap.less');
        $this->assertNotEmpty($asset->getContent());
    }

    public function testImageFile()
    {
        //JPG
        $file = new SplFileInfo(__DIR__ . '/../Assets/zoop.jpg');
        $asset = $this->getAssetUnserializer()->fromFile($file);

        $this->assertInstanceOf('Zoop\Theme\DataModel\Image', $asset);
        $this->assertEquals($asset->getExtension(), 'jpg');
        $this->assertEquals($asset->getMime(), 'image/jpeg');
        $this->assertEquals($asset->getName(), 'zoop.jpg');

        //PNG
        $file = new SplFileInfo(__DIR__ . '/../Assets/zoop.png');
        $asset = $this->getAssetUnserializer()->fromFile($file);

        $this->assertInstanceOf('Zoop\Theme\DataModel\Image', $asset);
        $this->assertEquals($asset->getExtension(), 'png');
        $this->assertEquals($asset->getMime(), 'image/png');
        $this->assertEquals($asset->getName(), 'zoop.png');
    }

    public function testHtmlFile()
    {
        $file = new SplFileInfo(__DIR__ . '/../Assets/bootstrap.html');
        $asset = $this->getAssetUnserializer()->fromFile($file);

        $this->assertInstanceOf('Zoop\Theme\DataModel\Template', $asset);
        $this->assertEquals($asset->getName(), 'bootstrap.html');
        $this->assertNotEmpty($asset->getContent());
    }

    public function testInvalidFile()
    {
        $file = new SplFileInfo(__DIR__ . '/../Assets/zoop.pdf');

        $this->setExpectedException('Exception');
        $asset = $this->getAssetUnserializer()->fromFile($file);
    }

    /**
     * @return Unserializer
     */
    private function getAssetUnserializer()
    {
        if (!isset($this->assetUnserializer)) {
            $this->assetUnserializer = $this->getApplicationServiceLocator()
                    ->get('zoop.commerce.theme.serializer.asset.unserializer');
        }
        return $this->assetUnserializer;
    }
}
