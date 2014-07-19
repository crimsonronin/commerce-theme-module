<?php

namespace Zoop\Theme\Test;

use Zoop\Store\DataModel\Store;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Shard\Manifest;
use Zoop\Shard\Serializer\Serializer;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\Theme\Test\Assets\TestData;
use Zoop\Shard\Core\Events;
use Zend\ServiceManager\ServiceManager;
use Zoop\Theme\Creator\ThemeCreatorImport;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\Folder as FolderModel;

abstract class AbstractTest extends AbstractHttpControllerTestCase
{
    protected static $documentManager;
    protected static $serviceManager;
    protected static $dbName;
    protected static $serializer;
    protected static $unserializer;
    protected static $manifest;
    protected static $store;
    protected static $creator;
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

            if (!isset(self::$serializer)) {
                self::$serializer = self::$manifest->getServiceManager()
                    ->get('serializer');
            }

            if (!isset(self::$creator)) {
                self::$creator = $this->getApplicationServiceLocator()
                    ->get('zoop.commerce.theme.creator.import');
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
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        return self::$serviceManager;
    }

    /**
     * @return string
     */
    public static function getDbName()
    {
        return self::$dbName;
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
     * @return Serializer
     */
    public static function getSerializer()
    {
        return self::$serializer;
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
     * @return ThemeCreatorImport
     */
    public static function getThemeCreatorImport()
    {
        return self::$creator;
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

    /**
     * Clears the DB
     */
    public static function clearDatabase()
    {
        if (self::$documentManager) {
            $collections = self::getDocumentManager()
                ->getConnection()
                ->selectDatabase(self::getDbName())
                ->listCollections();

            foreach ($collections as $collection) {
                /* @var $collection \MongoCollection */
                $collection->drop();
            }
            self::$documentManager->clear();
            self::$store = null;
        }
    }

    /**
     * @param ThemeInterface $theme
     */
    public static function saveTheme(ThemeInterface $theme)
    {
        self::getDocumentManager()->persist($theme);
        self::getDocumentManager()->flush($theme);

        self::saveThemeAssetsRecursively($theme, $theme->getAssets());
    }

    /**
     * @param ThemeInterface $theme
     * @param array $assets
     */
    public static function saveThemeAssetsRecursively(ThemeInterface $theme, $assets)
    {
        if (!empty($assets)) {
            /* @var $asset AssetInterface */
            foreach ($assets as $asset) {
                $parent = $asset->getParent();
                if (empty($parent)) {
                    $asset->setParent($theme);
                }
                $asset->setTheme($theme);

                self::getDocumentManager()->persist($asset);
                self::getDocumentManager()->flush($asset);
            }

            //look for folders and recurse
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel) {
                    $childAssets = $asset->getAssets();
                    if (!empty($childAssets)) {
                        self::saveThemeAssetsRecursively($theme, $childAssets);
                    }
                }
            }
        }
    }

    public function __call($name, $arguments)
    {
        var_dump($name, $arguments);
        $this->calls[$name] = $arguments;
    }
}
