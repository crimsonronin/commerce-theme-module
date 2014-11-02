<?php

namespace Zoop\Theme\Controller;

use Zend\Mvc\MvcEvent;
use Zoop\ShardModule\Controller\Listener\CreateListener;
use Zend\Http\Response;

class ThemeCreateListener extends CreateListener
{
    protected $store;
    protected $documentManager;
    protected $serviceLocator;

    /**
     * @param MvcEvent $event
     * @param type $metadata
     * @param type $documentManager
     * @return Response
     */
    protected function doAction(MvcEvent $event, $metadata, $documentManager)
    {
        //create a service manager helper
        $sm = $event->getTarget()->getOptions()->getServiceLocator();
        $creator = $sm->get('zoop.commerce.theme.creator');
        return $creator->create($event);
    }
}
