<?php

namespace Zoop\Theme;

use \Twig_Error_Loader;
use \Twig_LoaderInterface;
use \Twig_ExistsLoaderInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Theme\DataModel\ThemeInterface;

class MongoDbTwigLoader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    protected $dm;
    protected $theme;
    protected $cache;

    public function __construct(DocumentManager $dm, ThemeInterface $theme)
    {
        $this->setDm($dm);
        $this->setTheme($theme);
    }

    public function getDm()
    {
        return $this->dm;
    }

    public function setDm(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setTheme(ThemeInterface $theme)
    {
        $this->theme = $theme;
    }

    public function getSource($pathName)
    {
        if (false === $source = $this->getValue('content', $pathName)) {
            throw new Twig_Error_Loader(sprintf('Template "%s" does not exist.', $pathName));
        }

        return $source;
    }

    // Twig_ExistsLoaderInterface as of Twig 1.11
    public function exists($pathName)
    {
        return $pathName === $this->getValue('pathname', $pathName);
    }

    public function getCacheKey($pathName)
    {
        return $pathName;
    }

    public function isFresh($pathName, $time)
    {
        if (false === $lastModified = $this->getValue('lastModified', $pathName)) {
            return false;
        }

        return $lastModified <= $time;
    }

    protected function getValue($column, $pathName)
    {
        $asset = $this->getThemeAsset($pathName);
        if (!empty($asset)) {
            $method = 'get' . ucwords($column);
            if (method_exists($asset, $method)) {
                return $asset->$method();
            }
        }
        return false;
    }

    protected function getThemeAsset($pathName)
    {
        $asset = $this->getThemeAssetFromCache($pathName);
        if (empty($asset)) {
            $theme = $this->getTheme();
            $asset = $this->getDm()->createQueryBuilder('Zoop\Theme\DataModel\Asset')
                    ->field('pathname')->equals($pathName)
                    ->field('theme')->references($theme)
                    ->getQuery()
                    ->getSingleResult();
            $this->cache[$pathName] = $asset;
        }
        return $asset;
    }

    protected function getThemeAssetFromCache($pathName)
    {
        if (isset($this->cache[$pathName]) && !empty($this->cache[$pathName])) {
            return $this->cache[$pathName];
        }
        return false;
    }
}
