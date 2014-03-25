<?php

namespace Zoop\Theme\Parser\Node;

use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Javascript;

class JavascriptNode extends AbstractFileNode implements NodeInterface, FileNodeInterface
{
    protected $model;

    /**
     * @return Javascript
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
     * @param Javascript $model
     */
    public function setModel(AssetInterface $model)
    {
        $this->model = $model;
    }

    /**
     * @return Javascript
     */
    public function createDataModel()
    {
        $model = new Javascript;
        $this->parseFileModel($model);
        $tempDir = $this->getToken()->getTempDirectory();

        //get the css contents
        $filename = $tempDir . '/' . $model->getPathname();
        $model->setContent(file_get_contents($filename));

        return $model;
    }

    public function __toString()
    {
        return $this->getModel()->getSrc();
    }
}
