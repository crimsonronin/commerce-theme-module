<?php

namespace Zoop\Theme\Service;

use \Twig_Loader_Chain;
use \Twig_Loader_Filesystem;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\MongoDbTwigLoader;
use Zoop\Theme\Extension\Core\CeilExtension;
use Zoop\Theme\Extension\Core\SortExtension;
use Zoop\Theme\Extension\Core\Nl2pExtension;
use Zoop\Theme\Extension\TokenParser\Get as GetTokenParser;
//use Zoop\Theme\TemplateManager;
use Zoop\Theme\LegacyTemplateManager as TemplateManager;
use Zoop\Theme\TwigEnvironment;

class StorefrontTemplateFactory implements FactoryInterface
{

    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return TemplateManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['zoop'];
        $store = $serviceLocator->get('zoop.commerce.store.legacy.active');

        //check for a set of legacy custom templates
        $templates = $config['theme']['storefront']['templates'];
        $customTemplate = $config['theme']['template_dir'] . '/storefront/' . $store->getSubDomain();

        if (is_dir($customTemplate)) {
            array_unshift($templates, $customTemplate);
        }

        $loader = new Twig_Loader_Filesystem($templates);

        if ($config['dev'] === false) {
            $theme = $serviceLocator->get('zoop.commerce.theme.active');
            if (!empty($theme)) {
                $dm = $serviceLocator->get('shard.commerce.modelmanager');

                $dbLoader = new MongoDbTwigLoader($dm, $theme);

                $loader = new Twig_Loader_Chain([$dbLoader, $loader]);
            }
        }
        $twig = new TwigEnvironment($loader, array(
            'cache' => ($config['dev'] === true) ? false : $config['cache']['directory'] . '/',
        ));
        $twig->addExtension(new CeilExtension());
        $twig->addExtension(new SortExtension());
        $twig->addExtension(new Nl2pExtension());
        $twig->addTokenParser(new GetTokenParser());

        return new TemplateManager($twig);
    }

}
