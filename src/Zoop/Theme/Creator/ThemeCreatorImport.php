<?php

namespace Zoop\Theme\Creator;

use \DirectoryIterator;
use \Exception;
use \SplFileInfo;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\Serializer\Asset\Unserializer as AssetUnserializer;
use Zoop\Theme\Helper\FileHelperTrait;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class ThemeCreatorImport extends AbstractThemeCreator implements ThemeCreatorImportInterface
{
    use FileHelperTrait;
    
    protected $additionalErrorMessages;
    protected $assetUnserializer;

    public function __construct(
        Unserializer $unserializer,
        $tempDirectory,
        $maxFileUploadSize = 20971520
    ) {
        $this->setUnserializer($unserializer);
        $this->setTempDirectory($tempDirectory);
        $this->setMaxFileUploadSize($maxFileUploadSize);
    }

    /**
     * @return boolean
     * @throws Exception
     */
    public function import(SplFileInfo $uploadedFile)
    {
        if (!empty($uploadedFile)) {
            if ($this->isValidUpload($uploadedFile) === true) {

                $tempDir = $this->getTempThemeDirectory();

                if ($this->unzipTheme($uploadedFile->getPathname(), $tempDir) === true) {
                    $theme = $this->getTheme();
                    
                    //set theme name
                    $theme->setName(str_replace('.zip', '', $uploadedFile->getFilename()));

                    //get assets from directory
                    $assets = $this->getAssetsFromDirectory($tempDir);
                    $theme->setAssets($assets);
                    
                    return true;
                } else {
                    throw new Exception(
                        sprintf(
                            'Could not unzip the file "%s" into "%s"',
                            $uploadedFile->getFilename(),
                            $tempDir
                        )
                    );
                }
            }
        }

        return false;
    }

    /**
     * Loops through the directory to parse assets.
     * 
     * @param type $directory
     * @param AssetInterface $parent
     * @return AssetInterface
     */
    protected function getAssetsFromDirectory($directory, AssetInterface $parent = null)
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
    protected function getAsset($pathname)
    {
        return $this->getAssetUnserializer()
            ->fromFile(new SplFileInfo($pathname));
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

    /**
     * @param AssetUnserializer $assetUnserializer
     */
    public function setAssetUnserializer(AssetUnserializer $assetUnserializer)
    {
        $this->assetUnserializer = $assetUnserializer;
    }

    /**
     * @return array
     */
    public function getAdditionalErrorMessages()
    {
        return $this->additionalErrorMessages;
    }

    /**
     * @param array $additionalErrorMessages
     */
    public function setAdditionalErrorMessages($additionalErrorMessages)
    {
        $this->additionalErrorMessages = [];
        if (is_array($additionalErrorMessages)) {
            foreach ($additionalErrorMessages as $error) {
                $this->additionalErrorMessages[] = $error['message'];
            }
        }
    }

    /**
     * @param string $errorMessage
     */
    public function addAdditionalErrorMessage($errorMessage)
    {
        if (!empty($errorMessage)) {
            $this->additionalErrorMessages[] = $errorMessage;
        }
    }

    public function __destruct()
    {
        $this->deleteTempDirectory($this->getTempThemeDirectory());
    }
}
