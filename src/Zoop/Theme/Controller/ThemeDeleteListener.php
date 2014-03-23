<?php

/**
 * @package    Zoop
 */

namespace Zoop\Theme\Controller;

use Zend\Mvc\MvcEvent;
use Zoop\Shard\SoftDelete\SoftDeleter;
use Zoop\ShardModule\Controller\Listener\AbstractActionListener;
use Zoop\ShardModule\Controller\Result;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\Folder as FolderModel;
/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class ThemeDeleteListener extends AbstractActionListener
{
    protected $softDeleter;

    public function delete(MvcEvent $event)
    {
        return $this->route($event);
    }

    protected function doAction(MvcEvent $event, $metadata, $documentManager)
    {
        $options = $event->getTarget()->getOptions();
        $softDeleter = $options->getManifest()->getServiceManager()->get('softdeleter');
        $this->setSoftDeleter($softDeleter);

        /* @var $theme ThemeInterface */
        if (!$theme = $event->getParam('document')) {
            $theme = $documentManager->getRepository($metadata->name)
                ->findOneBy([$options->getProperty() => $event->getParam('id')]);
        }

        //soft delete
        $this->getSoftDeleter()->softDelete(
            $theme,
            $documentManager->getClassMetadata(get_class($theme))
        );
        $this->deleteRecursively($theme, $theme->getAssets(), $documentManager);

        $result = new Result([]);
        $result->setStatusCode(204);

        $event->setResult($result);

        return $result;
    }

    private function deleteRecursively(ThemeInterface $theme, $assets, $documentManager)
    {
        if (!empty($assets)) {
            /* @var $asset AssetInterface */
            foreach ($assets as $asset) {
                //soft delete
                $this->getSoftDeleter()->softDelete(
                    $asset,
                    $documentManager->getClassMetadata(get_class($asset))
                );
            }

            //look for folders and recurse
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel) {
                    $childAssets = $asset->getAssets();
                    if (!empty($childAssets)) {
                        $this->deleteRecursively($theme, $childAssets, $documentManager);
                    }
                }
            }
        }
    }

    /**
     * @return SoftDeleter
     */
    public function getSoftDeleter()
    {
        return $this->softDeleter;
    }

    /**
     * @param SoftDeleter $softDeleter
     */
    public function setSoftDeleter(SoftDeleter $softDeleter)
    {
        $this->softDeleter = $softDeleter;
    }
}
