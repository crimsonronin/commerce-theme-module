<?php

namespace Zoop\Theme\Parser;

use \SplFileInfo;
use \DirectoryIterator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\Parser\DirectoryParserInterface;
use Zoop\Theme\Serializer\Asset\UnserializerInterface;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class DirectoryParser implements
    DirectoryParserInterface,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $unserializer;

    public function __construct(UnserializerInterface $unserializer)
    {
        $this->setUnserializer($unserializer);
    }

    /**
     * {@inheritdoc}
     */
    public function parse($directory)
    {
        $this->setTempDirectory($directory);

        //parse directory for assets
        return $this->parseDirectory($directory);
    }

    /**
     * Loops through the directory to parse assets.
     *
     * @param string $directory
     * @param AssetInterface $parent
     * @return array
     */
    protected function parseDirectory($directory, AssetInterface $parent = null)
    {
        $assets = [];

        $files = new DirectoryIterator($directory);

        /* @var $file DirectoryIterator */
        foreach ($files as $file) {
            if (($file->isDir() || $file->isFile()) && !$file->isDot()) {
                $asset = $this->getAsset($file->getPathname());

                if ($asset instanceof AssetInterface) {
                    $assets[] = $asset;

                    if (!is_null($parent)) {
                        $asset->setParent($parent);
                    }

                    if ($file->isDir()) {
                        $childAssets = $this->parseDirectory(
                            $file->getPathname(), $asset
                        );
                        $asset->setAssets($childAssets);
                    }
                }
            }
        }

        return $assets;
    }

    /**
     * @param string $pathname
     * @return AssetInterface
     */
    protected function getAsset($pathname)
    {
        return $this->getUnserializer()
            ->fromFile(new SplFileInfo($pathname));
    }

    /**
     * @param string $tempDirectory
     */
    protected function setTempDirectory($tempDirectory)
    {
        $this->getUnserializer()
            ->setTempDirectory($tempDirectory);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnserializer()
    {
        return $this->unserializer;
    }

    /**
     * {@inheritdoc}
     */
    public function setUnserializer(UnserializerInterface $unserializer)
    {
        $this->unserializer = $unserializer;
    }
}
