<?php

namespace Zoop\Theme\Test\Parser;

use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Parser\DirectoryParser;
use Zoop\Theme\Helper\FileHelperTrait;
class DirectoryParserTest extends AbstractTest
{
    use FileHelperTrait;

    protected $tempDir;

    public function setUp()
    {
        parent::setUp();
        $this->setTempDir(__DIR__ . '/../../../../../data/temp');
    }

    public function testParseSimpleTheme()
    {
        $tempDirName = 'simple-temp';
        $simpleTheme = __DIR__ . '/../Assets/simple-theme.zip';
        $tempDir =  $this->createDirectory($this->getTempDir(), $tempDirName);

        $assets = [];

        //unzip
        if($this->unzip($simpleTheme, $tempDir)) {
            $parser = $this->getDirectoryParser();
            $assets = $parser->parse($tempDir);
        }

        $this->assertCount(1, $assets);
        $this->assertInstanceOf('Zoop\Theme\DataModel\ContentAssetInterface', $assets[0]);
        $this->deleteTempDirectory($tempDir);
    }

    public function testParseComplexTheme()
    {
        $tempDirName = 'complex-temp';
        $complexTheme = __DIR__ . '/../Assets/complex-theme.zip';
        $tempDir =  $this->createDirectory($this->getTempDir(), $tempDirName);

        $assets = [];

        //unzip
        if($this->unzip($complexTheme, $tempDir)) {
            $parser = $this->getDirectoryParser();
            $assets = $parser->parse($tempDir);
        }

        $this->assertCount(4, $assets);
        $this->assertInstanceOf('Zoop\Theme\DataModel\FolderAssetInterface', $assets[0]);
        $this->assertInstanceOf('Zoop\Theme\DataModel\ContentAssetInterface', $assets[1]);
        $this->assertInstanceOf('Zoop\Theme\DataModel\FolderAssetInterface', $assets[2]);
        $this->assertInstanceOf('Zoop\Theme\DataModel\FolderAssetInterface', $assets[3]);
        $this->deleteTempDirectory($tempDir);
    }

    /**
     * @return DirectoryParser
     */
    protected function getDirectoryParser()
    {
        return $this->getApplicationServiceLocator()
            ->get('zoop.commerce.theme.parser.directoryparser');
    }

    /**
     * @return string
     */
    protected function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @param string $tempDir
     */
    protected function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;
    }
}
