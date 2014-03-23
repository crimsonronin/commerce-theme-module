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
        if (!isset($uploadedFile['theme'])) {
            throw new Exception('No file uploaded');
        }

        $result = $event->getResult();
        $theme = $result->getModel();

        $sm = $event->getTarget()->getOptions()->getServiceLocator();
        $this->setServiceLocator($sm);

        $file = new SplFileInfo($uploadedFile['theme']['tmp_name']);

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

    private function import(SplFileInfo $file, ThemeInterface $theme)
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

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param MvcEvent $event
     * @return string
     */
    private function getStoreSubdomain()
    {
        return $this->getStore()->getSubdomain();
    }

    /**
     * @param MvcEvent $event
     * @return Store
     */
    public function getStore()
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
    public function getImporter()
    {
        if (!isset($this->importer)) {
            $this->importer = $this->getServiceLocator()->get('zoop.commerce.theme.creator.import');
        }
        return $this->importer;
    }

}
