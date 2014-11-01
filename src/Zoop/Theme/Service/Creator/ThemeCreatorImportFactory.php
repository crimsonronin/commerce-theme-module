<?php

namespace Zoop\Theme\Service\Creator;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Creator\ThemeCreatorImport;

class ThemeCreatorImportFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ThemeCreatorImport
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['zoop']['theme'];
        $manifest = $serviceLocator->get('shard.commerce.manifest');
        /* @var $manifest \Zoop\Shard\Manifest */
        $unserializer = $manifest->getServiceManager()->get('unserializer');

        $creator = new ThemeCreatorImport(
            $unserializer,
            $config['temp_dir'],
            $config['max_file_upload_size']
        );

        return $creator;
    }
}
