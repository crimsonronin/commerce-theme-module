<?php

/**
 * @package    Zoop
 */

namespace Zoop\Theme\Controller;

use Zend\Mvc\MvcEvent;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\ShardModule\Controller\Result;
use Zoop\ShardModule\Controller\Listener\UnserializeListener;

/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class ThemeUnserializeListener extends UnserializeListener
{
    public function create(MvcEvent $event)
    {
        if (count($event->getParam('deeperResource')) > 0 || $result = $event->getResult()) {
            return $event->getResult();
        }

        $sm = $event->getTarget()
            ->getOptions()
            ->getManifest()
            ->getServiceManager();

        $theme = $sm->get('zoop.commerce.theme.structure');

        $result = new Result(
            $sm->get('unserializer')
                ->fromArray(
                    $event->getParam('data'),
                    $event->getTarget()->getOptions()->getClass(),
                    $theme,
                    Unserializer::UNSERIALIZE_PATCH
                )
        );
        $event->setResult($result);

        return $result;
    }
}
