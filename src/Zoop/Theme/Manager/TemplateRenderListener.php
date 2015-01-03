<?php

namespace Zoop\Theme\Manager;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Theme\Events as ThemeEvents;
use Zoop\User\Service\SystemUserUtil;

/**
 * A listener to ensure the we use the theme user to render the template
 */
class TemplateRenderListener implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     * Attach listeners to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(ThemeEvents::TEMPLATE_PRE_RENDER, [$this, 'addSystemUser'], 1);
        $this->listeners[] = $events->attach(ThemeEvents::TEMPLATE_POST_RENDER, [$this, 'removeSystemUser'], 1);
    }

    /**
     * Detach listeners from an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * @param EventInterface $event
     */
    public function addSystemUser(EventInterface $event)
    {
        $user = $this->getServiceLocator()
            ->get('zoop.commerce.user.theme');
        
        $this->getSystemUserUtils()
            ->addSystemUser($user);
    }

    /**
     * @param EventInterface $event
     */
    public function removeSystemUser(EventInterface $event)
    {
        $this->getSystemUserUtils()
            ->removeSystemUser();
    }
    
    /**
     * @return SystemUserUtil
     */
    protected function getSystemUserUtils()
    {
        return $this->getServiceLocator()->get('zoop.commerce.user.systemuserutil');
    }
}
