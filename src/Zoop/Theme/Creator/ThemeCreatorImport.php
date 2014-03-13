<?php

namespace Zoop\Theme\Creator;

use \DirectoryIterator;
use \Exception;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \SplFileInfo;
use \ZipArchive;
use Zend\Validator\File;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\Serializer\Asset\Unserializer as AssetUnserializer;

/**
 *
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class ThemeCreatorImport extends AbstractThemeCreator implements ThemeCreatorImportInterface
{
    private $uploadedFile;
    private $tempDirectory;
    private $tempThemeDirectory;
    private $maxFileUploadSize = 20971520; //20MB
    private $additionalErrorMessages;
    private $assetUnserializer;

    /**
     *
     * @return ThemeInterface|boolean
     * @throws Exception
     */
    public function create(SplFileInfo $uploadedFile)
    {
        if (!empty($uploadedFile)) {
            if ($this->isValidUpload($uploadedFile) === true) {

                $tempDir = $this->getTempThemeDirectory();

                if ($this->unZipTheme($uploadedFile->getPathname(), $tempDir) === true) {
                    //set theme name
                    $this->getTheme()->setName(str_replace('.zip', '', $uploadedFile->getFilename()));

                    //set assets from dir
                    $assets = $this->getAssetsFromDirectory($tempDir);

                    $this->setAssets($assets, true);

                    return $this->getTheme();
                } else {
                    throw new Exception('Could not unzip the file "' . $uploadedFile->getFilename() . '" into "' . $tempDir . '"');
                }
            }
        }

        return false;
    }

    private function getAssetsFromDirectory($directory, AssetInterface $parent = null)
    {
        $assets = [];

        $files = new DirectoryIterator($directory);

        /* @var $file DirectoryIterator */
        foreach ($files as $file) {
            if (($file->isDir() || $file->isFile()) && !$file->isDot()) {
                $asset = $this->getAsset($file->getPathname());

                if ($asset instanceof AssetInterface) {
                    $assets[] = $asset;

                    if (!is_null($parent)) {
                        $asset->setParent($parent);
                    }

                    if ($file->isDir()) {
                        $childAssets = $this->getAssetsFromDirectory($file->getPathname(), $asset);
                        $asset->setAssets($childAssets);
                    }
                }
            }
        }

        return $assets;
    }

    /**
     * @return AssetInterface
     */
    private function getAsset($pathname)
    {
        return $this->getAssetUnserializer()->fromFile(new SplFileInfo($pathname));
    }

    private function isValidUpload(SplFileInfo $uploadedFile)
    {
        $fileSizeValidator = new File\Size($this->getMaxFileUploadSize());
        $zipValidator = new File\IsCompressed();

        if (!$zipValidator->isValid($uploadedFile->getPathname())) {
            throw new Exception('The file "' . $uploadedFile->getFilename() . '" is not a zip archive');
        } elseif (!$fileSizeValidator->isValid($uploadedFile->getPathname())) {
            throw new Exception('Exceeds the maximum file size of ' . $this->getMaxFileUploadSize() / 1024 / 1024 . 'MB');
        }
        return true;
    }

    /**
     * @return AssetUnserializer
     */
    public function getAssetUnserializer()
    {
        if (empty($this->assetUnserializer)) {
            $unserializer = new AssetUnserializer();
            $unserializer->setTempDirectory($this->getTempThemeDirectory());

            $this->setAssetUnserializer($unserializer);
        }

        return $this->assetUnserializer;
    }

    public function setAssetUnserializer(AssetUnserializer $assetUnserializer)
    {
        $this->assetUnserializer = $assetUnserializer;
    }

    public function getMaxFileUploadSize()
    {
        return $this->maxFileUploadSize;
    }

    private function getTempThemeDirectory()
    {
        return $this->tempThemeDirectory;
    }

    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    public function setMaxFileUploadSize($maxFileUploadSize)
    {
        $this->maxFileUploadSize = (int) $maxFileUploadSize;
    }

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

    private function setTempThemeDirectory($tempDirectory)
    {
        $this->tempThemeDirectory = $tempDirectory;
        return $this;
    }

    private function createDirectory($base, $name)
    {
        $dir = $base . '/' . $name;
        if (!is_dir($dir)) {
            mkdir($dir, 755, true);
        }
        return $dir;
    }

    private function unZipTheme($file, $dir)
    {
        $zip = new ZipArchive;
        if ($zip->open($file) === true) {
            $zip->extractTo($dir);
            $zip->close();
            return true;
        }
        return false;
    }

    public function getAdditionalErrorMessages()
    {
        return $this->additionalErrorMessages;
    }

    public function setAdditionalErrorMessages($additionalErrorMessages)
    {
        $this->additionalErrorMessages = [];
        if (is_array($additionalErrorMessages)) {
            foreach ($additionalErrorMessages as $error) {
                $this->additionalErrorMessages[] = $error['message'];
            }
        }
    }

    public function addAdditionalErrorMessage($errorMessage)
    {
        if (!empty($errorMessage)) {
            $this->additionalErrorMessages[] = $errorMessage;
        }
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

    public function __destruct()
    {
        $this->deleteTempDirectory($this->getTempThemeDirectory());
    }
}
