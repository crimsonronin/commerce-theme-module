<?php

namespace Zoop\Theme\Service\Lexer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Lexer\Lexer;
use Zoop\Theme\Lexer\LexerInterface;

class LexerFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return LexerInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $themeStructure = $serviceLocator->get('zoop.commerce.theme.structure');

        return new Lexer($themeStructure);
    }
}
