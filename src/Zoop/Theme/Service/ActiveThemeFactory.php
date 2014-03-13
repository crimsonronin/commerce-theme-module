<?php

namespace Zoop\Theme\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\DataModel\ThemeInterface;

class ActiveThemeFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ThemeInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dm = $serviceLocator->get('shard.commerce.modelmanager');
        $store = $serviceLocator->get('zoop.commerce.store.legacy.model.active');

        $theme = $dm->getRepository('Zoop\Theme\DataModel\AbstractTheme')
                ->findOneBy([
            'legacyStoreId' => $store->getId(),
            'active' => true
        ]);
        return $theme;
    }
}
