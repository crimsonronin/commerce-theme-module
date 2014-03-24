<?php

namespace Zoop\Theme\DataModel;

use Doctrine\Common\Collections\ArrayCollection;
//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow="*")
 * })
 */
class PrivateTheme extends AbstractTheme implements ThemeInterface
{
    /**
     * Array. Stores that this product is part of.
     * The Zones annotation means this field is used by the Zones filter so
     * only products from the active store are available.
     *
     * @ODM\Collection
     * @ODM\Index
     */
    protected $stores;

    /**
     *
     * @ODM\Int
     * @ODM\Index
     */
    protected $legacyStoreId;

    /**
     *
     * @ODM\String
     */
    protected $createdBy;

    /**
     *
     * @ODM\Boolean
     */
    protected $writeable = true;

    /**
     *
     * @ODM\Boolean
     */
    protected $deleteable = true;

    /**
     *
     * @ODM\Boolean
     * @ODM\Index
     */
    protected $active = false;

    public function __construct()
    {
        $this->assets = new ArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getStores()
    {
        return $this->stores;
    }

    /**
     * @param ArrayCollection $stores
     */
    public function setStores(ArrayCollection $stores)
    {
        $this->stores = $stores;
    }

    /**
     * @param string $store
     */
    public function addStore($store)
    {
        if (!empty($store) && $this->stores->contains($store) === false) {
            $this->stores->add($store);
        }
    }

    /**
     * @return integer
     */
    public function getLegacyStoreId()
    {
        return $this->legacyStoreId;
    }

    /**
     * @param integer $legacyStoreId
     */
    public function setLegacyStoreId($legacyStoreId)
    {
        $this->legacyStoreId = (int) $legacyStoreId;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;
    }
}
