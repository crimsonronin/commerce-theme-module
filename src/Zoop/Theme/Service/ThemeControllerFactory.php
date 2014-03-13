<?php

namespace Zoop\Theme\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Controller\ThemeController;

class ThemeControllerFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ThemeController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dm = $serviceLocator->get('shard.commerce.modelmanager');

        $manifest = $serviceLocator->get('shard.commerce.manifest');
        /* @var $manifest \Zoop\Shard\Manifest */
        $unserializer = $manifest->getServiceManager()->get('unserializer');
        $serializer = $manifest->getServiceManager()->get('serializer');
        $softDelete = $manifest->getServiceManager()->get('softdeleter');

        $importer = $serviceLocator->get('zoop.commerce.theme.creator.import');
        $store = $serviceLocator->get('zoop.commerce.store.legacy.model.active');

        $controller = new ThemeController;
        $controller->setDm($dm);
        $controller->setSerializer($serializer);
        $controller->setUnserializer($unserializer);
        $controller->setSoftDelete($softDelete);
        $controller->setImporter($importer);

        $controller->setStore($store);
        return $controller;
    }
}
