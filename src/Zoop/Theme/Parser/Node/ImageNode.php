<?php

namespace Zoop\Theme\Parser\Node;

use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Image;

class ImageNode extends AbstractFileNode implements NodeInterface, FileNodeInterface
{

    protected $model;

    /**
     * @return Image
     */
    public function getModel()
    {
        if (!isset($this->model)) {
            $image = $this->createDataModel();
            $this->setModel($image);
        }
        return $this->model;
    }

    /**
     * @param Image $model
     */
    public function setModel(AssetInterface $model)
    {
        $this->model = $model;
    }

    /**
     * @return Image
     */
    public function createDataModel()
    {
        $image = new Image;

        $tempDir = $this->getToken()->getTempDirectory();

        //if absolute path get the remote file
        //apply the mime, ext etc
        $this->parseFileModel($image);

        $localPathname = $tempDir . '/' . $image->getPathname();
        
        //set extension
        $image->setExtension($this->getExtension($localPathname));

        //apply image dimensions
        $dimensions = $this->getDimensions($localPathname);
        $image->setHeight($dimensions['height']);
        $image->setWidth($dimensions['width']);

        return $image;
    }

    public function getDimensions($pathname)
    {
        list($width, $height) = getimagesize($pathname);

        return [
            'width' => $width,
            'height' => $height
        ];
    }

    public function __toString()
    {
        return $this->getModel()->getSrc();
    }

}
