<?php

namespace Zoop\Theme\Service\Parser;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Parser\DirectoryParserInterface;
use Zoop\Theme\Parser\DirectoryParser;

class DirectoryParserFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return DirectoryParserInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $assetUnserializer = $serviceLocator
            ->get('zoop.commerce.theme.serializer.asset.unserializer');

        return new DirectoryParser($assetUnserializer);
    }
}
