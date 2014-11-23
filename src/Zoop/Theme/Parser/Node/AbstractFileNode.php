<?php

namespace Zoop\Theme\Parser\Node;

use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\Tokenizer\Token\AbstractFileToken;
use Zoop\Theme\Tokenizer\Token\AbstractAbsoluteFileToken;
use Zoop\Theme\Tokenizer\Token\AbstractRelativeFileToken;

abstract class AbstractFileNode extends AbstractNode
{
    protected $filename;
    protected $pathname;
    protected $path;

    protected function parseFileModel(AssetInterface $model)
    {
        $token = $this->getToken();

        if ($token instanceof AbstractAbsoluteFileToken) {
            $url = $token->getUrl();
            $tempDir = $token->getTempDirectory();
            $pathname = $this->getPathnameFromUrl($url);
            $localPathname = $tempDir . '/' . $pathname;

            if ($this->saveLocal($url, $localPathname)) {
                $model->setName($this->getFileName($localPathname));
                $model->setPathname($pathname);
                $model->setPath($this->getPath($pathname));
                $model->setMime($this->getMime($localPathname));
            }
        } elseif ($token instanceof AbstractRelativeFileToken) {
            $pathname = $token->getUrl();
            $tempDir = $token->getTempDirectory();
            $localPathname = $tempDir . '/' . $pathname;

            $model->setName($this->getFileName($localPathname));
            $model->setPathname($pathname);
            $model->setPath($this->getPath($pathname));
            $model->setMime($this->getMime($localPathname));
        }
    }

    /**
     * @param string $url
     * @throws Exception
     */
    protected function saveLocal($url, $localPathname)
    {
        if (!empty($url)) {
            $path = $this->getPath($localPathname);
            if (!is_dir($path)) {
                $this->createDirectory($path);
            }

            if (!copy($url, $localPathname)) {
                throw new Exception('Could not asset copy the asset from "' . $url . '" to "' . $localPathname . '"');
            } else {
                return true;
            }
        } else {
            throw new Exception('The asset could not be saved locally');
        }
    }

    /**
     *
     * @param string $base
     * @param string $name
     * @return string
     */
    protected function createDirectory($base, $name = null)
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

    /**
     * @param string $url
     * @return string
     */
    protected function getPathnameFromUrl($url)
    {
        if (!empty($url)) {
            if (preg_match('/^[http(s*)\:\/\/|\/\/](.*)/', $url)) {
                $urlParts = parse_url($url);

                return ltrim(strtolower(trim($urlParts['path'])), '/');
            } else {
                return ltrim(strtolower(str_replace(['../', './'], '', $url)), '/');
            }
        }
    }

    /**
     * @param string $pathname
     * @return string
     */
    protected function getFileName($pathname)
    {
        $parts = explode('/', $pathname);
        $name = end($parts);
        return $name;
    }

    /**
     * @param string $pathname
     * @return string
     */
    protected function getPath($pathname)
    {
        $parts = explode('/', $pathname);
        array_pop($parts);
        return implode($parts, '/');
    }

    /**
     * @param string $pathname
     * @return string
     */
    public function getMime($pathname)
    {
        $finfo = new \finfo(FILEINFO_MIME);
        $mimetype = $finfo->file($pathname);
        $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);

        return strtolower($mimetypeParts[0]);
    }

    /**
     * @param string $pathname
     * @return string
     */
    public function getExtension($pathname)
    {
        return strtolower(pathinfo($pathname, PATHINFO_EXTENSION));
    }
}
