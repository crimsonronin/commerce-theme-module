<?php

namespace Zoop\Theme\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\DataModel\ThemeInterface;

class ThemeStructureFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ThemeInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $manifest = $serviceLocator->get('shard.commerce.manifest');
        /* @var $manifest \Zoop\Shard\Manifest */
        $unserializer = $manifest->getServiceManager()->get('unserializer');

        $file = __DIR__ . '/../../../../config/theme.json';
        $class = 'Zoop\Theme\DataModel\AbstractTheme';

        $json = file_get_contents($file);

        $theme = $unserializer->fromJson($json, $class);

        return $theme;
    }
}
