<?php

namespace Zoop\Theme\Parser;

use \Exception;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \SplFileInfo;

abstract class AbstractFile
{

    private $url;
    private $cloudfrontEndpoint;
    private $tempDirectory;
    private $assetPath;
    private $relative = false;
    private $saved = false;

    public function __construct($url, $cloudfrontEndpoint, $tempDirectory)
    {
        $this->setUrl($url);
        $this->setCloudfrontEndpoint($cloudfrontEndpoint);
        $this->setTempDirectory($tempDirectory);
    }

    public function __destruct()
    {
        $this->deleteTempDirectory($this->getTempDirectory());
    }

    public function getCloudfrontEndpoint()
    {
        return $this->cloudfrontEndpoint;
    }

    public function setCloudfrontEndpoint($cloudfrontEndpoint)
    {
        $this->cloudfrontEndpoint = $cloudfrontEndpoint;
    }

    public function getAssetPath()
    {
        return $this->assetPath;
    }

    public function setAssetPath($assetPath)
    {
        $this->assetPath = $assetPath;
    }

    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    public function setTempDirectory($tempDirectory)
    {
        if (is_dir($tempDirectory)) {
            $this->tempDirectory = $this->createDirectory($tempDirectory, uniqid(null, true));
        } else {
            throw new Exception('The directory "' . $tempDirectory . '" does not exist');
        }
        return $this;
    }

    public function saveLocal()
    {
        $url = $this->getUrl();
        if (!empty($url)) {
            $local = $this->getLocalPathName();

            if (!copy($url, $local)) {
                throw new Exception('Could not asset copy the asset from "' . $url . '" to "' . $local . '"');
            } else {
                $this->setSaved(true);
            }
        } else {
            throw new Exception('The asset could not be saved locally');
        }
    }

    public function isValid($mimeRegEx)
    {
        $filePathname = $this->getLocalPathName();
        if (!empty($filePathname)) {
            if (preg_match($mimeRegEx, $this->getMime())) {
                return true;
            }
        }
        return false;
    }

    public function getMime()
    {
        $filePathname = $this->getLocalPathName();
        if (!empty($filePathname)) {
            return mime_content_type($filePathname);
        }
        return false;
    }

    public function get()
    {
        if ($this->getSaved() === true) {
            return $this->getLocalPathName();
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $urlParts = parse_url($url);
        if (empty($urlParts['scheme'])) {
            //check for // as it's still absolute it's just missing a scheme
            $this->setRelative(true);
        }
        $this->url = $url;
    }

    public function getRelative()
    {
        return $this->relative;
    }

    public function setRelative($relative)
    {
        $this->relative = (bool) $relative;
    }

    public function getSaved()
    {
        return $this->saved;
    }

    public function setSaved($saved)
    {
        $this->saved = (bool) $saved;
    }

    public function getPath()
    {
        $pathName = $this->getPathName();

        $pathParts = explode('/', $pathName);
        if (count($pathParts) > 1) {
            array_pop($pathParts);
            return implode('/', $pathParts);
        }

        return implode('/', $pathParts);
    }

    public function getPathName()
    {
        $url = $this->getUrl();

        if (!empty($url)) {
            if (preg_match('/^[http(s*)\:\/\/|\/\/](.*)/', $url)) {
                $urlParts = parse_url($this->getUrl());
                return strtolower(trim($urlParts['path']));
            } else {
                return strtolower(str_replace(['../', './'], '', $url));
            }
        }
    }

    public function getName()
    {
        $pathName = $this->getPathName();
        $pathParts = explode('/', $pathName);
        $name = end($pathParts);
        return $name;
    }

    private function getLocalPathName()
    {
        $tempDir = $this->getTempDirectory();
        $asset = $this->getAssetPath();
        $dir = (!empty($asset) ? '/' . $asset : '') . $this->getPath();
        $name = $this->getName();
        $this->createDirectory($tempDir, $dir);

        return $tempDir . $dir . '/' . $name;
    }

    private function createDirectory($base, $name = null)
    {
        if (!empty($name)) {
            $dir = $base . '/' . $name;
        } else {
            $dir = $base;
        }
        if (!is_dir($dir)) {
            mkdir($dir, 755, true);
        }
        return $dir;
    }

    private function deleteTempDirectory($dir)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
        /* @var $file \SplFileInfo */
        foreach ($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

}
