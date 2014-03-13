<?php

/**
 * @package Zoop
 */

namespace Zoop\SkeletonModule;

/**
 *
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class Module
{
    /**
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }
}
