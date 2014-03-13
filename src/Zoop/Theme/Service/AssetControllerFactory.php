<?php

namespace Zoop\Theme\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Controller\AssetController;

class AssetControllerFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AssetController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dm = $serviceLocator->get('shard.commerce.modelmanager');

        $manifest = $serviceLocator->get('shard.commerce.manifest');
        /* @var $manifest \Zoop\Shard\Manifest */
        $unserializer = $manifest->getServiceManager()->get('unserializer');
        $serializer = $manifest->getServiceManager()->get('serializer');
        $softDelete = $manifest->getServiceManager()->get('softdeleter');

        $store = $serviceLocator->get('zoop.commerce.store.legacy.model.active');

        $controller = new AssetController;
        $controller->setDm($dm);
        $controller->setSerializer($serializer);
        $controller->setUnserializer($unserializer);
        $controller->setSoftDelete($softDelete);
        $controller->setStore($store);
        return $controller;
    }
}
