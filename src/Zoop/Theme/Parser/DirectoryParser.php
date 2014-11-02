<?php

namespace Zoop\Theme\Parser;

use \SplFileInfo;
use \DirectoryIterator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\Serializer\Asset\UnserializerInterface;
use Zoop\Theme\DataModel\ThemeInterface;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class DirectoryParser implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    protected $unserializer;
    protected $tempDirectory;
    
    /**
     * @param string $directory
     * @param ThemeInterface $theme
     */
    public function parse($directory, ThemeInterface $theme)
    {
        $this->setTempDirectory($directory);
        
        //parse directory for assets
        $assets = $this->parseDirectory($directory);
        
        $theme->setAssets($assets);
    }
    
    /**
     * Loops through the directory to parse assets.
     *
     * @param string $directory
     * @param AssetInterface $parent
     * @return AssetInterface
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
                            $file->getPathname(),
                            $asset
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
     * @return UnserializerInterface
     */
     public function getUnserializer()
    {
        if (empty($this->unserializer)) {
            $unserializer = $this->getServiceLocator()
                ->get('zoop.commerce.theme.serializer.asset.unserializer');
            
            //override the default template directory to use the
            //current uuid one
            $unserializer->setTempDirectory($this->getTempDirectory());

            $this->setUnserializer($unserializer);
        }

        return $this->unserializer;
    }

    /**
     * @param UnserializerInterface $unserializer
     */
    public function setUnserializer(UnserializerInterface $unserializer)
    {
        $this->unserializer = $unserializer;
    }
    
    /**
     * @return string
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * @param string $tempDirectory
     */
    public function setTempDirectory($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }
}
