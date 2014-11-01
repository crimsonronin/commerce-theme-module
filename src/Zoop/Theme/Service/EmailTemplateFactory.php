<?php

namespace Zoop\Theme\Service;

use \Twig_Loader_Filesystem;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\Extension\Core\CeilExtension;
use Zoop\Theme\Extension\Core\SortExtension;
use Zoop\Theme\Extension\Core\Nl2pExtension;
use Zoop\Theme\Extension\TokenParser\Get as GetTokenParser;
use Zoop\Theme\LegacyTemplateManager as TemplateManager;
use Zoop\Theme\TwigEnvironment;

class EmailTemplateFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return TemplateManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['zoop'];
        $store = $serviceLocator->get('zoop.commerce.store.active');

        $templates = $config['theme']['email']['templates'];
        $customTemplate = $config['theme']['template_dir'] . '/storefront/' . $store->getId();

        if (is_dir($customTemplate)) {
            array_unshift($templates, $customTemplate);
        }

        $loader = new Twig_Loader_Filesystem($templates);
        $twig = new TwigEnvironment($loader, array(
            'cache' => (DEV === true) ? false : $config['cache']['directory'],
        ));
        $twig->addExtension(new CeilExtension());
        $twig->addExtension(new SortExtension());
        $twig->addExtension(new Nl2pExtension());
        $twig->addTokenParser(new GetTokenParser());

        return new TemplateManager($twig);
    }
}
