<?php

namespace Zoop\Theme\Test;

use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;
use Zend\Http\Header\GenericHeader;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Shard\Manifest;
use Zoop\Shard\Serializer\Serializer;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\Shard\Core\Events;
use Zend\ServiceManager\ServiceManager;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\Folder as FolderModel;

abstract class AbstractTest extends AbstractHttpControllerTestCase
{
    protected static $documentManager;
    protected static $noAuthDocumentManager;
    protected static $serviceManager;
    protected static $dbName;
    protected static $serializer;
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

            self::$noAuthDocumentManager = $this->getApplicationServiceLocator()
                ->get('doctrine.odm.documentmanager.noauth');

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
     * @return DocumentManager
     */
    public static function getNoAuthDocumentManager()
    {
        return self::$noAuthDocumentManager;
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
     * Clears the DB
     */
    public static function clearDatabase()
    {
        if (self::$documentManager) {
            $collections = self::getNoAuthDocumentManager()
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
        self::getNoAuthDocumentManager()->persist($theme);
        self::getNoAuthDocumentManager()->flush($theme);

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

                self::getNoAuthDocumentManager()->persist($asset);
                self::getNoAuthDocumentManager()->flush($asset);
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

    public function applyUserToRequest($request, $key, $secret)
    {
        $request->getHeaders()->addHeaders([
            GenericHeader::fromString('Authorization: Basic ' . base64_encode(sprintf('%s:%s', $key, $secret)))
        ]);
    }

    public function applyJsonRequest($request)
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $request->getHeaders()
            ->addHeaders([
                $accept,
                ContentType::fromString('Content-type: application/json'),
            ]);
    }

    public function applyMultiPartRequest($request)
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $request->getHeaders()
            ->addHeaders([
                $accept,
                ContentType::fromString('Content-type: multipart/form-data'),
            ]);
    }

    public function __call($name, $arguments)
    {
        var_dump($name, $arguments);
        $this->calls[$name] = $arguments;
    }
}
