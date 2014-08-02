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
use Zend\ServiceManager\ServiceManager;

class ThemeCreateListener extends CreateListener
{
    protected $store;
    protected $documentManager;
    protected $serviceLocator;

    protected function doAction(MvcEvent $event, $metadata, $documentManager)
    {
        //create a service manager helper
        $sm = $event->getTarget()->getOptions()->getServiceLocator();
        $this->setServiceLocator($sm);

        $request = $event->getRequest();

        $uploadedFile = $request->getFiles()->toArray();
        $xFileName = $request->getHeaders()->get('X-File-Name');

        // test to see if this is a file import or single create
        if (isset($uploadedFile['theme']) || $xFileName) {
            return $this->doImport($event);
        } else {
            return parent::doAction($event, $metadata, $documentManager);
        }
    }

    /**
     * Creates a theme from an uploaded file
     * 
     * @param MvcEvent $event
     * @return mixed
     * @throws Exception
     */
    protected function doImport(MvcEvent $event)
    {
        $request = $event->getRequest();
        
        $uploadedFile = $request->getFiles()->toArray();
        
        if (isset($uploadedFile['theme'])) {
            $uploadedFileName = $uploadedFile['theme']['tmp_name'];
        } else {
            $uploadedContent = $request->getContent();
            if (!empty($uploadedContent)) {
                $filename = $request->getHeaders()->get('X-File-Name')->getFieldValue();
                $uploadedFileName = $this->saveLocalFile($filename, $uploadedContent);
            } else {
                throw new Exception('No file uploaded');
            }
        }

        $result = $event->getResult();
        $theme = $result->getModel();

        $file = new SplFileInfo($uploadedFileName);

        if (empty($file)) {
            throw new Exception('No file uploaded');
        }

        try {
            $this->import($file, $theme);
            $result->setStatusCode(201);

            $this->removeLocalFile($uploadedFileName);

            return $result;
        } catch (Exception $e) {
            $this->removeLocalFile($uploadedFileName);

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

    /**
     * @param string $filename
     * @param string $content
     * @return string
     */
    protected function saveLocalFile($filename, $content)
    {
        $tempDir = $this->getTempDirectory();
        if (!is_dir($tempDir)) {
            $createdDir = mkdir($tempDir);
        }

        $filePathname = $tempDir . '/' . $filename;

        file_put_contents($filePathname, $content);
        return $filePathname;
    }

    /**
     * @param string $filename
     */
    protected function removeLocalFile($filename)
    {
        $tempDir = $this->getTempDirectory();
        if (strpos($filename, $tempDir) === 0) {
            @unlink($filename);
            @rmdir($tempDir);
        }
    }

    /**
     * @return ServiceManager
     */
    protected function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceManager $serviceLocator
     */
    protected function setServiceLocator(ServiceManager $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return string
     */
    protected function getStoreSubdomain()
    {
        return $this->getStore()->getSubdomain();
    }

    /**
     * @return Store
     */
    protected function getStore()
    {
        if (!isset($this->store)) {
            $this->store = $this->getServiceLocator()
                ->get('zoop.commerce.store.active');
        }
        return $this->store;
    }

    /**
     * @return ThemeCreatorImport
     */
    protected function getImporter()
    {
        if (!isset($this->importer)) {
            $this->importer = $this->getServiceLocator()
                ->get('zoop.commerce.theme.creator.import');
        }
        return $this->importer;
    }

    /**
     * @return string
     */
    protected function getTempDirectory()
    {
        if (!isset($this->tempDirectory)) {
            $this->tempDirectory = $this->getServiceLocator()
                ->get('config')['zoop']['theme']['temp_dir'] . '/' . uniqid();
        }
        return $this->tempDirectory;
    }
}
