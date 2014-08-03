<?php

namespace Zoop\Theme\Test\Controller;

use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Test\FileMoc;
use Zend\Http\Header\Accept;
use Zend\Http\Header\Origin;
use Zend\Http\Header\ContentType;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\PrivateTheme;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\Template;

class ThemeControllerTest extends AbstractTest
{
    const DOCUMENT_PRIVATE_THEME = 'Zoop\Theme\DataModel\PrivateTheme';

    public function testOptionsRequestSucceed()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('OPTIONS')
            ->getHeaders()->addHeaders([
                $accept,
                Origin::fromString('Origin: http://apple.zoopcommerce.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/themes');

        $response = $this->getResponse();

        $this->assertResponseStatusCode(201);
    }
    
    public function testThemeCreate()
    {
        $data = [
            'type' => 'PrivateTheme',
            'stores' => ['apple'],
            'name' => 'Test'
        ];

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('POST')
            ->setContent(json_encode($data))
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('http://apple.zoopcommerce.local/themes');
        $this->assertResponseStatusCode(201);

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $id = str_replace(['Location: ', '/themes/'], '', $response->getHeaders()->get('Location')->toString());

        $this->assertFalse(isset($result));

        $theme = $this->getTheme($id);
        $this->assertNotEmpty($theme);
        $this->assertEquals('Test', $theme->getName());
        
        //prime all assets
        /* @var $asset Template */
        $asset = $theme->getAssets()[13];
        
        $this->assertEquals('index.html', $asset->getName());
        $this->assertEmpty($asset->getContent());
        
        return $theme;
    }

    public function testSimpleThemeImport()
    {
        $files = new FileMoc([
            'theme' => [
                'name' => 'simple-theme.zip',
                'type' => 'application/zip',
                'size' => 542,
                'tmp_name' => __DIR__ . '/../Assets/simple-theme.zip',
                'error' => 0
            ]
        ]);
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $request = $this->getRequest();

        $request->setMethod('POST')
            ->getHeaders()->addHeader($accept);

        $request->setFiles($files);

        $this->dispatch('http://apple.zoopcommerce.local/themes/import');
        $this->assertResponseStatusCode(201);

        $response = $this->getResponse();

        $result = json_decode($response->getContent(), true);

        $id = str_replace(
            ['Location: ', '/themes/import/'],
            '',
            $response->getHeaders()->get('Location')->toString()
        );

        $this->assertTrue(isset($result['error']));
        $this->assertFalse($result['error']);

        $theme = $this->getTheme($id);
        $this->assertNotEmpty($theme);
        $this->assertEquals('simple-theme', $theme->getName());
    }

    public function testComplexThemeImport()
    {
        $files = new FileMoc([
            'theme' => [
                'name' => 'complex-theme.zip',
                'type' => 'application/zip',
                'size' => 542,
                'tmp_name' => __DIR__ . '/../Assets/complex-theme.zip',
                'error' => 0
            ]
        ]);
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $request = $this->getRequest();

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([$accept]);

        $request->setFiles($files);

        $this->dispatch('http://apple.zoopcommerce.local/themes/import');
        $this->assertResponseStatusCode(201);

        $response = $this->getResponse();

        $result = json_decode($response->getContent(), true);

        $id = str_replace(
            ['Location: ', '/themes/import/'],
            '',
            $response->getHeaders()->get('Location')->toString()
        );

        $this->assertTrue(isset($result['error']));
        $this->assertFalse($result['error']);

        $theme = $this->getTheme($id);
        $this->assertNotEmpty($theme);
        $this->assertEquals('complex-theme', $theme->getName());
        
        return $id;
    }

    public function testGetListTheme()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('GET')
            ->getHeaders()->addHeaders([$accept]);

        $this->dispatch('http://apple.zoopcommerce.local/themes');

        $result = json_decode($this->getResponse()->getContent(), true);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('shard.rest.themes');
        $this->assertCount(3, $result);
    }

    /**
     * @depends testComplexThemeImport
     */
    public function testGetTheme($id)
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('GET')
            ->getHeaders()->addHeaders([$accept]);

        $this->dispatch(sprintf('http://apple.zoopcommerce.local/themes/%s', $id));

        $result = json_decode($this->getResponse()->getContent(), true);
        $this->assertResponseStatusCode(200);
        $this->assertEquals('complex-theme', $result['name']);
        $this->assertNotEmpty($result['assets']);
        $this->assertCount(15, $result['assets']);
        
        return $result;
    }

    /**
     * @depends testThemeCreate
     */
    public function testUpdateTheme(PrivateTheme $theme)
    {
        $id = $theme->getId();
        
        $content = '<html><body><h1>This is some content</h1></body></html>';
        
        /* @var $asset Template */
        $asset = $theme->getAssets()[13];
        $asset->setContent($content);
        
        $jsonData = self::getSerializer()->toJson($theme);
        
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PUT')
            ->setContent($jsonData)
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch(sprintf('http://apple.zoopcommerce.local/themes/%s', $id));
        $this->assertResponseStatusCode(204);
        
        $this->reset();
        
        // check to see if the asset was updated correctly
        $this->getRequest()
            ->setMethod('GET')
            ->getHeaders()->addHeaders([$accept]);

        $this->dispatch(sprintf('http://apple.zoopcommerce.local/themes/%s', $id));
        $result = json_decode($this->getResponse()->getContent(), true);
        
        $newAsset = $result['assets'][13];
        
        $this->assertEquals('index.html', $newAsset['name']);
        $this->assertEquals($content, $newAsset['content']);
    }

    /**
     * @depends testThemeCreate
     */
    public function testDeleteSimpleTheme(PrivateTheme $theme)
    {
        $id = $theme->getId();
        
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('DELETE')
            ->getHeaders()->addHeaders([$accept]);

        $this->dispatch(sprintf('http://apple.zoopcommerce.local/themes/%s', $id));
        $this->assertResponseStatusCode(204);
        
        $this->reset();
        
        // check that we cannot get the deleted theme
        $this->getRequest()
            ->setMethod('GET')
            ->getHeaders()->addHeaders([$accept]);

        $this->dispatch(sprintf('http://apple.zoopcommerce.local/themes/%s', $id));

        $this->assertResponseStatusCode(404);
        $result = json_decode($this->getResponse()->getContent(), true);
        
        $this->assertEquals('Document not found', $result['title']);
    }

    /**
     * @return PrivateTheme
     */
    protected function createSimpleTheme()
    {
        $private = new PrivateTheme;
        $private->setName('Test');
        $private->addStore('apple');

        $this->getDocumentManager()->persist($private);
        $this->getDocumentManager()->flush($private);
        $this->getDocumentManager()->clear();

        return $private;
    }

    /**
     * @return PrivateTheme
     */
    protected function createComplexTheme()
    {
        $private = $this->getApplicationServiceLocator()->get('zoop.commerce.theme.structure');
        $private->setName('Test');
        $private->addStore('apple');

        $this->getDocumentManager()->persist($private);
        $this->getDocumentManager()->flush($private);

        //persist assets
        $this->saveRecursively($private, $private->getAssets());

        $this->getDocumentManager()->clear();

        return $private;
    }

    /**
     * @param string $id
     * @return PrivateTheme
     */
    protected function getTheme($id)
    {
        return $this->getDocumentManager()
            ->createQueryBuilder(self::DOCUMENT_PRIVATE_THEME)
            ->eagerCursor(true)
            ->hydrate(true)
            ->field('id')->equals($id)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     *
     * @param ThemeInterface $theme
     * @param array $assets
     */
    protected function saveRecursively(ThemeInterface $theme, $assets)
    {
        if (!empty($assets)) {
            /* @var $asset AssetInterface */
            foreach ($assets as $asset) {
                $parent = $asset->getParent();
                if (empty($parent)) {
                    $asset->setParent($theme);
                }
                $asset->setTheme($theme);

                $this->getDocumentManager()->persist($asset);
                $this->getDocumentManager()->flush($asset);
            }

            //look for folders and recurse
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel) {
                    $childAssets = $asset->getAssets();
                    if (!empty($childAssets)) {
                        $this->saveRecursively($theme, $childAssets);
                    }
                }
            }
        }
    }
}