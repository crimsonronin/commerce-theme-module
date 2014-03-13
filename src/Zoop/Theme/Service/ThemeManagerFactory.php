<?php

namespace Zoop\Theme\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\ThemeManager;

class ThemeManagerFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ThemeManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config =  $serviceLocator->get('config')['zoop']['theme'];

        $assetManager = $serviceLocator->get('zoop.commerce.theme.assetmanager');
        $theme = $serviceLocator->get('zoop.commerce.theme');
        $validator = $serviceLocator->get('zoop.commerce.validator');
        $dm = $serviceLocator->get('shard.commerce.modelmanager');

        $themeManager = new ThemeManager();
        $themeManager->setDm($dm);
        $themeManager->setTheme($theme);
        $themeManager->setValidator($validator);
        $themeManager->setAssetManager($assetManager);
        $themeManager->setTempDirectory($config['temp_dir']);

        return $themeManager;
    }
}
