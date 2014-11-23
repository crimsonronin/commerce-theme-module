<?php

namespace Zoop\Theme\Creator;

use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\ShardModule\Exception;
use Zoop\Theme\Creator\CreatorInterface;
use Zoop\Theme\Parser\ThemeParserInterface;

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
            $result = $this->getFileImportCreator()
                ->create($event);
        } else {
            $result = $this->getSimpleCreator()
                ->create($event);
        }

        $theme = $result->getModel();

        //parse theme
        $this->getThemeParser()->parse($theme);

        return $result;
    }

    /**
     * @return CreatorInterface
     */
    private function getSimpleCreator()
    {
        return $this->getServiceLocator()
            ->get('zoop.commerce.theme.creator.simple');
    }

    /**
     * @return CreatorInterface
     */
    private function getFileImportCreator()
    {
        return $this->getServiceLocator()
            ->get('zoop.commerce.theme.creator.import.file');
    }

    /**
     * @return ThemeParserInterface
     */
    private function getThemeParser()
    {
        return $this->getServiceLocator()
            ->get('zoop.commerce.theme.parser.themeparser');
    }
}
