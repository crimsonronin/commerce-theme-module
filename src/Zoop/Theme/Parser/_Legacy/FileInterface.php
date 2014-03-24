<?php

namespace Zoop\Theme\Parser;

interface FileInterface
{

    public function getName();

    public function getPath();

    public function getUrl();

    public function setUrl($url);
}
