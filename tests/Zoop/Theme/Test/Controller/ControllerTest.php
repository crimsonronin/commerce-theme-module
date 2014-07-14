<?php

namespace Zoop\Theme\Test\Controller;

use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Test\FileMoc;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\PrivateTheme;
use Zoop\Theme\DataModel\ThemeInterface;

class ControllerTest extends AbstractTest
{
    const DOCUMENT_PRIVATE_THEME = 'Zoop\Theme\DataModel\PrivateTheme';

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

        $this->dispatch('http://apple.zoopcommerce.local/admin/themes');
        $this->assertResponseStatusCode(201);

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $id = str_replace(['Location: ', '/admin/themes/'], '', $response->getHeaders()->get('Location')->toString());

        $this->assertFalse(isset($result));

        $theme = $this->getTheme($id);
        $this->assertNotEmpty($theme);
        $this->assertEquals('Test', $theme->getName());
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

        $this->dispatch('http://apple.zoopcommerce.local/admin/themes/import');
        $this->assertResponseStatusCode(201);

        $response = $this->getResponse();

        $result = json_decode($response->getContent(), true);

        $id = str_replace(
            ['Location: ', '/admin/themes/import/'],
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
                ->getHeaders()->addHeader($accept);

        $request->setFiles($files);

        $this->dispatch('http://apple.zoopcommerce.local/admin/themes/import');
        $this->assertResponseStatusCode(201);

        $response = $this->getResponse();

        $result = json_decode($response->getContent(), true);

        $id = str_replace(
            ['Location: ', '/admin/themes/import/'],
            '',
            $response->getHeaders()->get('Location')->toString()
        );

        $this->assertTrue(isset($result['error']));
        $this->assertFalse($result['error']);

        $theme = $this->getTheme($id);
        $this->assertNotEmpty($theme);
        $this->assertEquals('complex-theme', $theme->getName());
    }

    public function testGetListTheme()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
                ->setMethod('GET')
                ->getHeaders()->addHeader($accept);

        $this->dispatch('http://apple.zoopcommerce.local/admin/themes');

        $result = json_decode($this->getResponse()->getContent(), true);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('shard.rest.themes');
        $this->assertCount(3, $result);
    }

    public function testGetTheme()
    {
        $private = $this->createSimpleTheme();

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
                ->setMethod('GET')
                ->getHeaders()->addHeader($accept);

        $this->dispatch('http://apple.zoopcommerce.local/admin/themes/' . $private->getId());

        $result = json_decode($this->getResponse()->getContent(), true);
        $this->assertResponseStatusCode(200);
        $this->assertEquals('Test', $result['name']);
    }

    public function testDeleteSimpleTheme()
    {
        $private = $this->createComplexTheme();

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
                ->setMethod('DELETE')
                ->getHeaders()->addHeader($accept);

        $this->dispatch('http://apple.zoopcommerce.local/admin/themes/' . $private->getId());

        $this->assertResponseStatusCode(204);
        //need to ensure assets are soft deleted too
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
            ->getRepository(self::DOCUMENT_PRIVATE_THEME)->find($id);
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
