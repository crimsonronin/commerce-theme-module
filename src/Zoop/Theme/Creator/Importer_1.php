<?php

namespace Zoop\Theme\Service;

use \Exception;
use \DateTime;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \DirectoryIterator;
use \ZipArchive;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\File\S3;
use Zoop\Store\Store;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\PrivateTheme as PrivateThemeModel;
use Zoop\Theme\DataModel\SharedTheme as SharedThemeModel;
use Zoop\Theme\DataModel\ZoopTheme as ZoopThemeModel;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Css as CssModel;
use Zoop\Theme\DataModel\GzippedCss as GzippedCssModel;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\Image as ImageModel;
use Zoop\Theme\DataModel\Javascript as JavascriptModel;
use Zoop\Theme\DataModel\GzippedJavascript as GzippedJavascriptModel;
use Zoop\Theme\DataModel\Less as LessModel;
use Zoop\Theme\DataModel\Template as TemplateModel;
use Zoop\Theme\Parser\File as FileParser;
use Zoop\Theme\Parser\Css as CssParser;
use Zoop\Theme\Parser\Image as ImageParser;
use Zoop\Theme\Parser\Html as HtmlParser;
use Zoop\Theme\Service\Validator;
use Zend\Config\Config;

class Importer_1
{

    const MAX_ZIP_SIZE_KB = 20480;
    const DEFAULT_DIR_ASSETS = 'assets';
    const DEFAULT_DIR_ASSETS_JS = 'js';
    const DEFAULT_DIR_ASSETS_IMAGES = 'images';
    const DEFAULT_DIR_ASSETS_CSS = 'css';
    const DEFAULT_DIR_LAYOUTS = 'layouts';
    const DEFAULT_DIR_MACROS = 'macros';
    const S3_TEMPLATE_ROOT = 'storefront/%s/templates/%s';

    private $additionalErrorMessages = [];
    private $config;
    private $cloudfrontEndpoint;
    private $s3Bucket;
    private $s3Folder;
    private $tempDirectory;
    private $tempThemeDirectory;
    /* @var $validator Validator */
    private $validator;
    private $dm;
    private $assetImageRoot;
    private $assets = [];

    public function __construct(DocumentManager $dm, Validator $validator, $s3Bucket, $cloudfrontEndpoint, $tempDirectory)
    {
        $this->setTempDirectory($tempDirectory);
        $this->setValidator($validator);
        $this->setDm($dm);
        $this->setCloudfrontEndpoint($cloudfrontEndpoint);
        $this->setS3Bucket($s3Bucket);

        $this->setConfig(new Config(require __DIR__ . '/../config/theme.config.php'));
    }

    public function __destruct()
    {
        $this->deleteTempDirectory($this->getTempThemeDirectory());
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(Config $config)
    {
        $this->config = $config->toArray();
    }

    /* @return DocumentManager */

    public function getDm()
    {
        return $this->dm;
    }

    public function setDm(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /* @return Validator */

    public function getValidator()
    {
        return $this->validator;
    }

    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function getCloudfrontEndpoint()
    {
        return $this->cloudfrontEndpoint;
    }

    public function getS3Bucket()
    {
        return $this->s3Bucket;
    }

    public function setCloudfrontEndpoint($cloudfrontEndpoint)
    {
        $this->cloudfrontEndpoint = $cloudfrontEndpoint;
    }

    public function setS3Bucket($s3Bucket)
    {
        $this->s3Bucket = $s3Bucket;
    }

    public function importPrivateTheme(Store $store, $uploadedFile)
    {
        $theme = new PrivateThemeModel;
        $theme->setCreatedOn(new DateTime);
        $theme->setLegacyStoreId($store->getId());
        $theme->setStores([$store->getSubDomain()]);

        $this->importTheme($theme, $uploadedFile);
        return $theme;
    }

    public function importSharedTheme(Store $store, $uploadedFile)
    {
        $theme = new SharedThemeModel;
        $theme->setCreatedOn(new DateTime);

        $this->importTheme($theme, $uploadedFile);
        return $theme;
    }

    public function importZoopTheme($uploadedFile)
    {
        $theme = new ZoopThemeModel;
        $theme->setCreatedOn(new DateTime);

        $this->importTheme($theme, $uploadedFile);
        return $theme;
    }

    public function importTheme(ThemeInterface $theme, $uploadedFile)
    {
        if (is_file($uploadedFile['tmp_name'])) {
            if (mime_content_type($uploadedFile['tmp_name']) == 'application/zip') {
                if ((self::MAX_ZIP_SIZE_KB * 1024) > filesize($uploadedFile['tmp_name'])) {
                    $tempDir = $this->getTempThemeDirectory();
                    if ($this->unZipTheme($uploadedFile['tmp_name'], $tempDir) === true) {
                        //set theme name
                        $theme->setName(str_replace('.zip', '', $uploadedFile['name']));

                        $this->setAssets($this->createAssetsFromDirectory($tempDir));

                        //validate Theme
                        if ($this->getValidator()->validate($theme) === true) {
                            //save theme so we can set s3 bucket dir
                            $this->saveTheme($theme);

                            //save all css, js and images to s3
                            $this->saveAssetsToS3($this->getAssets());

                            //set the image asset root so we can add new images to it easily
                            $this->setRootAssetImageFolder($this->getAssets());

                            //Parse out css, js and images and store on S3
                            $this->createAdditionalAssets($this->getAssets());

                            try {
                                //save to database
                                $this->save($theme, $this->getAssets());
                            } catch (Exception $e) {
                                throw new Exception('Could not save the theme: "' . $e->getMessage());
                            }
                        } else {
                            $errors = $this->getValidator()->getErrors();
                            $this->setAdditionalErrorMessages($errors);

                            throw new Exception('The theme contains errors');
                        }
                    } else {
                        throw new Exception('Could not unzip the file "' . $uploadedFile['name'] . '" into "' . $tempDir . '"');
                    }
                } else {
                    throw new Exception('Exceeds the maximum file size of ' . self::MAX_ZIP_SIZE_KB / 1024 . 'MB');
                }
            } else {
                throw new Exception('The file "' . $uploadedFile['name'] . '" is not a zip archive');
            }
        } else {
            throw new Exception('The file "' . $uploadedFile['name'] . '" does not exist');
        }
    }

    private function saveAssetsToS3($assets)
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

    private function saveAssetToS3($data, $mime, $dir, $filename)
    {
        $dir = $this->getS3Folder() . '/' . $dir;

        $url = S3::putFile($this->getS3Bucket(), $dir, $filename, $data, S3::$PUBLIC_ACL, $mime);

        if ($url) {
            return sprintf('%s%s/%s', $this->getCloudfrontEndpoint(), $dir, $filename);
        }
        return false;
    }

    private function saveTheme(ThemeInterface $theme)
    {
        $this->getDm()->persist($theme);
        $this->getDm()->flush();

        //set the s3 dir
        $this->setS3Folder($theme);
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

    private function save(ThemeInterface $theme, $assets)
    {
        $this->saveTheme($theme);

        $this->saveRecursively($theme, $assets);
    }

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

    private function createAdditionalAssets($assets)
    {
        $additionalAssets = $this->createAdditionalAssetsWithinHtml($assets);
        //add additional js and css assets to the asset pool so we can check them for assets too
    }

    private function createAdditionalAssetsWithinHtml($assets)
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

    private function getAdditionalAssets($additionalAssets, $assetPath, $mimeRegex, $type)
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

    private function addImagesToTheme($imageAssets)
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

    private function createFolderRecursively($name)
    {
        $imageRoot = $this->getRootAssetImageFolder();
        if ($imageRoot instanceof FolderModel) {
            $newFolderName = $imageRoot->getPath();
            $this->createDirectory($this->getTempThemeDirectory() . '/' . $newFolderName, $name);

            $folder = new \DirectoryIterator($path);
        }
    }

    private function getAssetUrl(AssetInterface $asset)
    {
        if ($asset instanceof ImageModel) {
            return $asset->getSrc();
        } elseif ($asset instanceof JavascriptModel || $asset instanceof GzippedJavascriptModel) {
            return $asset->getSrc();
        } elseif ($asset instanceof CssModel || $asset instanceof GzippedCssModel) {
            return $asset->getHref();
        }
    }

    private function getAsset($assets, $relativePath, $assetName)
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

    private function findFolder($assets, $folderName)
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

    private function findAsset($assets, $assetName)
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

    private function findAssetRecursively($assets, $assetName)
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

    private function setRootAssetImageFolder($assets)
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

    private function getRootAssetImageFolder()
    {
        return $this->assetImageRoot;
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

    private function setAttributesFromConfig(AssetInterface $assetModel)
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

    private function getAssetConfig($asset, $config)
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

    private function createAssetsFromDirectory($rootDir, FolderModel $parent = null)
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

    private function getFolder($name, FolderModel $parent = null)
    {

    }

    private function createAssetModel($asset, FolderModel $parent = null)
    {
        if ($asset->isDir() && $asset->getFilename() != '.' && $asset->getFilename() != '..') {
            //get existing folder
            if (!is_null($parent)) {
                $currentAssets = $parent->getAssets();
            } else {
                $currentAssets = $this->getAssets();
            }
            $existingFolder = $this->findFolder($currentAssets, $asset->getFilename());

            if ($existingFolder === false) {
                //Folder
                $newAsset = $this->createFolder($asset, $parent);

                //set default attributes from config
                $this->setAttributesFromConfig($newAsset);
            } else {
                $newAsset = $existingFolder;
            }

            $newAsset->setAssets($this->createAssetsFromDirectory($asset->getPathname(), $newAsset));
        } elseif ($asset->isFile() && !$asset->isDir()) {
            $extension = $asset->getExtension();
            $mime = mime_content_type($asset->getPathname());
            $newAsset = false;

            if (($mime == 'text/html' || $mime == 'text/plain' || $mime == 'text/x-asm') && $extension == 'html') {
                //Twig/HTML Template
                $newAsset = $this->createTemplate($asset, $parent);
            } elseif (preg_match("/image\/([a-z0-9]+)/", $mime)) {
                //Image
                $newAsset = $this->createImage($asset, $parent);
            } elseif (($mime == 'text/css' || $mime == 'text/x-asm' || $mime == 'application/x-gzip') && $extension == 'css') {
                //Css
                $newAsset = $this->createCss($asset, $parent);
            } elseif (($mime == 'application/javascript' || $mime == 'text/plain' || $mime == 'text/x-asm' || $mime == 'application/x-gzip') && $extension == 'js') {
                //Javascript
                $newAsset = $this->createJavascript($asset, $parent);
            } elseif (($mime == 'application/less' || $mime == 'text/plain' || $mime == 'text/x-asm') && $extension == 'less') {
                //Javascript
                $newAsset = $this->createLess($asset, $parent);
            }

            //set default attributes from config
            if ($newAsset) {
                $this->setAttributesFromConfig($newAsset);
            }
        }
        return $newAsset;
    }

    private function getFileContent(DirectoryIterator $file)
    {
        return file_get_contents($file->getPathname());
    }

    private function createFolder(DirectoryIterator $folder, FolderModel $parent = null)
    {
        $folderModel = new FolderModel;
        $folderModel->setName($folder->getFilename());
        $folderModel->setCreatedOn(new DateTime);

        if (!empty($parent)) {
            $folderModel->setParent($parent);
        }
        $this->setPaths($folderModel, $parent);
        return $folderModel;
    }

    private function createCss(DirectoryIterator $file, FolderModel $parent = null)
    {
        $mime = mime_content_type($file->getPathname());

        if ($mime == 'application/x-gzip') {
            $css = new GzippedCssModel;
        } else {
            $css = new CssModel;
            $css->setContent($this->getFileContent($file));
        }

        $css->setName($file->getFilename());
        $css->setCreatedOn(new DateTime);

        if (!empty($parent)) {
            $css->setParent($parent);
        }
        $this->setPaths($css, $parent);

        return $css;
    }

    private function createImage(DirectoryIterator $file, FolderModel $parent = null)
    {
        $mime = mime_content_type($file->getPathname());
        list($width, $height, $type, $attr) = getimagesize($file->getPathname());

        $image = new ImageModel;

        $image->setName($file->getFilename());
        $image->setCreatedOn(new DateTime);
        $image->setMime($mime);

        $image->setHeight($height);
        $image->setWidth($width);

        //save to S3
        $content = $this->getFileContent($file);

        if (!empty($parent)) {
            $image->setParent($parent);
        }
        $this->setPaths($image, $parent);

        return $image;
    }

    private function createJavascript(DirectoryIterator $file, FolderModel $parent = null)
    {
        $mime = mime_content_type($file->getPathname());

        if ($mime == 'application/x-gzip') {
            $js = new GzippedJavascriptModel;
        } else {
            $js = new JavascriptModel;
            $js->setContent($this->getFileContent($file));
        }

        $js->setName($file->getFilename());
        $js->setCreatedOn(new DateTime);

        if (!empty($parent)) {
            $js->setParent($parent);
        }
        $this->setPaths($js, $parent);

        return $js;
    }

    private function createLess(DirectoryIterator $file, FolderModel $parent = null)
    {
        //get the parsed assets and add to the queue
        $less = new LessModel;
        $less->setName($file->getFilename());
        $less->setCreatedOn(new DateTime);
        $less->setContent($this->getFileContent($file));

        if (!empty($parent)) {
            $less->setParent($parent);
        }
        $this->setPaths($less, $parent);

        return $less;
    }

    private function createTemplate(DirectoryIterator $file, FolderModel $parent = null)
    {
        //get the parsed assets and add to the queue
        $template = new TemplateModel;
        $template->setName($file->getFilename());
        $template->setCreatedOn(new DateTime);
        $template->setContent($this->getFileContent($file));

        if (!empty($parent)) {
            $template->setParent($parent);
        }
        $this->setPaths($template, $parent);

        return $template;
    }

    private function setPaths(AssetInterface $asset, $parent)
    {
        if (!empty($parent)) {
            $base = $parent->getPath();
            $base = !empty($base) ? $base . '/' : $base;
            $path = $base . $parent->getName();
            $asset->setPath($path);
            $asset->setPathName($path . '/' . $asset->getName());
        } else {
            $asset->setPathName($asset->getName());
        }
    }

    private function createDirectory($base, $name)
    {
        $dir = $base . '/' . $name;
        if (!is_dir($dir)) {
            mkdir($dir, 755, true);
        }
        return $dir;
    }

    public function getTempThemeDirectory()
    {
        return $this->tempThemeDirectory;
    }

    public function setTempThemeDirectory($tempDirectory)
    {
        $this->tempThemeDirectory = $tempDirectory;
        return $this;
    }

    public function getTempDirectory()
    {
        return $this->tempDirectory;
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

    public function clearParsedAssets()
    {
        $this->parsedAssets = [];
        return $this;
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
            $this->additionalErrorMessages[] = $error['message'];
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

}
