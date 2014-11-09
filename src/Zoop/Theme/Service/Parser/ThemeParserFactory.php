<?php

namespace Zoop\Theme\Service\Parser;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Parser\ThemeParserInterface;
use Zoop\Theme\Parser\ThemeParser;

class ThemeParserFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ThemeParserInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $assetParser = $serviceLocator->get('zoop.commerce.theme.parser.assetparser');
        $contentParser = $serviceLocator->get('zoop.commerce.theme.parser.contentparser');
        $themeLinter = $serviceLocator->get('zoop.commerce.theme.themelinter');
        $lexer = $serviceLocator->get('zoop.commerce.theme.lexer');
        $tokenParser= $serviceLocator->get('zoop.commerce.theme.parser.tokenparser');
         
        return new ThemeParser(
            $assetParser,
            $contentParser,
            $lexer,
            $themeLinter,
            $tokenParser
        );
    }
}
