<?php

namespace Zoop\Theme\Controller;

use \Exception;
use \SplFileInfo;
use Zend\Mvc\MvcEvent;
use Zoop\ShardModule\Controller\Listener\CreateListener;
use Zoop\ShardModule\Controller\Result;
use Zoop\Store\DataModel\Store;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\AbstractTheme;
use Zoop\Theme\DataModel\PrivateTheme as PrivateThemeModel;
use Zoop\Theme\Creator\ThemeCreatorImport;

class ThemeCreateListener extends CreateListener
{
    protected $store;
    protected $documentManager;

    public function create(MvcEvent $event)
    {
        return $this->route($event);
    }

    protected function doAction(MvcEvent $event, $metadata, $documentManager)
    {
        $this->setDocumentManager($documentManager);
        
        $request = $event->getRequest();
        /* @var $post \Zend\Stdlib\Parameters */
        $post = $request->getPost()->toArray();

        //create new
        //if there's no data in the post we will try and check for a file that should be imported
        if (empty($post)) {
            $uploadedFile = $request->getFiles()->toArray();
            if (!isset($uploadedFile['theme'])) {
                throw new Exception('No file uploaded');
            }

            $file = new SplFileInfo($uploadedFile['theme']['tmp_name']);

            if (empty($file)) {
                throw new Exception('No file uploaded');
            }

            try {
                $this->import($event, $file);
                $result = new Result;
                $result->setStatusCode(201);
            } catch (Exception $e) {
                $result = new Result;
                $result->setStatusCode(403);
            }
        }

        return $result;
    }

    private function import(MvcEvent $event, $file)
    {
        $importer = $this->getImporter($event);
        try {
            $theme = $importer->create($file);
            if ($theme instanceof ThemeInterface) {
                //add the store to the private theme
                if ($theme instanceof PrivateThemeModel) {
                    $theme->addStore($this->getStoreSubdomain($event));
                }
                $this->save($theme);

                return true;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function saveTheme(ThemeInterface $theme)
    {
        $this->getDocumentManager()->persist($theme);
        $this->getDocumentManager()->flush();
    }

    private function save(ThemeInterface $theme)
    {
        $this->saveTheme($theme);
        $this->saveRecursively($theme, $theme->getAssets());
    }

    /**
     *
     * @param ThemeInterface $theme
     * @param array $assets
     */
    private function saveRecursively(ThemeInterface $theme, $assets)
    {
        if (!empty($assets)) {
            /* @var $asset AssetInterface */
            foreach ($assets as $asset) {
                $parent = $asset->getParent();
                if (empty($parent)) {
                    $asset->setParent($theme);
                }
                $asset->setTheme($theme);

                $this->getDocumentManager()->persist($asset);
                $this->getDocumentManager()->flush();
            }

            //look for folders and recurse
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel) {
                    $childAssets = $asset->getAssets();
                    if (!empty($childAssets)) {
                        $this->saveRecursively($theme, $childAssets);
                    }
                }
            }
        }
    }

    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    public function setDocumentManager($documentManager)
    {
        $this->documentManager = $documentManager;
    }

        /**
     * @param MvcEvent $event
     * @return string
     */
    private function getStoreSubdomain(MvcEvent $event)
    {
        return $this->getStore($event)->getSubdomain();
    }

    /**
     * @param MvcEvent $event
     * @return Store
     */
    public function getStore(MvcEvent $event)
    {
        if (!isset($this->store)) {
            $this->store = $event->getTarget()->getOptions()->getServiceLocator()->get('zoop.commerce.store.active');
        }
        return $this->store;
    }

    /**
     * @param MvcEvent $event
     * @return ThemeCreatorImport
     */
    public function getImporter(MvcEvent $event)
    {
        if (!isset($this->importer)) {
            $this->importer = $event->getTarget()->getOptions()->getServiceLocator()->get('zoop.commerce.theme.creator.import');
        }
        return $this->importer;
    }

}
