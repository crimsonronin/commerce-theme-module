<?php

namespace Zoop\Theme\Bridge;

interface BridgeInterface
{

    public function getVariables();

    public function getData();

    public function setData($data);

    public function getFormat();

    public function setFormat($format);
}
