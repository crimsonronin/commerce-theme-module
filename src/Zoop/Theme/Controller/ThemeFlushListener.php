<?php

/**
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\Theme\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Zend\Mvc\MvcEvent;
use Zoop\ShardModule\Controller\Listener\FlushListener;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\Folder as FolderModel;

/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class ThemeFlushListener extends FlushListener
{
    public function flush(MvcEvent $event)
    {
        if ($event->getTarget()->forward()->getNumNestedForwards() > 0) {
            return $event->getResult();
        }

        $result = $event->getResult();
        $createdDocument = $result->getModel();
        $options = $event->getTarget()->getOptions();
        $documentManager = $options->getModelManager();

        $this->setDocumentManager($documentManager);

        $this->save($createdDocument);

        if (!($flushExceptions = $options->getExceptionSubscriber()->getFlushExceptions())) {
            return $event->getResult();
        } else {
            return $this->prepareExceptions($flushExceptions, $options->getExceptionSerializer());
        }
    }

    private function save(ThemeInterface $theme)
    {
        $this->saveTheme($theme);
        $this->saveRecursively($theme, $theme->getAssets());
    }

    private function saveTheme(ThemeInterface $theme)
    {
        $this->getDocumentManager()->persist($theme);
        $this->getDocumentManager()->flush($theme);
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
                $this->getDocumentManager()->flush($asset);
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

    /**
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @param DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }
}
