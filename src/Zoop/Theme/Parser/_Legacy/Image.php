<?php

namespace Zoop\Theme\Parser;

use \SplFileInfo;

/**
 * This class accepts a CSS model and parses it
 * looking for additional CSS file references or Images.
 *
 * We can then process those files separately to ensure
 * that all content lives on our CDN
 *
 * @category   CategoryName
 * @package    Zoop Commerce Theme
 * @author     Josh Stuart <josh.stuart@zoopcommerce.com>
 *
 * @copyright  Zoop Pty Ltd
 */
class Image extends AbstractFileParser implements ParserInterface
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
