<?php

namespace Zoop\Theme\Test;

use Zoop\Store\DataModel\Store;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Shard\Core\Events;
use Zend\ServiceManager\ServiceManager;

abstract class BaseTest extends AbstractHttpControllerTestCase
{
    protected static $documentManager;
    protected static $serviceManager;
    protected static $dbName;
    public $calls;

    public function setUp()
    {
        $this->setApplicationConfig(
            require __DIR__ . '/../../../test.application.config.php'
        );

        //create db connection and store requests
        if (!isset(self::$documentManager)) {
            self::$documentManager = $this->getApplicationServiceLocator()->get('doctrine.odm.documentmanager.commerce');
            self::$dbName = $this->getApplicationServiceLocator()->get('config')['doctrine']['odm']['connection']['commerce']['dbname'];

            $eventManager = self::$documentManager->getEventManager();
            $eventManager->addEventListener(Events::EXCEPTION, $this);

            //create a demo store
            $this->createStore();
            //set the Request host so that active store works correctly.
            $request = $this->getApplicationServiceLocator()->get('request');
            /* @var $request Request */
            $request->getUri()->setHost('demo.zoopcommerce.local');
        }
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

    protected static function createStore($data = [])
    {
        $db = self::getDocumentManager();
        if (!empty($data)) {
            //serialize
        } else {
            $store = new Store;
            $store->setSlug('demo');
            $store->setSubdomain('demo');
            $store->setName('Demo');
            $store->setEmail('josh@zoopcommerce.com');
        }

        $db->persist($store);
        $db->flush($store);
        $db->clear();
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
        }
    }

    public function __call($name, $arguments)
    {
        var_dump($name, $arguments);
        $this->calls[$name] = $arguments;
    }
}
