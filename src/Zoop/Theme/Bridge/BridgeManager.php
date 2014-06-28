<?php

namespace Zoop\Theme\Bridge;

class BridgeManager
{
    private $bridges = [];

    public function getBridges()
    {
        return $this->bridges;
    }

    public function setBridges($bridges)
    {
        $this->bridges = $bridges;
    }

    public function addBridge($key, BridgeInterface $bridge)
    {
        $this->bridges[$key] = new $bridge();
    }

    public function getVariables($data)
    {
        $newData = [];
        /* @var $bridge BridgeInterface */
        foreach ($this->getBridges() as $key => $bridge) {
            if (isset($data[$key])) {
                $bridge->setData($data[$key]);
                $newData[$key] = $bridge->getVariables();
            }
        }

        foreach ($data as $key => $d) {
            if (!isset($newData[$key])) {
                $newData[$key] = $d;
            }
        }

        return $newData;
    }
}
