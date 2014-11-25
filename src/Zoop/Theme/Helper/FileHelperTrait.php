<?php

namespace Zoop\Theme\Helper;


use \Exception;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \SplFileInfo;
use \ZipArchive;
use Zend\Validator\File;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
trait FileHelperTrait
{
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
     * @param string $toDirectory
     * @return boolean
     */
    protected function unzip($file, $toDirectory)
    {
        $zip = new ZipArchive;
        if ($zip->open($file) === true) {
            $zip->extractTo($toDirectory);
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
    protected function isValidUpload(SplFileInfo $uploadedFile, $maxFileUploadSize = null)
    {
        $zipValidator = new File\IsCompressed();

        if (!$zipValidator->isValid($uploadedFile->getPathname())) {
            throw new Exception(sprintf('The file "%s" is not a zip archive', $uploadedFile->getFilename()));
        }

        if (!empty($maxFileUploadSize)) {
            $fileSizeValidator = new File\Size($maxFileUploadSize);

            if (!$fileSizeValidator->isValid($uploadedFile->getPathname())) {
                throw new Exception(
                    sprintf(
                        'Exceeds the maximum file size of %dMB',
                        ($maxFileUploadSize / 1024 / 1024)
                    )
                );
            }
        }
        return true;
    }

    /**
     * @param string $dir
     */
    protected function deleteTempDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

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

    /**
     * @param string $filename
     * @param string $content
     * @return string
     */
    protected function saveFile($tempDir, $filename, $content)
    {
        if (!is_dir($tempDir)) {
            @mkdir($tempDir);
        }

        $filePathname = $tempDir . '/' . $filename;

        file_put_contents($filePathname, $content);
        return $filePathname;
    }

    /**
     * @param string $filename
     */
    protected function removeFile($tempDir, $filename)
    {
        if (strpos($filename, $tempDir) === 0) {
            @unlink($filename);
            @rmdir($tempDir);
        }
    }
}
