<?php

namespace Zoop\Theme\Manager;

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

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LegacyTemplateManager extends TemplateManager implements TemplateManagerInterface
{
    private $bridgeManager;

    public function __construct()
    {
        $this->setBridgeManager(new BridgeManager());
    }

    public function getBridgeManager()
    {
        return $this->bridgeManager;
    }

    public function setBridgeManager(BridgeManager $bridgeManager)
    {
        $bridgeManager->addBridge('checkout', new CheckoutBridge);
        $bridgeManager->addBridge('collection', new CollectionBridge);
        $bridgeManager->addBridge('collections', new CollectionsBridge);
        $bridgeManager->addBridge('download', new DownloadBridge);
        $bridgeManager->addBridge('information', new InformationBridge);
        $bridgeManager->addBridge('linklists', new LinklistsBridge);
        $bridgeManager->addBridge('menu', new MenuBridge);
        $bridgeManager->addBridge('order', new OrderBridge);
        $bridgeManager->addBridge('page', new PageBridge);
        $bridgeManager->addBridge('paginate', new PaginateBridge);
        $bridgeManager->addBridge('product', new ProductBridge);
        $bridgeManager->addBridge('site', new SiteBridge);
        $bridgeManager->addBridge('store', new StoreBridge);
        $bridgeManager->addBridge('search', new SearchBridge);
        $this->bridgeManager = $bridgeManager;
    }

    public function getBridgedVariables()
    {
        return $this->getBridgeManager()->getVariables($this->getVariables());
    }

    public function render()
    {
        return $this->load($this->getFile(), $this->getBridgedVariables());
    }
}
