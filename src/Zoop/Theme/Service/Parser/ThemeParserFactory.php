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
        $lexer = $serviceLocator->get('zoop.commerce.theme.lexer');
        $themeLinter = $serviceLocator->get('zoop.commerce.theme.linter.themelinter');
        $tokenParser = $serviceLocator->get('zoop.commerce.theme.parser.tokenparser');

        $themeParser = new ThemeParser(
            $assetParser,
            $contentParser,
            $lexer,
            $themeLinter,
            $tokenParser
        );

        $config = $serviceLocator->get('config')['zoop']['theme']['creator'];

        $themeParser->setCanLint($config['lint']);
        $themeParser->canParseContent($config['parse']['content']);
        $themeParser->canParseAssets($config['parse']['assets']);

        return $themeParser;
    }
}
