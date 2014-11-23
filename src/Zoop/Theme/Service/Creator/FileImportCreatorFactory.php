<?php

namespace Zoop\Theme\Service\Creator;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Creator\FileImportCreator;

class FileImportCreatorFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ThemeCreatorImport
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['zoop']['theme'];
        $directoryParser = $serviceLocator->get('zoop.commerce.theme.parser.directoryparser');

        $creator = new FileImportCreator(
            $directoryParser,
            $config['temp_dir'],
            $config['max_file_upload_size']
        );

        return $creator;
    }
}
