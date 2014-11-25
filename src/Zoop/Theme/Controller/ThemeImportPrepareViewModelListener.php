<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Theme\Controller;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ThemeImportPrepareViewModelListener
{
    public function __call($name, $args)
    {
        return $this->prepareViewModel($args[0], $name);
    }

    /**
     * @param MvcEvent $event
     * @param mixed $action
     * @return JsonModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareViewModel(MvcEvent $event, $action)
    {
        if ($event->getTarget()->forward()->getNumNestedForwards() > 0) {
            return $event->getResult();
        }

        $result = $event->getResult();

        $response = $event->getResponse();
        $response->setStatusCode($result->getStatusCode());
        $response->getHeaders()->addHeaders($result->getHeaders());

        //this is to comply with the current ajax file uploads
        $viewModel = new JsonModel(['error' => false]);

        $event->setResult($viewModel);

        return $viewModel;
    }
}
