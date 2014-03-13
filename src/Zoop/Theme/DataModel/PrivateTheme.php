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

    public function getStores()
    {
        return $this->stores;
    }

    public function setStores(array $stores)
    {
        $this->stores = $stores;
    }

    public function addStore($store)
    {
        if (!empty($store)) {
            $this->stores[] = $store;
        }
    }

    public function getLegacyStoreId()
    {
        return $this->legacyStoreId;
    }

    public function setLegacyStoreId($legacyStoreId)
    {
        $this->legacyStoreId = (int) $legacyStoreId;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

}
