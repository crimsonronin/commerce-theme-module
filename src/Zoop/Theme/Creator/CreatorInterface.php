<?php

namespace Zoop\Theme\Creator;

use Zend\Mvc\MvcEvent;
use Zend\Http\Response;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
interface CreatorInterface
{
    /**
     * @param MvcEvent $event
     * @return Response
     */
    public function create(MvcEvent $event);
}
