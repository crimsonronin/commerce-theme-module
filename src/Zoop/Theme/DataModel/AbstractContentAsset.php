<?php

namespace Zoop\Theme\DataModel;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

abstract class AbstractContentAsset extends AbstractFileAsset
{
    /**
     *
     * @ODM\String
     */
    protected $content;

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = mb_convert_encoding($content, 'UTF-8');
    }
}
