<?php

namespace Zoop\Theme\Service;

use \Exception;
use Zoop\Theme\DataModel\ThemeInterface;

class Compiler
{

    protected function saveAssetsToS3($assets)
    {
        if (is_array($assets) && !empty($assets)) {
            foreach ($assets as $asset) {
                if ($asset instanceof ImageModel || $asset instanceof JavascriptModel || $asset instanceof CssModel) {
                    $url = $this->saveAssetToS3($asset->getContent(), $asset->getMime(), $asset->getPath(), $asset->getName());

                    if ($asset instanceof ImageModel || $asset instanceof JavascriptModel) {
                        $asset->setSrc($url);
                    } else {
                        $asset->setHref($url);
                    }
                } elseif ($asset instanceof GzippedJavascriptModel || $asset instanceof GzippedCssModel) {
                    //add content encoding header to gzip
                    $url = $this->saveAssetToS3($asset->getContent(), $asset->getMime(), $asset->getPath(), $asset->getName());

                    if ($asset instanceof GzippedJavascriptModel) {
                        $asset->setSrc($url);
                    } else {
                        $asset->setHref($url);
                    }
                } elseif ($asset instanceof FolderModel) {
                    $this->saveAssetsToS3($asset->getAssets());
                }
            }
        }
    }

    protected function saveAssetToS3($data, $mime, $dir, $filename)
    {
        $dir = $this->getS3Folder() . '/' . $dir;

        $url = S3::putFile($this->getS3Bucket(), $dir, $filename, $data, S3::$PUBLIC_ACL, $mime);

        if ($url) {
            return sprintf('%s%s/%s', $this->getCloudfrontEndpoint(), $dir, $filename);
        }
        return false;
    }
    
    public function getS3Folder()
    {
        return $this->s3Folder;
    }

    public function setS3Folder(ThemeInterface $theme)
    {
        if ($theme instanceof PrivateThemeModel) {
            $storeSlug = $theme->getStores()[0];
            $themeId = $theme->getId();
            $this->s3Folder = sprintf(self::S3_TEMPLATE_ROOT, $storeSlug, $themeId);
        }
    }

    protected function createAdditionalAssets($assets)
    {
        $additionalAssets = $this->createAdditionalAssetsWithinHtml($assets);
        //add additional js and css assets to the asset pool so we can check them for assets too
    }

    protected function createAdditionalAssetsWithinHtml($assets)
    {
        $additionalAssets = [];
        if (!empty($assets) && is_array($assets)) {
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel) {
                    $this->createAdditionalAssetsWithinHtml($asset->getAssets());
                } elseif ($asset instanceof TemplateModel) {
                    $htmlParser = new HtmlParser();
                    $htmlParser->setContent($asset->getContent());
                    $htmlParser->parse();

                    $cssAssets = $this->getAdditionalAssets($htmlParser->getParsedCssAssets(), 'assets/css', '/text\/(plain|html|css)/', 'css');
                    $jsAssets = $this->getAdditionalAssets($htmlParser->getParsedJavascriptAssets(), 'assets/javascript', '/text\/(plain|html|js)/', 'javascript');
                    $imageAssets = $this->getAdditionalAssets($htmlParser->getParsedImageAssets(), 'assets/images', '/image\/(.*)/', 'image');

                    $parsedAssets = array_merge($cssAssets, $jsAssets, $imageAssets);

                    if (!empty($parsedAssets)) {
                        $asset->setContent($htmlParser->compileContent($parsedAssets));
                    }
                }
            }
        }
        return $additionalAssets;
    }

    protected function getAdditionalAssets($additionalAssets, $assetPath, $mimeRegex, $type)
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
                        $newAssetUrl = $this->saveAssetToS3(file_get_contents($file->get()), $file->getMime(), $assetPath . $file->getPath(), $file->getName());
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

    protected function addImagesToTheme($imageAssets)
    {
        $replacedAssets = [];
        if (!empty($imageAssets) && is_array($imageAssets)) {
            foreach ($imageAssets as $key => $imageAssetUrl) {
                $imageParser = new ImageParser();
                $imageParser->setUrl($imageAssetUrl);
                $assetName = $imageParser->getName();
                $relativePath = $imageParser->getPath();

                $existingAsset = $this->getAsset($this->getRootAssetImageFolder(), $relativePath, $assetName);
                if ($existingAsset !== false && $existingAsset instanceof ImageModel) {
                    //already exists so just get the url
                    $replacedAssets[$key] = $existingAsset->getSrc();
                } else {
                    //doesn't exist so create it
                    //create new folder recursively if doesn't exist
                    $folderPath = $this->createFolderRecursively($relativePath);

                    //create image
//                    $image = $this->createImage($file, $parent);
//                    $parent->addAsset($image);
                }
            }
        }
    }

    protected function createFolderRecursively($name)
    {
        $imageRoot = $this->getRootAssetImageFolder();
        if ($imageRoot instanceof FolderModel) {
            $newFolderName = $imageRoot->getPath();
            $this->createDirectory($this->getTempThemeDirectory() . '/' . $newFolderName, $name);

            $folder = new \DirectoryIterator($path);
        }
    }

    protected function getAssetUrl(AssetInterface $asset)
    {
        if ($asset instanceof ImageModel) {
            return $asset->getSrc();
        } elseif ($asset instanceof JavascriptModel || $asset instanceof GzippedJavascriptModel) {
            return $asset->getSrc();
        } elseif ($asset instanceof CssModel || $asset instanceof GzippedCssModel) {
            return $asset->getHref();
        }
    }

    protected function getAsset($assets, $relativePath, $assetName)
    {
        $folderName = explode("/", $relativePath)[0];
        if (!empty($folderName)) {
            $folder = $this->findFolder($assets, $folderName);
            if ($folder !== false) {
                $relativePath = str_replace($folderName . '/', '', $relativePath);
                return $this->findAsset($folder->getAssets(), $relativePath, $assetName);
            }
        } else {
            //last folder
            $folder = $this->findFolder($assets, $folderName);
            if ($folder !== false) {
                return $this->findAsset($folder->getAssets(), $assetName);
            }
        }
        return false;
    }

    public function getAssets()
    {
        return $this->assets;
    }

    public function setAssets($assets)
    {
        $this->assets = $assets;
    }

    protected function findFolder($assets, $folderName)
    {
        if (!empty($assets) && is_array($assets)) {
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel && $asset->getName() == $folderName) {
                    return $asset;
                }
            }
        }
        return false;
    }

    protected function findAsset($assets, $assetName)
    {
        if (!empty($assets) && is_array($assets)) {
            foreach ($assets as $asset) {
                if ($asset->getName() == $assetName) {
                    return $asset;
                }
            }
        }
        return false;
    }

    protected function findAssetRecursively($assets, $assetName)
    {
        if (!empty($assets) && is_array($assets)) {
            foreach ($assets as $asset) {
                if ($asset->getName() == $assetName) {
                    return $asset;
                }
            }
        }
        return false;
    }

    protected function setRootAssetImageFolder($assets)
    {
        if (!empty($assets) && is_array($assets)) {
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel && $asset->getName() == self::DEFAULT_DIR_ASSETS) {
                    $assets = $asset->getAssets();
                    foreach ($assets as $asset) {
                        if ($asset instanceof FolderModel && $asset->getName() == self::DEFAULT_DIR_ASSETS_IMAGES) {
                            $this->assetImageRoot = $asset;
                        }
                    }
                    $this->assetImageRoot = false;
                }
            }
        }
    }

    /* @return FolderModel */
    protected function getRootAssetImageFolder()
    {
        return $this->assetImageRoot;
    }

    protected function setAttributesFromConfig(AssetInterface $assetModel)
    {
        $themeConfig = $this->getConfig()['zoop']['legacy']['theme']['structure'];

        $path = $assetModel->getPath();
        if (!empty($path)) {
            $assetPath = explode('/', $path);
        }

        $assetPath[] = $assetModel->getName();

        $assetConfig = $this->getAssetConfig($assetPath, $themeConfig);

        if (!empty($assetConfig)) {
            $assetModel->setDeletable((bool) $assetConfig['delatable']);
            $assetModel->setWritable((bool) $assetConfig['writable']);
            $assetModel->setEditable((bool) $assetConfig['editable']);
        }
    }

    protected function getAssetConfig($asset, $config)
    {
        if (is_array($asset)) {
            for ($i = 0; $i < count($asset); $i++) {
                if (!empty($config)) {
                    if (array_key_exists(strtolower($asset[$i]), $config)) {
                        if (isset($config[$asset[$i]]['children']) && count($config[$asset[$i]]['children']) > 0 && (count($asset) - 1) > $i) {
                            $config = $config[$asset[$i]]['children'];
                        } else {
                            $config = $config[$asset[$i]];
                        }
                    } else {
                        $config = false;
                    }
                }

                if ((count($asset) - 1) == $i) {
                    return $config;
                }
            }
        } else {
            if (array_key_exists(strtolower($asset), $config)) {
                return $config[$asset];
            }
        }
        return false;
    }

    protected function createAssetsFromDirectory($rootDir, FolderModel $parent = null)
    {
        $documents = [];
        $assets = new DirectoryIterator($rootDir);

        /* @var $asset DirectoryIterator */
        foreach ($assets as $path => $asset) {
            $assetModel = $this->createAssetModel($asset, $parent);

            if (!empty($assetModel)) {
                $documents[] = $assetModel;
            }
        }
        return $documents;
    }

    protected function getFolder($name, FolderModel $parent = null)
    {

    }

    protected function getFileContent(DirectoryIterator $file)
    {
        return file_get_contents($file->getPathname());
    }
}
