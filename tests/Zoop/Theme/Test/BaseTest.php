<?php

namespace Zoop\Theme\Test;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zoop\Shard\Manifest;
use Zoop\Shard\Serializer\Unserializer;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Shard\Core\Events;

abstract class BaseTest extends AbstractHttpControllerTestCase
{
    protected $documentManager;
    protected $unserializer;
    protected $manifest;
    protected $db;
    protected $store;

    public function setUp()
    {
        $this->setApplicationConfig(
            require __DIR__ . '/../../../../config/module.config.php'
        );

        $manifest = $this->getApplicationServiceLocator()
            ->get('zoop.commerce.theme.private');
        
        $manifest = $this->getApplicationServiceLocator()
            ->get('shard.commerce.manifest');

        $dm = $this->getApplicationServiceLocator()
            ->get('shard.commerce.modelmanager');

        $unserializer = $manifest->getServiceManager()
            ->get('unserializer');

        $this->setManifest($manifest);
        $this->setDocumentManager($dm);

        $this->setUnserializer($unserializer);

        $eventManager = $dm->getEventManager();
        $eventManager->addEventListener(Events::EXCEPTION, $this);

        $this->calls = [];
    }

    /**
     *
     * @return Manifest
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     *
     * @param Manifest $manifest
     */
    public function setManifest(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }

    /**
     *
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     *
     * @return Unserializer
     */
    public function getUnserializer()
    {
        return $this->unserializer;
    }

    /**
     *
     * @param DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     *
     * @param Unserializer $unserializer
     */
    public function setUnserializer(Unserializer $unserializer)
    {
        $this->unserializer = $unserializer;
    }

    public function tearDown()
    {
        $this->clearDatabase();
    }

    public function clearDatabase()
    {
//        if ($this->documentManager) {
//            $db = $this->getApplicationServiceLocator()
//                ->get('config')['doctrine']['odm']['connection']['commerce']['dbname'];
//            $collections = $this->getDocumentManager()
//                ->getConnection()
//                ->selectDatabase($db)->listCollections();
//
//            foreach ($collections as $collection) {
//                /* @var $collection \MongoCollection */
//                $collection->drop();
//            }
//        }
    }

    public function __call($name, $arguments)
    {
        var_dump($name, $arguments[0]);
        $this->calls[$name] = $arguments;
    }

}
