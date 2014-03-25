<?php

namespace Zoop\Theme\Tokenizer\Token;

abstract class AbstractFileToken
{
    protected $url;
    protected $tempDirectory;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    public function setTempDirectory($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }
}
