<?php

namespace Zoop\Theme\Controller;

use \Exception;
use \SplFileInfo;
use Zend\Mvc\MvcEvent;
use Zoop\ShardModule\Controller\Listener\CreateListener;
use Zoop\Store\DataModel\Store;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\PrivateTheme as PrivateThemeModel;
use Zoop\Theme\Creator\ThemeCreatorImport;

class ThemeCreateListener extends CreateListener
{
    protected $store;
    protected $documentManager;
    protected $serviceLocator;

    protected function doAction(MvcEvent $event, $metadata, $documentManager)
    {
        $request = $event->getRequest();

        $uploadedFile = $request->getFiles()->toArray();
        
        if (isset($uploadedFile['theme'])) {
            $uploadedFileName = $uploadedFile['theme']['tmp_name'];
        } else {
            $uploadedContent = $request->getContent();
            if (!empty($uploadedContent)) {
                $uploadedFileName = $this->saveLocal($uploadedContent);
            } else {
                throw new Exception('No file uploaded');
            }
        }

        $result = $event->getResult();
        $theme = $result->getModel();

        $sm = $event->getTarget()->getOptions()->getServiceLocator();
        $this->setServiceLocator($sm);

        $file = new SplFileInfo($uploadedFileName);

        if (empty($file)) {
            throw new Exception('No file uploaded');
        }

        try {
            $this->import($file, $theme);
            $result->setStatusCode(201);

            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function import(SplFileInfo $file, ThemeInterface $theme)
    {
        $importer = $this->getImporter();
        try {
            if ($theme instanceof PrivateThemeModel) {
                $theme->addStore($this->getStoreSubdomain());
            }

            //use the current theme
            $importer->setTheme($theme);
            $importer->import($file);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function saveLocal($content)
    {
        $name = tempnam(sys_get_temp_dir(), 'Zoop');
        file_put_contents($name, $content);
        return $name;
    }

    protected function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    protected function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param MvcEvent $event
     * @return string
     */
    protected function getStoreSubdomain()
    {
        return $this->getStore()->getSubdomain();
    }

    /**
     * @param MvcEvent $event
     * @return Store
     */
    protected function getStore()
    {
        if (!isset($this->store)) {
            $this->store = $this->getServiceLocator()->get('zoop.commerce.store.active');
        }
        return $this->store;
    }

    /**
     * @param MvcEvent $event
     * @return ThemeCreatorImport
     */
    protected function getImporter()
    {
        if (!isset($this->importer)) {
            $this->importer = $this->getServiceLocator()->get('zoop.commerce.theme.creator.import');
        }
        return $this->importer;
    }

}
