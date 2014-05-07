<?php

namespace Zoop\Theme\Tokenizer\Token;

abstract class AbstractFileToken
{
    protected $url;
    protected $tempDirectory;
    protected $filePath;

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
    
    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }
}
