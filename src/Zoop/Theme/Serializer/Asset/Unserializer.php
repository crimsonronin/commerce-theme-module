<?php

namespace Zoop\Theme\Serializer\Asset;

use Exception;
use SplFileInfo;
use Zend\Validator\File;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Css as CssModel;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\GzippedCss as GzippedCssModel;
use Zoop\Theme\DataModel\GzippedJavascript as GzippedJavascriptModel;
use Zoop\Theme\DataModel\Image as ImageModel;
use Zoop\Theme\DataModel\Javascript as JavascriptModel;
use Zoop\Theme\DataModel\Less as LessModel;
use Zoop\Theme\DataModel\Template as TemplateModel;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 *
 * TODO: Create asset unserializers to reduce the complexity of this class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class Unserializer implements UnserializerInterface
{
    protected $tempDirectory;

    /**
     * {@inheritdoc}
     */
    public function fromFile(SplFileInfo $file)
    {
        $asset = false;

        if ($file->isFile()) {
            $asset = $this->unserializeModel($file);
        } elseif ($file->isDir()) {
            $asset = $this->unserializeFolder($file);
        } else {
            throw new Exception('The file: ' . $file->getFilename() . ' is not recognized');
        }

        return $asset;
    }

    /**
     * @return AssetInterface
     * @throws Exception
     */
    protected function unserializeModel(SplFileInfo $file)
    {
        $pathname = $file->getPathname();

        if ($this->isHtml($pathname)) {
            //Twig/HTML Template
            return $this->unserializeTemplate($file);
        } elseif ($this->isImage($pathname)) {
            //Image
            return $this->unserializeImage($file);
        } elseif ($this->isCss($pathname)) {
            //Css
            return $this->unserializeCss($file);
        } elseif ($this->isJavascript($pathname)) {
            //Javascript
            return $this->unserializeJavascript($file);
        } elseif ($this->isLess($pathname)) {
            //Less
            return $this->unserializeLess($file);
        } else {
            throw new Exception(
                sprintf(
                    'The file "%s" is not a supported file type',
                    str_replace($this->getTempDirectory() . DIRECTORY_SEPARATOR, '', $file->getPathname())
                )
            );
        }

        return false;
    }

    protected function isHtml($pathname)
    {
        $mime = new File\MimeType(['text/html', 'text/plain', 'text/x-asm']);
        $extension = new File\Extension(['html']);

        return $mime->isValid($pathname) && $extension->isValid($pathname);
    }

    protected function isImage($pathname)
    {
        $file = new File\IsImage();

        return $file->isValid($pathname);
    }

    protected function isCss($pathname)
    {
        $mime = new File\MimeType(['text/css', 'text/plain', 'text/x-asm', 'application/x-gzip']);
        $extension = new File\Extension(['css', 'min.css']);

        return $mime->isValid($pathname) && $extension->isValid($pathname);
    }

    protected function isJavascript($pathname)
    {
        $mime = new File\MimeType([
            'application/javascript',
            'text/javascript',
            'text/plain',
            'text/x-asm',
            'application/x-gzip'
        ]);
        $extension = new File\Extension(['js', 'min.js']);

        return $mime->isValid($pathname) && $extension->isValid($pathname);
    }

    protected function isLess($pathname)
    {
        $mime = new File\MimeType(['application/less', 'text/plain', 'text/x-asm']);
        $extension = new File\Extension(['less']);

        return $mime->isValid($pathname) && $extension->isValid($pathname);
    }

    /**
     * @return string
     */
    protected function getFileContent(SplFileInfo $file)
    {
        return file_get_contents($file->getPathname());
    }

    /**
     * @return FolderModel
     */
    protected function unserializeFolder(SplFileInfo $folder)
    {
        $folderModel = new FolderModel;
        $folderModel->setName($folder->getFilename());

        $this->setPaths($folderModel, $folder->getPathname());

        return $folderModel;
    }

    /**
     * @return CssModel|GzippedCssModel
     */
    protected function unserializeCss(SplFileInfo $file)
    {
        $gzipValidator = new File\MimeType(['application/x-gzip']);

        if ($gzipValidator->isValid($file->getPathname())) {
            $css = new GzippedCssModel;
        } else {
            $css = new CssModel;
            $css->setContent($this->getFileContent($file));
        }

        $css->setName($file->getFilename());

        $this->setPaths($css, $file->getPathname());

        return $css;
    }

    /**
     * @return ImageModel
     */
    protected function unserializeImage(SplFileInfo $file)
    {
        $mime = mime_content_type($file->getPathname());
        list($width, $height) = getimagesize($file->getPathname());

        $image = new ImageModel;

        $image->setName($file->getFilename());
        $image->setMime($mime);
        $image->setExtension(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
        $image->setHeight($height);
        $image->setWidth($width);

        //TODO: save to S3
//        $content = $this->getFileContent($file);

        $this->setPaths($image, $file->getPathname());

        return $image;
    }

    /**
     * @return JavascriptModel|GzippedJavascriptModel
     */
    protected function unserializeJavascript(SplFileInfo $file)
    {
        $gzipValidator = new File\MimeType(['application/x-gzip']);

        if ($gzipValidator->isValid($file->getPathname())) {
            $js = new GzippedJavascriptModel;
        } else {
            $js = new JavascriptModel;
            $js->setContent($this->getFileContent($file));
        }

        $js->setName($file->getFilename());

        $this->setPaths($js, $file->getPathname());

        return $js;
    }

    /**
     * @return LessModel
     */
    protected function unserializeLess(SplFileInfo $file)
    {
        //get the parsed assets and add to the queue
        $less = new LessModel;
        $less->setName($file->getFilename());
        $less->setContent($this->getFileContent($file));

        $this->setPaths($less, $file->getPathname());

        return $less;
    }

    /**
     * @return TemplateModel
     */
    protected function unserializeTemplate(SplFileInfo $file)
    {
        //get the parsed assets and add to the queue
        $template = new TemplateModel;
        $template->setName($file->getFilename());
        $template->setContent($this->getFileContent($file));

        $this->setPaths($template, $file->getPathname());

        return $template;
    }

    /**
     * @param AssetInterface $asset
     * @param string $absolutePathname
     */
    protected function setPaths(AssetInterface $asset, $absolutePathname)
    {
        $relativePathname = str_replace($this->getTempDirectory() . '/', '', $absolutePathname);
        $relativePath = rtrim(str_replace($asset->getName(), '', $relativePathname), '/');

        $asset->setPath($relativePath);
        $asset->setPathName($relativePathname);
    }

    /**
     * {@inheritdoc}
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function setTempDirectory($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }
}
