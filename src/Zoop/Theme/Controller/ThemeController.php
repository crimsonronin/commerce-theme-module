<?php

namespace Zoop\Theme\Controller;

use \Exception;
use \SplFileInfo;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Store\DataModel\Store;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\AbstractTheme;
use Zoop\Theme\DataModel\PrivateTheme as PrivateThemeModel;
use Zoop\Theme\DataModel\SharedTheme as SharedThemeModel;
use Zoop\Theme\DataModel\ZoopTheme as ZoopThemeModel;
use Zoop\Theme\Creator\ThemeCreatorImport;
use Zoop\Shard\SoftDelete\SoftDeleter;
use Zoop\Shard\Serializer\Serializer;
use Zoop\Shard\Serializer\Unserializer;

/**
 *
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class ThemeController
{
    const CLASS_MODEL = 'Zoop\Theme\DataModel\AbstractTheme';

    private $dm;
    private $importer;
    private $store;
    private $softDelete;
    private $serializer;
    private $unserializer;

    /**
     *
     * @return SoftDeleter
     */
    public function getSoftDelete()
    {
        return $this->softDelete;
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return Unserializer
     */
    public function getUnserializer()
    {
        return $this->unserializer;
    }

    /**
     * @param SoftDeleter $softDelete
     */
    public function setSoftDelete(SoftDeleter $softDelete)
    {
        $this->softDelete = $softDelete;
    }

    /**
     *
     * @param Serializer $serializer
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     *
     * @param Unserializer $unserializer
     */
    public function setUnserializer(Unserializer $unserializer)
    {
        $this->unserializer = $unserializer;
    }

    /**
     * @return ThemeCreatorImport
     */
    public function getImporter()
    {
        return $this->importer;
    }

    /**
     *
     * @param ThemeCreatorImport $importer
     */
    public function setImporter(ThemeCreatorImport $importer)
    {
        $this->importer = $importer;
    }

    public function import(SplFileInfo $file, $active = false)
    {
        $importer = $this->getImporter();
        try {
            $theme = $importer->create($file);
            if ($theme instanceof ThemeInterface) {
                //add the store to the private theme
                if ($theme instanceof PrivateThemeModel) {
                    $theme->addStore($this->getStoreSubDomain());
                }
                if ($active === true) {
                    //set all other themes inactive
                    $this->setAllInactive();

                    $theme->setActive($active);
                }
                $this->save($theme);

                //return theme
                $this->getSerializer()->setMaxNestingDepth(0);
                return $this->getSerializer()->toArray($theme);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function create($data)
    {

    }

    public function remove($id)
    {
        $this->getSerializer()->setMaxNestingDepth(0);
        /* @var $theme AbstractTheme */
        $theme = $this->getDm()->createQueryBuilder(self::CLASS_MODEL)
                ->field('stores')->in([$this->getStoreSubDomain()])
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();
        if ($theme) {
            $this->getSoftDelete()->softDelete($theme, $this->getDm()->getClassMetadata(get_class($theme)));
            $this->getDm()->flush();

            return json_encode(['error' => false, 'message' => 'Theme deleted']);
        } else {
            return json_encode(['error' => true, 'message' => 'Could not delete the theme']);
        }
    }

    public function update($id, $data)
    {
        //set this theme
        $theme = $this->getUnserializer()->fromArray($data, self::CLASS_MODEL);

        $this->save($theme);

        return $this->getSerializer()->toJson($theme);
    }

    /**
     *
     * @param string|int $id
     * @return json
     */
    public function get($id)
    {
        $this->getSerializer()->setMaxNestingDepth(10);
        $theme = $this->getDm()->createQueryBuilder(self::CLASS_MODEL)
                ->field('stores')->in([$this->getStoreSubDomain()])
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();
        if (!empty($theme)) {
            return $this->getSerializer()->toJson($theme);
        } else {
            return json_encode(['error' => true, 'message' => 'Could not find theme']);
        }
    }

    public function getList()
    {
        $this->getSerializer()->setMaxNestingDepth(0);
        $themes = $this->getDm()->createQueryBuilder(self::CLASS_MODEL)
                ->field('stores')->in([$this->getStoreSubDomain()])
                ->getQuery();
        if (!empty($themes)) {
            $themeArray = [];
            foreach ($themes as $theme) {
                $themeArray[] = $this->getSerializer()->toArray($theme);
            }
            return json_encode($themeArray);
        } else {
            return json_encode(['error' => true, 'message' => 'Could not find theme']);
        }
    }

    public function getActive()
    {
        $this->getSerializer()->setMaxNestingDepth(10);
        $theme = $this->getDm()->createQueryBuilder(self::CLASS_MODEL)
                ->field('stores')->in([$this->getStoreSubDomain()])
                ->field('active')->equals(true)
                ->getQuery()
                ->getSingleResult();
        if (!empty($theme)) {
            return $this->getSerializer()->toJson($theme);
        } else {
            return json_encode(['error' => true, 'message' => 'Could not find theme']);
        }
    }

    private function setAllInactive()
    {
        $themes = $this->getDm()->createQueryBuilder(self::CLASS_MODEL)
                ->field('stores')->in([$this->getStoreSubDomain()])
                ->field('active')->equals(true)
                ->getQuery();
        if (!empty($themes)) {
            foreach ($themes as $theme) {
                $theme->setActive(false);
                $this->saveTheme($theme);
            }
        }
        return true;
    }

    /**
     *
     * @return strings
     */
    public function getStoreSubDomain()
    {
        return $this->getStore()->getSubDomain();
    }

    /**
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     *
     * @param Store $store
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
    }

    /**
     *
     * @return DocumentManager
     */
    public function getDm()
    {
        return $this->dm;
    }

    /**
     *
     * @param DocumentManager $dm
     */
    public function setDm(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    private function saveTheme(ThemeInterface $theme)
    {
        $this->getDm()->persist($theme);
        $this->getDm()->flush();
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

                $this->getDm()->persist($asset);
                $this->getDm()->flush();
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
}
