<?php

namespace Zoop\Theme\Test\Creator;

use \SplFileInfo;
use Zend\Stdlib\Parameters;
use Zend\Mvc\MvcEvent;
use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Creator\FileImportCreator;
use Zoop\Theme\DataModel\PrivateTheme;

class FileImportCreatorTest extends AbstractTest
{
    /**
     * Should fail to import due to incorrect file type
     */
    public function testInvalidFileImport()
    {
        $mvcEvent = $this->getMvcEventMock(__DIR__ . '/../Assets/zoop.jpg');

        $this->setExpectedException('Exception');
        $this->getFileImportCreator()
            ->create($mvcEvent);
    }

    /**
     * Should fail to import to large file size
     */
    public function testInvalidFileSizeImport()
    {
        $creator = $this->getFileImportCreator();
        $creator->setMaxFileUploadSize(1024);
        
        $mvcEvent = $this->getMvcEventMock(__DIR__ . '/../Assets/complex-theme.zip');

        $this->setExpectedException('Exception');
        $creator->create($mvcEvent);
    }

    /**
     * Should import a simple theme zip file and create a PrivateTheme model
     */
    public function testValidSimpleThemeImport()
    {
        $creator = $this->getFileImportCreator();
        $creator->setMaxFileUploadSize(1024 * 1024 * 20);

        $mvcEvent = $this->getMvcEventMock(__DIR__ . '/../Assets/simple-theme.zip');

        $result = $creator->create($mvcEvent);
        $this->assertInstanceOf('Zoop\ShardModule\Controller\Result', $result);

        $theme = $result->getModel();
        $assets = $theme->getAssets();

        $this->assertInstanceOf('Zoop\Theme\DataModel\PrivateTheme', $theme);
        $this->assertEquals('simple-theme', $theme->getName());
        $this->assertCount(1, $assets);
        $this->assertInstanceOf('Zoop\Theme\DataModel\AssetInterface', $assets[0]);

        //check the index content which should be the only non-empty file
        /* @var $asset \Zoop\Theme\DataModel\AssetInterface */
        foreach ($assets as $asset) {
            if ($asset->getName() === 'index.html') {
                $this->assertInstanceOf('Zoop\Theme\DataModel\Template', $asset);
                $this->assertNotEmpty($asset->getContent());
            }
        }
    }

    /**
     * Should import a complex theme zip file and create a PrivateTheme model
     */
    public function testValidComplexThemeImport()
    {
        $creator = $this->getFileImportCreator();
        $mvcEvent = $this->getMvcEventMock(__DIR__ . '/../Assets/complex-theme.zip');

        $result = $creator->create($mvcEvent);
        $this->assertInstanceOf('Zoop\ShardModule\Controller\Result', $result);

        $theme = $result->getModel();
        $assets = $theme->getAssets();

        $this->assertInstanceOf('Zoop\Theme\DataModel\PrivateTheme', $theme);
        $this->assertEquals('complex-theme', $theme->getName());
        $this->assertCount(4, $assets);
        $this->assertInstanceOf('Zoop\Theme\DataModel\AssetInterface', $assets[0]);

        //maybe need some more tests to traverse the child assets
    }
    
    /**
     * @return FileImportCreator
     */
    protected function getFileImportCreator()
    {
        return $this->getApplicationServiceLocator()
            ->get('zoop.commerce.theme.creator.import.file');
    }
    
    /**
     * Create a ZF2 MVC mock object
     * 
     * @param string $file
     * @return MvcEvent
     */
    protected function getMvcEventMock($file)
    {
        $theme = new PrivateTheme();
        
        $request = $this->getMock('Zend\Http\Request');
        $request->method('getFiles')
            ->willReturn(new Parameters([
                'theme' => [
                    'tmp_name' => $file
                ]
            ]));
        
        
        $result = $this->getMock('Zoop\ShardModule\Controller\Result');
        $result->expects($this->any())
            ->method('setStatusCode');
        $result->method('getModel')
            ->willReturn($theme);
        
        $mvcEvent = $this->getMock('Zend\Mvc\MvcEvent');
        $mvcEvent->method('getRequest')
            ->willReturn($request);
        $mvcEvent->method('getResult')
            ->willReturn($result);
        
        return $mvcEvent;
    }
}
