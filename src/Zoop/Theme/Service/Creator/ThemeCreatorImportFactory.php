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
        $themeStructure = $serviceLocator->get('zoop.commerce.theme.structure');

        $creator = new ThemeCreatorImport();
        $creator->setTempDirectory($config['temp_dir']);
        $creator->setMaxFileUploadSize($config['max_file_upload_size']);
        $creator->setUnserializer($unserializer);
        $creator->setThemeStructure($themeStructure);

        return $creator;
    }
}
