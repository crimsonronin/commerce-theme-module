<?php

namespace Zoop\Theme\Manager;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Theme\Events;

class TemplateManager extends AbstractTemplateManager implements
    ServiceLocatorAwareInterface,
    TemplateManagerInterface
{
    use ServiceLocatorAwareTrait;

    public function render()
    {
        return $this->load($this->getFile(), $this->getVariables());
    }

    public function load($file, $data = [])
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        $template = $this->getTwig()->loadTemplate($file);
        $this->getEventManager()->trigger(Events::TEMPLATE_PRE_RENDER, null, $data);
        
        //render the template
        $renderedTemplate = $template->render($data);
        
        $this->getEventManager()->trigger(Events::TEMPLATE_POST_RENDER, null, [
                'data' => $data,
                'render' => $renderedTemplate
            ]);

        return $renderedTemplate;
    }
    
    /**
     * 
     * @return EventManagerInterface
     */
    protected function getEventManager()
    {
        return $this->getServiceLocator()
            ->get('Application')
            ->getEventManager();
    }
}