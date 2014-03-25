<?php

namespace Zoop\Theme\Parser\Node;

use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Css;

class CssNode extends AbstractFileNode implements NodeInterface, FileNodeInterface
{
    protected $model;

    /**
     * @return Css
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
     * @param Css $model
     */
    public function setModel(AssetInterface $model)
    {
        $this->model = $model;
    }

    /**
     * @return Css
     */
    public function createDataModel()
    {
        $css = new Css;
        $this->parseFileModel($css);
        $tempDir = $this->getToken()->getTempDirectory();

        //get the css contents
        $filename = $tempDir . '/' . $css->getPathname();
        $css->setContent(file_get_contents($filename));

        return $css;
    }

    public function __toString()
    {
        return $this->getModel()->getSrc();
    }
}
