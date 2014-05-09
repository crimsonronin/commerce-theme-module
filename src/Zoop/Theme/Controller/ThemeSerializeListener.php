<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Theme\Controller;

use Zend\Mvc\MvcEvent;
use Zoop\ShardModule\Controller\Result;
use Zoop\ShardModule\Controller\Listener\SerializeListener;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ThemeSerializeListener extends SerializeListener
{
    public function serialize(MvcEvent $event)
    {
        $result = $event->getResult();
        if (!($result instanceof Result) || $result->getSerializedModel() || !($model = $result->getModel())) {
            return $result;
        }

        $serializer = $event->getTarget()->getOptions()->getManifest()->getServiceManager()->get('serializer');
        $serializer->setMaxNestingDepth(10);

        $serializedModel = $serializer->toArray($model);
        if ($select = $this->getSelect($event)) {
            $serializedModel = array_intersect_key($serializedModel, array_fill_keys($select, 0));
        }
        $result->setSerializedModel($serializedModel);

        return $result;
    }

    public function getList(MvcEvent $event)
    {
        $result = $event->getResult();
        if (!($result instanceof Result) || !($model = $result->getModel())) {
            return;
        }

        if (!is_array($model) && !($model instanceof \Traversable)) {
            return $this->serialize($event);
        }

        $serializer = $event->getTarget()->getOptions()->getManifest()->getServiceManager()->get('serializer');
        $serializer->setMaxNestingDepth(0);

        $items = [];
        foreach ($model as $item) {
            $items[] = $serializer->toArray($item);
        }
        if ($select = $this->getSelect($event)) {
            foreach ($items as $key => $item) {
                $items[$key] = array_intersect_key($item, array_fill_keys($select, 0));
            }
        }

        $result->setSerializedModel($items);

        return $result;
    }
}
