<?php

namespace Zoop\Theme\Creator;

use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\ShardModule\Exception;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class Creator implements CreatorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param MvcEvent $event
     * @return Response
     * @throws Exception\DocumentAlreadyExistsException
     */
    public function create(MvcEvent $event)
    {
        $request = $event->getRequest();

        $uploadedFile = $request->getFiles()->toArray();
        $xFileName = $request->getHeaders()->get('X-File-Name');

        // test to see if this is a file import or single create
        if (isset($uploadedFile['theme']) || $xFileName) {
            $importer = $this->getServiceLocator()
                ->get('zoop.commerce.theme.creator.import.file');

            return $importer->create($event);
        } else {
            // create using default
            $creator = $this->getServiceLocator()
                ->get('zoop.commerce.theme.creator.simple');

            return $creator->create($event);
        }
    }
}
