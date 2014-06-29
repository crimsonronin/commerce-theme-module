<?php

namespace Zoop\Theme\Test\Bridge;

use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\Bridge\BridgeManager;
use Zoop\Theme\Bridge\Checkout as CheckoutBridge;
use Zoop\Theme\Bridge\Collection as CollectionBridge;
use Zoop\Theme\Bridge\Collections as CollectionsBridge;
use Zoop\Theme\Bridge\Download as DownloadBridge;
use Zoop\Theme\Bridge\Information as InformationBridge;
use Zoop\Theme\Bridge\Linklists as LinklistsBridge;
use Zoop\Theme\Bridge\Search as SearchBridge;
use Zoop\Theme\Bridge\Store as StoreBridge;
use Zoop\Theme\Bridge\Site as SiteBridge;
use Zoop\Theme\Bridge\Menu as MenuBridge;
use Zoop\Theme\Bridge\Page as PageBridge;
use Zoop\Theme\Bridge\Paginate as PaginateBridge;
use Zoop\Theme\Bridge\Product as ProductBridge;
use Zoop\Theme\Bridge\Order as OrderBridge;

class BridgeManagerTest extends AbstractTest
{
    public function testCheckoutBridge()
    {
        $bm = new BridgeManager;
        $bm->addBridge('checkout', new CheckoutBridge);

        $preVars = include __DIR__ . '/Assets/checkout.php';

        $vars = $bm->getVariables($preVars);

        $this->assertEquals($preVars, $vars);
    }

    public function testCollectionBridge()
    {
        $bm = new BridgeManager;
        $bm->addBridge('collection', new CollectionBridge);

        $preVars = include __DIR__ . '/Assets/collection.php';

        $vars = $bm->getVariables($preVars);

        $this->assertTrue(isset($vars['collection']));
        $collection = $vars['collection'];
        $legacyCollection = $preVars['collection'];

        $this->assertEquals($collection['name'], $legacyCollection['categoryName']);
        $this->assertEquals($collection['slug'], $legacyCollection['categoryUrlSlug']);
        $this->assertEquals($collection['naturalSortName'], $legacyCollection['categoryOrderName']);
        $this->assertCount(1, $collection['imageSets']);
        $this->assertCount(1, $collection['children']);
        $this->assertEquals(
            $collection['children'][0]['name'],
            $legacyCollection['categoryChildren'][0]['categoryOrderName']
        );
    }
}
