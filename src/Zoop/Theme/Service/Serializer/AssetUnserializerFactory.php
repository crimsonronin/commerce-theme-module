<?php

namespace Zoop\Theme\Service\Serializer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Serializer\Asset\Unserializer;

class AssetUnserializerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Unserializer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['zoop']['theme'];

        $unserializer = new Unserializer();
        $unserializer->setTempDirectory($config['temp_dir']);

        return $unserializer;
    }
}
