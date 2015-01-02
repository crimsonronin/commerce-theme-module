<?php

namespace Zoop\Theme\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Store\DataModel\Store;

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
        $store = $serviceLocator->get('zoop.commerce.entity.active');
        /* @var $store Store */

        $theme = $dm->createQueryBuilder('Zoop\Theme\DataModel\AbstractTheme')
            ->field('stores')->in([$store->getId()])
            ->field('isActive')->equals(true)
            ->getQuery()
            ->getSingleResult();

        return $theme;
    }
}
