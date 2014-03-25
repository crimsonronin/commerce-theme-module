<?php

namespace Zoop\Theme\Test\Parser;

use \SplFileInfo;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use Zoop\Theme\Test\BaseTest;
use Zoop\Theme\Parser\Node\ImageNode;
use Zoop\Theme\Parser\Node\CssNode;
use Zoop\Theme\Parser\Node\JavascriptNode;
use Zoop\Theme\Tokenizer\Token;

class FileParserTest extends BaseTest
{
    protected static $tempDir;

    public function setUp()
    {
        parent::setUp();

        $this->setTempDir(__DIR__ . '/../../../../../data/temp');
    }

    public static function getTempDir()
    {
        return self::$tempDir;
    }

    public static function setTempDir($tempDir)
    {
        self::$tempDir = $tempDir;
    }

    public function testParseAbsoluteHttpsImage()
    {
        $tempDir = $this->getTempDir();

        $url = 'https://zoop-ops-sydney.s3.amazonaws.com/testing/assets/zoop.png';
        $token = new Token\Absolute\ImageToken($url);
        $token->setTempDirectory($tempDir);

        $node = new ImageNode;
        $node->setToken($token);

        $model = $node->getModel();
        $this->assertEquals('zoop.png', $model->getName());
        $this->assertEquals('testing/assets/zoop.png', $model->getPathname());
        $this->assertEquals('image/png', $model->getMime());
        $this->assertEquals(150, $model->getWidth());
        $this->assertEquals(64, $model->getHeight());
    }

    public function testParseAbsoluteHttpImage()
    {
        $tempDir = $this->getTempDir();

        $url = 'https://zoop-ops-sydney.s3.amazonaws.com/testing/assets/zoop.jpg';
        $token = new Token\Absolute\ImageToken($url);
        $token->setTempDirectory($tempDir);

        $node = new ImageNode;
        $node->setToken($token);

        $model = $node->getModel();
        $this->assertEquals('zoop.jpg', $model->getName());
        $this->assertEquals('testing/assets/zoop.jpg', $model->getPathname());
        $this->assertEquals('image/jpeg', $model->getMime());
        $this->assertEquals(500, $model->getWidth());
        $this->assertEquals(500, $model->getHeight());
    }

    public function testParseRelativeImage()
    {
        $tempDir = __DIR__ . '/../Assets';

        $url = 'images/zoop.png';
        $token = new Token\Relative\ImageToken($url);
        $token->setTempDirectory($tempDir);

        $node = new ImageNode;
        $node->setToken($token);

        $model = $node->getModel();
        $this->assertEquals('zoop.png', $model->getName());
        $this->assertEquals($url, $model->getPathname());
        $this->assertEquals('image/png', $model->getMime());
        $this->assertEquals(150, $model->getWidth());
        $this->assertEquals(64, $model->getHeight());
    }

    public function testParseAbsoluteCss()
    {
        $tempDir = $this->getTempDir();

        $url = 'https://zoop-ops-sydney.s3.amazonaws.com/testing/assets/bootstrap.css';
        $token = new Token\Absolute\CssToken($url);
        $token->setTempDirectory($tempDir);

        $node = new CssNode;
        $node->setToken($token);

        $model = $node->getModel();
        $this->assertEquals('bootstrap.css', $model->getName());
        $this->assertEquals('testing/assets/bootstrap.css', $model->getPathname());
        $this->assertNotEmpty($model->getContent());
    }

    public function testParseRelativeCss()
    {
        $tempDir = __DIR__ . '/../Assets';

        $url = 'bootstrap.css';
        $token = new Token\Relative\CssToken($url);
        $token->setTempDirectory($tempDir);

        $node = new CssNode;
        $node->setToken($token);

        $model = $node->getModel();
        $this->assertEquals('bootstrap.css', $model->getName());
        $this->assertEquals('bootstrap.css', $model->getPathname());
        $this->assertNotEmpty($model->getContent());
    }

    public function testParseAbsoluteJavascript()
    {
        $tempDir = $this->getTempDir();

        $url = 'https://zoop-ops-sydney.s3.amazonaws.com/testing/assets/jquery-2.1.0.js';
        $token = new Token\Absolute\JavascriptToken($url);
        $token->setTempDirectory($tempDir);

        $node = new JavascriptNode;
        $node->setToken($token);

        $model = $node->getModel();
        $this->assertEquals('jquery-2.1.0.js', $model->getName());
        $this->assertEquals('testing/assets/jquery-2.1.0.js', $model->getPathname());
        $this->assertNotEmpty($model->getContent());
    }

    public function testParseRelativeJavascript()
    {
        $tempDir = __DIR__ . '/../Assets';

        $url = 'jquery-2.1.0.js';
        $token = new Token\Relative\JavascriptToken($url);
        $token->setTempDirectory($tempDir);

        $node = new JavascriptNode;
        $node->setToken($token);

        $model = $node->getModel();
        $this->assertEquals('jquery-2.1.0.js', $model->getName());
        $this->assertEquals('jquery-2.1.0.js', $model->getPathname());
        $this->assertNotEmpty($model->getContent());
    }

    /**
     * @param string $dir
     */
    protected static function deleteDirectoryRecursively($dir, $deleteRoot = false)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
        /* @var $file SplFileInfo */
        foreach ($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }

            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        if ($deleteRoot === true) {
            rmdir($dir);
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::deleteDirectoryRecursively(self::getTempDir());
    }
}
