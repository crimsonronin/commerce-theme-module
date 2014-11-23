<?php

namespace Zoop\Theme\Service\Manager;

use Zoop\Theme\AssetManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssetManagerFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AssetManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $themeManager = new AssetManager();

        return $themeManager;
    }
}
