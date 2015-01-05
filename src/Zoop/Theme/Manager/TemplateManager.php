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

    /**
     * Render the twig template
     * 
     * @return string
     */
    public function render()
    {
        return $this->load($this->getFile(), $this->getVariables());
    }

    /**
     * Load and render the twig template provided
     * 
     * @param string $file
     * @param array $data
     * @return string
     */
    protected function load($file, $data = [])
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        $this->getEventManager()->trigger(Events::TEMPLATE_PRE_RENDER, null, $data);
        
        //render the template
        $template = $this->getTwig()->loadTemplate($file);
        $renderedTemplate = $template->render($data);
        
        $this->getEventManager()->trigger(Events::TEMPLATE_POST_RENDER, null, [
                'data' => $data,
                'render' => $renderedTemplate
            ]);

        return $renderedTemplate;
    }
    
    /**
     * @return EventManagerInterface
     */
    protected function getEventManager()
    {
        return $this->getServiceLocator()
            ->get('Application')
            ->getEventManager();
    }
}