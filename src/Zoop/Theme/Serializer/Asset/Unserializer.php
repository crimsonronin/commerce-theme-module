<?php

namespace Zoop\Theme\Serializer\Asset;

use \Exception;
use \SplFileInfo;
use Zend\Validator\File;
use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Css as CssModel;
use Zoop\Theme\DataModel\GzippedCss as GzippedCssModel;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\Image as ImageModel;
use Zoop\Theme\DataModel\Javascript as JavascriptModel;
use Zoop\Theme\DataModel\GzippedJavascript as GzippedJavascriptModel;
use Zoop\Theme\DataModel\Less as LessModel;
use Zoop\Theme\DataModel\Template as TemplateModel;

/**
 *
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class Unserializer
{

    private $tempDirectory;

    /**
     * @return AssetUnserializer
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
     * @return AssetUnserializer
     */
    private function unserializeModel(SplFileInfo $file)
    {
        $pathname = $file->getPathname();

        $htmlValidator = new File\MimeType(['text/html', 'text/plain', 'text/x-asm']);
        $htmlExtensionValidator = new File\Extension(['html']);
        $imageValidator = new File\IsImage();
        $cssValidator = new File\MimeType(['text/css', 'text/plain', 'text/x-asm', 'application/x-gzip']);
        $cssExtensionValidator = new File\Extension(['css', 'min.css']);
        $jsValidator = new File\MimeType(['application/javascript', 'text/plain', 'text/x-asm', 'application/x-gzip']);
        $jsExtensionValidator = new File\Extension(['js', 'min.js']);
        $lessValidator = new File\MimeType(['application/less', 'text/plain', 'text/x-asm']);
        $lessExtensionValidator = new File\Extension(['less']);

        if ($htmlValidator->isValid($pathname) && $htmlExtensionValidator->isValid($pathname)) {
            //Twig/HTML Template
            return $this->unserializeTemplate($file);
        } elseif ($imageValidator->isValid($pathname)) {
            //Image
            return $this->unserializeImage($file);
        } elseif ($cssValidator->isValid($pathname) && $cssExtensionValidator->isValid($pathname)) {
            //Css
            return $this->unserializeCss($file);
        } elseif ($jsValidator->isValid($pathname) && $jsExtensionValidator->isValid($pathname)) {
            //Javascript
            return $this->unserializeJavascript($file);
        } elseif ($lessValidator->isValid($pathname) && $lessExtensionValidator->isValid($pathname)) {
            //Less
            return $this->unserializeLess($file);
        } else {
            throw new Exception('The file "' . str_replace($this->getTempDirectory() . DIRECTORY_SEPARATOR, '', $file->getPathname()) . '" is not a supported file type');
        }

        return false;
    }

    /**
     * @return string
     */
    private function getFileContent(SplFileInfo $file)
    {
        return file_get_contents($file->getPathname());
    }

    /**
     * @return FolderModel
     */
    private function unserializeFolder(SplFileInfo $folder)
    {
        $folderModel = new FolderModel;
        $folderModel->setName($folder->getFilename());

        $this->setPaths($folderModel, $folder->getPathname());

        return $folderModel;
    }

    /**
     * @return CssModel|GzippedCssModel
     */
    private function unserializeCss(SplFileInfo $file)
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
    private function unserializeImage(SplFileInfo $file)
    {
        $mime = mime_content_type($file->getPathname());
        list($width, $height, $type, $attr) = getimagesize($file->getPathname());

        $image = new ImageModel;

        $image->setName($file->getFilename());
        $image->setMime($mime);
        $image->setExtension(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
        $image->setHeight($height);
        $image->setWidth($width);

        //save to S3
        $content = $this->getFileContent($file);

        $this->setPaths($image, $file->getPathname());

        return $image;
    }

    /**
     * @return JavascriptModel|GzippedJavascriptModel
     */
    private function unserializeJavascript(SplFileInfo $file)
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
    private function unserializeLess(SplFileInfo $file)
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
    private function unserializeTemplate(SplFileInfo $file)
    {
        //get the parsed assets and add to the queue
        $template = new TemplateModel;
        $template->setName($file->getFilename());
        $template->setContent($this->getFileContent($file));

        $this->setPaths($template, $file->getPathname());

        return $template;
    }

    private function setPaths(AssetInterface $asset, $absolutePathname)
    {
        $relativePathname = str_replace($this->getTempDirectory() . '/', '', $absolutePathname);
        $relativePath = rtrim(str_replace($asset->getName(), '', $relativePathname), '/');

        $asset->setPath($relativePath);
        $asset->setPathName($relativePathname);
    }

    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    public function setTempDirectory($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }

}
