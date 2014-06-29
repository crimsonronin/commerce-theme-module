<?php

namespace Zoop\Theme\Test;

use Zoop\Store\DataModel\Store;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Shard\Manifest;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\Theme\Test\Assets\TestData;
use Zoop\Shard\Core\Events;
use Zend\ServiceManager\ServiceManager;

abstract class AbstractTest extends AbstractHttpControllerTestCase
{
    protected static $documentManager;
    protected static $serviceManager;
    protected static $dbName;
    protected static $unserializer;
    protected static $manifest;
    protected static $store;
    public $calls;

    public function setUp()
    {
        $this->setApplicationConfig(
            require __DIR__ . '/../../../test.application.config.php'
        );

        //create db connection and store requests
        if (!isset(self::$documentManager)) {
            self::$documentManager = $this->getApplicationServiceLocator()
                ->get('doctrine.odm.documentmanager.commerce');
            
            self::$dbName = $this->getApplicationServiceLocator()
                ->get('config')['doctrine']['odm']['connection']['commerce']['dbname'];

            $eventManager = self::$documentManager->getEventManager();
            $eventManager->addEventListener(Events::EXCEPTION, $this);

            if (!isset(self::$manifest)) {
                self::$manifest = $this->getApplicationServiceLocator()
                    ->get('shard.commerce.manifest');
            }

            if (!isset(self::$unserializer)) {
                self::$unserializer = self::$manifest->getServiceManager()
                    ->get('unserializer');
            }

            //create a apple store
            self::getStore();
        }
        
        if (empty(self::$store)) {
            $store = self::getStore();
        }
        
        //set the Request host so that active store works correctly.
        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('apple.zoopcommerce.local');
    }

    public static function tearDownAfterClass()
    {
        self::clearDatabase();
    }

    /**
     * @return DocumentManager
     */
    public static function getDocumentManager()
    {
        return self::$documentManager;
    }

    /**
     * @param DocumentManager $documentManager
     */
    public static function setDocumentManager(DocumentManager $documentManager)
    {
        self::$documentManager = $documentManager;
    }

    /**
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        return self::$serviceManager;
    }

    /**
     * @param ServiceManager $serviceManager
     */
    public static function setServiceManager(ServiceManager $serviceManager)
    {
        self::$serviceManager = $serviceManager;
    }

    /**
     * @return string
     */
    public static function getDbName()
    {
        return self::$dbName;
    }

    /**
     * @param string $dbName
     */
    public static function setDbName($dbName)
    {
        self::$dbName = $dbName;
    }

    /**
     *
     * @return Manifest
     */
    public static function getManifest()
    {
        return self::$manifest;
    }

    /**
     *
     * @return Unserializer
     */
    public static function getUnserializer()
    {
        return self::$unserializer;
    }

    /**
     * @return Store
     */
    protected static function getStore()
    {
        if (!isset(self::$store)) {
            $store = TestData::createStore(self::getUnserializer());

            self::getDocumentManager()->persist($store);
            self::getDocumentManager()->flush($store);
            self::getDocumentManager()->clear($store);
            self::$store = $store;
        }
        return self::$store;
    }

    public static function clearDatabase()
    {
        if (self::$documentManager) {
            $collections = self::getDocumentManager()
                ->getConnection()
                ->selectDatabase(self::getDbName())->listCollections();

            foreach ($collections as $collection) {
                /* @var $collection \MongoCollection */
                $collection->drop();
            }
            self::$documentManager->clear();
            self::$store = null;
        }
    }

    public function __call($name, $arguments)
    {
        var_dump($name, $arguments);
        $this->calls[$name] = $arguments;
    }
}
