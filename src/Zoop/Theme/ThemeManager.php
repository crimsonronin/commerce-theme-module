<?php

namespace Zoop\Theme;

use \Exception;
use \DateTime;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \DirectoryIterator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Theme\AssetManager;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\PrivateTheme as PrivateThemeModel;
use Zoop\Theme\DataModel\SharedTheme as SharedThemeModel;
use Zoop\Theme\DataModel\ZoopTheme as ZoopThemeModel;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\Template as TemplateModel;
use Zoop\Theme\Validator;

class ThemeManager
{
    const S3_TEMPLATE_ROOT = 'storefront/%s/templates/%s';

    private $cloudfrontEndpoint;
    private $s3Bucket;
    private $s3Folder;
    private $tempDirectory;
    private $validator;
    private $dm;
    private $theme;
    private $assetManager;

    /**
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @return DocumentManager
     */
    public function getDm()
    {
        return $this->dm;
    }

    /**
     * @return string
     */
    public function getCloudfrontEndpoint()
    {
        return $this->cloudfrontEndpoint;
    }

    /**
     * @return string
     */
    public function getS3Bucket()
    {
        return $this->s3Bucket;
    }

    /**
     * @return string
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * @return ThemeInterface
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     *
     * @param Validator $validator
     * @return ThemeManager
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     *
     * @param DocumentManager $dm
     * @return ThemeManager
     */
    public function setDm(DocumentManager $dm)
    {
        $this->dm = $dm;
        return $this;
    }

    /**
     *
     * @param string $cloudfrontEndpoint
     * @return ThemeManager
     */
    public function setCloudfrontEndpoint($cloudfrontEndpoint)
    {
        $this->cloudfrontEndpoint = $cloudfrontEndpoint;
        return $this;
    }

    /**
     *
     * @param string $s3Bucket
     * @return ThemeManager
     */
    public function setS3Bucket($s3Bucket)
    {
        $this->s3Bucket = $s3Bucket;
        return $this;
    }

    /**
     *
     * @param string $tempDirectory
     * @return ThemeManager
     */
    public function setTempDirectory($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
        return $this;
    }

    /**
     *
     * @param ThemeInterface $theme
     * @return ThemeManager
     */
    public function setTheme(ThemeInterface $theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return AssetManager
     */
    public function getAssetManager()
    {
        return $this->assetManager;
    }

    /**
     *
     * @param AssetManager $assetManager
     * @return ThemeManager
     */
    public function setAssetManager(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
        return $this;
    }

    /**
     *
     * @param string $name
     * @return ThemeManager
     */
    public function setName($name)
    {
        $this->getTheme()->setName($name);
        return $this;
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->getTheme()->getAssets();
    }

    /**
     *
     * @return boolean
     */
    public function validate()
    {
        return $this->getValidator()->validate($this->getTheme());
    }

    /**
     * @param boolean $setIsActive
     */
    public function save($setIsActive = true)
    {
        $theme = $this->getTheme();
        $assets = $theme->getAssets();

        //save the theme
        $this->saveTheme($theme);

        //set the s3 folder from the theme name
        $this->setS3Folder($theme);

        //parse assets to create additional assets
        $this->createAdditionalAssets($assets);

        //save assets
        $this->saveRecursively($theme, $assets);

        if ($setIsActive === true) {
            $theme->setIsActive(true);
        }

        //final
        $this->saveTheme($theme);
    }

    /**
     * @param ThemeInterface $theme
     */
    public function saveTheme(ThemeInterface $theme)
    {
        $this->getDm()->persist($theme);
        $this->getDm()->flush();
    }

    /**
     *
     * @param ThemeInterface $theme
     * @param array $assets
     */
    private function saveRecursively(ThemeInterface $theme, $assets)
    {
        if (!empty($assets)) {
            /* @var $asset AssetInterface */
            foreach ($assets as $asset) {
                $parent = $asset->getParent();
                if (empty($parent)) {
                    $asset->setParent($theme);
                }
                $asset->setTheme($theme);

                $this->getDm()->persist($asset);
                $this->getDm()->flush();
            }

            //look for folders and recurse
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel) {
                    $childAssets = $asset->getAssets();
                    if (!empty($childAssets)) {
                        $this->saveRecursively($theme, $childAssets);
                    }
                }
            }
        }
    }

    /**
     *
     * @return string
     */
    public function getS3Folder()
    {
        return $this->s3Folder;
    }

    /**
     *
     * @param ThemeInterface $theme
     */
    public function setS3Folder(ThemeInterface $theme)
    {
        if ($theme instanceof PrivateThemeModel) {
            $storeSlug = $theme->getStores()[0];
            $themeId = $theme->getId();
            $this->s3Folder = sprintf(self::S3_TEMPLATE_ROOT, $storeSlug, $themeId);
        }
    }

    /**
     *
     * @param AssetInterface $asset
     */
    public function addAsset(AssetInterface $asset)
    {
        $this->getTheme()->addAsset($asset);
    }

    /**
     *
     * @param array $assets
     */
    public function addAssets($assets)
    {
        if (is_array($assets) && !empty($assets)) {
            foreach ($assets as $asset) {
                $this->addAsset($asset);
            }
        }
    }

    /**
     *
     * @param string $directory
     * @return boolean
     */
    public function setAssetsFromDirectory($directory)
    {
        $assets = $this->createAssetsFromDirectory($directory);
        if (!empty($assets)) {
            $this->addAssets($assets);
            return true;
        }
        return false;
    }

    /**
     * @param string $directory
     * @param FolderModel $parent
     * @return array
     */
    private function createAssetsFromDirectory($directory, FolderModel $parent = null)
    {
        $assets = [];

        $files = new DirectoryIterator($directory);

        /* @var $file DirectoryIterator */
        foreach ($files as $file) {
            $asset = $this->getAssetManager()->createAsset($file, $parent);

            if (!empty($asset)) {
                if ($asset instanceof FolderModel) {
                    $childAssets = $this->createAssetsFromDirectory($directory . '/' . $asset->getName(), $asset);
                    $asset->setAssets($childAssets);
                }
                $assets[] = $asset;
            }
        }

        return $assets;
    }

    /**
     *
     * @param string $path
     * @param string $name
     * @param array $assets
     * @return boolean|AssetInterface
     */
    public function findAsset($path, $name, $assets = null)
    {
        if (!is_null($assets)) {
            $assets = $this->getAssets();
        }

        $all = explode("/", $path)[0];
        $current = $all[0];

        if (!empty($assets) && is_array($assets)) {
            for ($i = 0; $i < count($assets); $i++) {
                if ($assets[$i]->getName() == $current && count($all) == 1) {
                    return $assets[$i];
                } elseif ($assets[$i] instanceof FolderModel && $assets[$i]->getName() == $current) {
                    $childAsset = $this->findAsset(implode('/', array_shift($all)), $name, $assets[$i]->getAssets());

                    if ($childAsset !== false) {
                        return $childAsset;
                    }
                }
            }
        }

        return false;
    }

    /**
     *
     * @param array $assets
     */
    private function createAdditionalAssets($assets)
    {
        $additionalAssets = $this->createAssetsFromHtml($assets);
        //add additional js and css assets to the asset pool so we can check them for assets too
    }

    /**
     *
     * @param array $assets
     */
    private function createAssetsFromHtml($assets)
    {
        if (!empty($assets) && is_array($assets)) {
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel) {
                    $this->createAssetsFromHtml($asset->getAssets());
                } elseif ($asset instanceof TemplateModel) {
                    $contentAssets = $this->getAssetManager()->getAssetsFromContent($asset);
                }
            }
        }
    }

    /**
     *
     * @param array $additionalAssets
     * @param string $assetPath
     * @param string $mimeRegex
     * @param string $type
     * @return array
     */
    private function getAssetFromContent($additionalAssets, $assetPath, $mimeRegex, $type)
    {
        $parsedAssets = [];

        if (!empty($additionalAssets)) {
            foreach ($additionalAssets as $key => $url) {
                $file = new FileParser($url, $this->getCloudfrontEndpoint(), $this->getTempDirectory());

                //if we get a relative file, we need to double check that it exists and update the url reference
                if ($file->getRelative() === true) {
                    //check for existing asset
                    $existingAsset = $this->getAsset($this->getAssets(), $file->getPath(), $file->getName());

                    if (!empty($existingAsset)) {
                        $parsedAssets[$key] = $this->getAssetUrl($existingAsset);
                    }
                } else {
                    $file->setAssetPath($assetPath);
                    $file->saveLocal();

                    if ($file->isValid($mimeRegex)) {
                        //check for existing asset
                        //create new css asset
                        $this->createAssetsFromDirectory($file->getTempDirectory());

                        //save to S3
                        $newAssetUrl = $this->saveAssetToS3(
                            file_get_contents($file->get()),
                            $file->getMime(),
                            $assetPath . $file->getPath(),
                            $file->getName()
                        );

                        //replace url to s3
                        if (!empty($newAssetUrl)) {
                            $parsedAssets[$key] = $newAssetUrl;
                        }
                    } else {
                        //add to errors
                        $this->addAdditionalErrorMessage('"' . $url . '" is not a valid ' . $type . ' file');
                    }
                }
                if (!isset($parsedAssets[$key])) {
                    $parsedAssets[$key] = $url;
                }
                unset($file);
            }
        }

        return $parsedAssets;
    }
}
