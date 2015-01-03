<?php

namespace Zoop\Theme\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\MongoDbTwigLoader;

class MongoDbTwigLoaderFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return MongoDbTwigLoader
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $theme = $serviceLocator->get('zoop.commerce.theme.active');
        $dm = $serviceLocator->get('shard.commerce.modelmanager');

        return new MongoDbTwigLoader($dm, $theme);
    }
}
