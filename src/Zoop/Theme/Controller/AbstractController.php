<?php

namespace Zoop\Theme\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zoop\Store\DataModel\Store;
use Zoop\Shard\Manifest;
use Zoop\Shard\SoftDelete\SoftDeleter;
use Zoop\Shard\Serializer\Serializer;
use Zoop\Shard\Serializer\Unserializer;

/**
 *
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
abstract class AbstractController extends AbstractActionController
{
    protected $dm;
    protected $manifest;
    protected $store;
    protected $softDelete;
    protected $serializer;
    protected $unserializer;

    /**
     * @return Manifest
     */
    public function getManifest()
    {
        if (!isset($this->manifest)) {
            $this->manifest = $this->getServiceLocator()->get('shard.commerce.manifest');
        }
        return $this->manifest;
    }

    /**
     *
     * @return SoftDeleter
     */
    public function getSoftDelete()
    {
        if (!isset($this->softDelete)) {
            $this->softDelete = $this->getManifest()->getServiceManager()->get('softdeleter');
        }
        return $this->softDelete;
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        if (!isset($this->serializer)) {
            $this->serializer = $this->getManifest()->getServiceManager()->get('serializer');
        }
        return $this->serializer;
    }

    /**
     * @return Unserializer
     */
    public function getUnserializer()
    {
        if (!isset($this->unserializer)) {
            $this->unserializer = $this->getManifest()->getServiceManager()->get('unserializer');
        }
        return $this->unserializer;
    }

    /**
     *
     * @return Store
     */
    public function getStore()
    {
        if (!isset($this->store)) {
            $this->store = $this->getServiceLocator()->get('zoop.commerce.entity.active');
        }
        return $this->store;
    }

    /**
     *
     * @return DocumentManager
     */
    public function getDm()
    {
        if (!isset($this->dm)) {
            $this->dm = $this->getServiceLocator()->get('shard.commerce.modelmanager');
        }
        return $this->dm;
    }

    /**
     *
     * @return strings
     */
    public function getStoreSubdomain()
    {
        return $this->getStore()->getSubdomain();
    }
}
