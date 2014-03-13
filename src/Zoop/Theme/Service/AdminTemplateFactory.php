<?php

namespace Zoop\Theme\Service;

use \Twig_Environment;
use \Twig_Loader_Filesystem;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Extension\Core\CeilExtension;
use Zoop\Theme\Extension\Core\SortExtension;
use Zoop\Theme\Extension\Core\Nl2pExtension;
use Zoop\Theme\TemplateManager;

class AdminTemplateFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return TemplateManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['zoop'];

        $loader = new Twig_Loader_Filesystem($config['theme']['admin']['templates']);
        $twig = new Twig_Environment($loader, array(
            'cache' => (DEV === true) ? false : $config['cache']['directory'],
        ));
        $twig->addExtension(new CeilExtension());
        $twig->addExtension(new SortExtension());
        $twig->addExtension(new Nl2pExtension());

        return new TemplateManager($twig);
    }
}
