<?php

namespace Zoop\Theme\Tokenizer\Token;

use \SplFileInfo;

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

    /**
     * @return boolean|SplFileInfo
     */
    protected function createTempImage()
    {
        $url = $this->getUrl();
        if (!empty($url)) {
            if (preg_match('/^[http(s*)\:\/\/|\/\/](.*)/', $url)) {
                //absolute
                $image = new AbsoluteImage();
                $image->setUrl($url);
                if ($image->saveTemp() === true) {
                    return new SplFileInfo($image->getPath());
                }
            } else {
                //relative
            }
        }
        return false;
    }

    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    public function setTempDirectory($tempDirectory)
    {
        if (is_dir($tempDirectory)) {
            $this->tempDirectory = $tempDirectory;
        } else {
            throw new Exception('The directory "' . $tempDirectory . '" does not exist');
        }
        return $this;
    }
}
