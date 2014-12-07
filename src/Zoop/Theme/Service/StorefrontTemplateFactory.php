<?php

namespace Zoop\Theme\Service;

use \Exception;
use \Twig_Loader_Chain;
use \Twig_Loader_Filesystem;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Theme\MongoDbTwigLoader;
use Zoop\Theme\Extension\Core\CeilExtension;
use Zoop\Theme\Extension\Core\SortExtension;
use Zoop\Theme\Extension\Core\Nl2pExtension;
use Zoop\Theme\Extension\TokenParser\Get as GetTokenParser;
use Zoop\Theme\Manager\LegacyTemplateManager as TemplateManager;
use Zoop\Theme\TwigEnvironment;

class StorefrontTemplateFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return TemplateManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['zoop'];
        $store = $serviceLocator->get('zoop.commerce.store.active');

        //check for a set of legacy custom templates
        $templates = $config['theme']['storefront']['templates'];
        $customTemplate = $config['theme']['template_dir'] . '/storefront/' . $store->getId();

        if (is_dir($customTemplate)) {
            array_unshift($templates, $customTemplate);
        }

        $isDev = (bool) (isset($config['dev']) ? $config['dev'] : false);

        try {
            $theme = $serviceLocator->get('zoop.commerce.theme.active');
            $dm = $serviceLocator->get('shard.commerce.modelmanager');

            $dbLoader = new MongoDbTwigLoader($dm, $theme);
            $fsLoader = new Twig_Loader_Filesystem($templates);
            $chainLoader = new Twig_Loader_Chain([$dbLoader, $fsLoader]);
        } catch (Exception $e) {
            throw new Exception('We cannot find the template for ' . $store->getId());
        }

        $twig = new TwigEnvironment($chainLoader, [
            'cache' => $isDev ? false : $config['cache']['directory'] . '/',
        ]);
        $twig->addExtension(new CeilExtension());
        $twig->addExtension(new SortExtension());
        $twig->addExtension(new Nl2pExtension());
        $twig->addTokenParser(new GetTokenParser());

        return new TemplateManager($twig);
    }
}
