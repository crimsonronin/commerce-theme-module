<?php

namespace Zoop\Theme\Parser;

use \SplFileInfo;

class Image_1
{

    private $url;
    private $tempDirectory;

    /* @return \SplFileInfo */

    public function createTempImage()
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
        if (is_dir($tempDirectory)) {
            $this->tempDirectory = $tempDirectory;
        } else {
            throw new Exception('The directory "' . $tempDirectory . '" does not exist');
        }
        return $this;
    }

}
