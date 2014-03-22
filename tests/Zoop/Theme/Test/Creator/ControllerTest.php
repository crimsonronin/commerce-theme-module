<?php

namespace Zoop\Theme\Test\Creator;

use Zoop\Theme\Test\BaseTest;
use Zoop\Theme\Test\FileMoc;
use Zend\Http\Header\Accept;
use Zend\Http\Header\Range;
use Zoop\Theme\DataModel\PrivateTheme;

class ControllerTest extends BaseTest
{

    const DOCUMENT_PRIVATE_THEME = '';

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

        $this->dispatch('http://demo.zoopcommerce.local/themes');
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
//        die(var_dump($response));
//        $result = json_decode($response->getContent(), true);
//        $response->getHeaders()->get('Location')->toString();
    }

    public function testComplexThemeImport()
    {
        $files = new FileMoc([
            'theme' => [
                'name' => 'complex-theme.zip',
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

        $this->dispatch('http://demo.zoopcommerce.local/themes');
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
//        die(var_dump($response));
//        $result = json_decode($response->getContent(), true);
//        $response->getHeaders()->get('Location')->toString();
    }

    public function testGetListTheme()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
                ->setMethod('GET')
                ->getHeaders()->addHeader($accept);

        $this->dispatch('http://demo.zoopcommerce.local/themes');

        $result = json_decode($this->getResponse()->getContent(), true);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('shard.rest.themes');
        $this->assertCount(2, $result);
    }

    public function testGetTheme()
    {
        $private = $this->createTheme();

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
                ->setMethod('GET')
                ->getHeaders()->addHeader($accept);

        $this->dispatch('http://demo.zoopcommerce.local/themes/' . $private->getId());

        $result = json_decode($this->getResponse()->getContent(), true);
        $this->assertResponseStatusCode(200);
        $this->assertEquals('Test', $result['name']);
    }

    /**
     * @return PrivateTheme
     */
    protected function createTheme()
    {
        $private = new PrivateTheme;
        $private->setName('Test');
        $private->addStore('demo');

        $this->getDocumentManager()->persist($private);
        $this->getDocumentManager()->flush($private);
        $this->getDocumentManager()->clear();

        return $private;
    }

    protected function getTheme($id)
    {
        return $this->getDocumentManager()
                        ->getRepository(self::DOCUMENT_PRIVATE_THEME)->find($id);
    }

}
