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
     * Array. Stores that this theme is part of.
     * The Zones annotation means this field is used by the Zones filter so
     * only products from the isActive store are available.
     *
     * @ODM\Collection
     * @ODM\Index
     */
    protected $stores = [];

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
    protected $isActive = false;

    public function __construct()
    {
        $this->assets = new ArrayCollection;
    }

    /**
     * @return array
     */
    public function getStores()
    {
        if (!is_array($this->stores)) {
            $this->stores = [];
        }
        return $this->stores;
    }

    /**
     * @param array $stores
     */
    public function setStores(array $stores)
    {
        $this->stores = $stores;
    }

    /**
     * @param string $store
     */
    public function addStore($store)
    {
        if (!empty($store) && in_array($store, $this->getStores()) === false) {
            $this->stores[] = $store;
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
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = (bool) $isActive;
    }
}
