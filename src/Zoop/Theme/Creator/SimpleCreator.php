<?php

namespace Zoop\Theme\Creator;

use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zoop\ShardModule\Exception;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class SimpleCreator implements CreatorInterface
{
    /**
     * @param MvcEvent $event
     * @return Response
     * @throws Exception\DocumentAlreadyExistsException
     */
    public function create(MvcEvent $event)
    {
        $result = $event->getResult();
        $result->setStatusCode(201);
        return $result;
    }
}
