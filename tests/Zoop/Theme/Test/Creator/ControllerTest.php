<?php

namespace Zoop\Theme\Test\Creator;

use Zoop\Theme\Test\BaseTest;
use Zoop\Theme\Test\FileMoc;
use Zend\Http\Header\Accept;
use Zend\Http\Header\Range;

class ControllerTest extends BaseTest
{
    public function testSimpleThemeImport()
    {
        $this->createStore();

        $files = new FileMoc([
            'theme' => [
                'name' => 'simple-theme.zip',
                'type' => 'application/zip',
                'size' => 542,
                'tmp_name' => __DIR__ . '/../Assets/simple-theme.zip',
                'error' => 0
            ]
        ]);
        $request = $this->getRequest();
        $request->setMethod('POST');

        $request->setFiles($files);

        $this->dispatch('http://demo.zoopcommerce.local/themes');
        $this->assertResponseStatusCode(200);
        
        //this is bad codes
        $this->setClearData(false);
    }
    
    public function testGetListTheme()
    {
        $this->createStore();
        
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('GET')
            ->getHeaders()->addHeader($accept);

        $this->dispatch('http://demo.zoopcommerce.local/themes');
        
        $result = json_decode($this->getResponse()->getContent(), true);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('shard.rest.themes');
    }

}
