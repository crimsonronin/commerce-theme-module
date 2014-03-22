<?php

namespace Zoop\Theme\Test;

use Zoop\Store\DataModel\Store;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Shard\Core\Events;

abstract class BaseTest extends AbstractHttpControllerTestCase
{

    protected $documentManager;
    protected $clearData = true;
    public $calls;

    public function setUp()
    {
        $this->setApplicationConfig(
                require __DIR__ . '/../../../test.application.config.php'
        );
        $dm = $this->getApplicationServiceLocator()->get('doctrine.odm.documentmanager.commerce');
        $this->setDocumentManager($dm);

        $eventManager = $dm->getEventManager();
        $eventManager->addEventListener(Events::EXCEPTION, $this);

        //create a demo store
        $this->createStore();
        //set the Request host so that active store works correctly.
        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('demo.zoopcommerce.local');
    }

    /**
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @param DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    protected function createStore($data = [])
    {
        if (!empty($data)) {
            //serialize
        } else {
            $store = new Store;
            $store->setSlug('demo');
            $store->setSubdomain('demo');
            $store->setName('Demo');
            $store->setEmail('josh@zoopcommerce.com');
        }

        $this->getDocumentManager()->persist($store);
        $this->getDocumentManager()->flush($store);
        $this->getDocumentManager()->clear();
    }

    public function tearDown()
    {
        $this->clearDatabase();
    }

    public function clearDatabase()
    {
        if ($this->documentManager && $this->getClearData() === true) {
            $db = $this->getApplicationServiceLocator()
                            ->get('config')['doctrine']['odm']['connection']['commerce']['dbname'];

            $collections = $this->getDocumentManager()
                            ->getConnection()
                            ->selectDatabase($db)->listCollections();

            foreach ($collections as $collection) {
                /* @var $collection \MongoCollection */
                $collection->drop();
            }
        }
    }

    public function getClearData()
    {
        return $this->clearData;
    }

    public function setClearData($clearData)
    {
        $this->clearData = $clearData;
    }

    public function __call($name, $arguments)
    {
        var_dump($name, $arguments);
        $this->calls[$name] = $arguments;
    }

}
