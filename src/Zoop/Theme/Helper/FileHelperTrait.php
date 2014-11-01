<?php

namespace Zoop\Theme\Helper;


use \Exception;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \ZipArchive;
use Zend\Validator\File;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
trait FileHelperTrait
{
    protected $tempDirectory;
    protected $tempThemeDirectory;
    protected $maxFileUploadSize = 20971520; //20MB
    
    /**
     * @param string $tempDirectory
     * @return ThemeCreatorImport
     * @throws Exception
     */
    public function setTempDirectory($tempDirectory)
    {
        if (is_dir($tempDirectory)) {
            $this->tempDirectory = $tempDirectory;
            $this->setTempThemeDirectory($this->createDirectory($tempDirectory, uniqid(null, true)));
        } else {
            throw new Exception('The directory "' . $tempDirectory . '" does not exist');
        }
        return $this;
    }
    
    /**
     * @param string $tempDirectory
     * @return ThemeCreatorImport
     */
    protected function setTempThemeDirectory($tempDirectory)
    {
        $this->tempThemeDirectory = $tempDirectory;
        return $this;
    }

    /**
     * @return string
     */
    protected function getTempThemeDirectory()
    {
        return $this->tempThemeDirectory;
    }

    /**
     * @return string
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * @param string $base
     * @param string $name
     * @return string
     */
    protected function createDirectory($base, $name)
    {
        $dir = $base . '/' . $name;
        if (!is_dir($dir)) {
            mkdir($dir, 755, true);
        }
        return $dir;
    }

    /**
     * @param string $file
     * @param string $dir
     * @return boolean
     */
    protected function unzipTheme($file, $dir)
    {
        $zip = new ZipArchive;
        if ($zip->open($file) === true) {
            $zip->extractTo($dir);
            $zip->close();
            return true;
        }
        return false;
    }

    /**
     * Validates the file upload
     * 
     * @param SplFileInfo $uploadedFile
     * @return boolean
     * @throws Exception
     */
    protected function isValidUpload(SplFileInfo $uploadedFile)
    {
        $fileSizeValidator = new File\Size($this->getMaxFileUploadSize());
        $zipValidator = new File\IsCompressed();

        if (!$zipValidator->isValid($uploadedFile->getPathname())) {
            throw new Exception(sprintf('The file "%s" is not a zip archive', $uploadedFile->getFilename()));
        } elseif (!$fileSizeValidator->isValid($uploadedFile->getPathname())) {
            throw new Exception(
                sprintf(
                    'Exceeds the maximum file size of %dMB',
                    ($this->getMaxFileUploadSize() / 1024 / 1024)
                )
            );
        }
        return true;
    }

    /**
     * @return int
     */
    public function getMaxFileUploadSize()
    {
        return $this->maxFileUploadSize;
    }

    /**
     * Sets the max upload size allowed in bytes
     *
     * @param integer $maxFileUploadSize
     */
    public function setMaxFileUploadSize($maxFileUploadSize)
    {
        $this->maxFileUploadSize = (int) $maxFileUploadSize;
    }
    
    /**
     * @param string $dir
     */
    protected function deleteTempDirectory($dir)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::CHILD_FIRST
        );

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
