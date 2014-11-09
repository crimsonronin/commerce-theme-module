<?php

namespace Zoop\Theme\Service\Linter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Linter\ThemeLinter;
use Zoop\Theme\Linter\ThemeLinterInterface;

class ThemeLinterFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ThemeLinterInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $themeStructure = $serviceLocator->get('zoop.commerce.theme.structure');
         
        return new ThemeLinter($themeStructure);
    }
}
