<?php

namespace Zoop\Theme\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use \Twig_Environment;
use Zoop\Theme\Validator;

class ValidatorFactory implements FactoryInterface
{
    /**
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Validator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $twig = new Twig_Environment;

        return new Validator($twig);
    }
}
