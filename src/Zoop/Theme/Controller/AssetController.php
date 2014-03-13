<?php

namespace Zoop\Theme\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Store\DataModel\Store;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Shard\SoftDelete\SoftDeleter;
use Zoop\Shard\Serializer\Serializer;
use Zoop\Shard\Serializer\Unserializer;

/**
 *
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class AssetController
{
    const CLASS_MODEL = 'Zoop\Theme\DataModel\AbstractAsset';

    private $dm;
    private $store;
    private $softDelete;
    private $serializer;
    private $unserializer;

    public function create($data)
    {

    }

    public function remove($id)
    {
        $this->getSerializer()->setMaxNestingDepth(0);
        /* @var $asset AbstractAsset */
        $asset = $this->getDm()->createQueryBuilder(self::CLASS_MODEL)
                ->field('stores')->in([$this->getStoreSubDomain()])
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();
        if ($asset) {
            $this->getSoftDelete()->softDelete($asset, $this->getDm()->getClassMetadata(get_class($asset)));
            $this->getDm()->flush();

            return json_encode(['error' => false, 'message' => 'Asset deleted']);
        } else {
            return json_encode(['error' => true, 'message' => 'Could not delete the asset']);
        }
    }

    public function update($id, $data)
    {
        //set this asset
        $asset = $this->getUnserializer()->fromArray($data, self::CLASS_MODEL, null, 'unserialize_patch');

        $this->saveTheme($asset);

        return $asset;
    }

    /**
     *
     * @param string|int $id
     * @return json
     */
    public function get($id)
    {
        $this->getSerializer()->setMaxNestingDepth(10);
        $asset = $this->getDm()->createQueryBuilder(self::CLASS_MODEL)
                ->field('stores')->in([$this->getStoreSubDomain()])
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();
        if (!empty($asset)) {
            return $this->getSerializer()->toJson($asset);
        } else {
            return json_encode(['error' => true, 'message' => 'Could not find asset']);
        }
    }

    public function getList()
    {
        $this->getSerializer()->setMaxNestingDepth(0);
        $assets = $this->getDm()->createQueryBuilder(self::CLASS_MODEL)
                ->field('stores')->in([$this->getStoreSubDomain()])
                ->getQuery();
        if (!empty($assets)) {
            $assetArray = [];
            foreach ($assets as $asset) {
                $assetArray[] = $this->getSerializer()->toArray($asset);
            }
            return json_encode($assetArray);
        } else {
            return json_encode(['error' => true, 'message' => 'Could not find asset']);
        }
    }

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

    private function save(AssetInterface $asset)
    {
        $this->getDm()->persist($asset);
        $this->getDm()->flush();
    }
}
