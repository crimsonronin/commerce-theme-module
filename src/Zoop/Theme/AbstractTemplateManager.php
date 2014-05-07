<?php

namespace Zoop\Theme;

use \Twig_Environment;

abstract class AbstractTemplateManager
{

    const CACHE_DIR = '/../../cache';

    private $cacheDirectory;
    private $file;
    private $twig;
    private $variables = [];

    public function __construct(Twig_Environment $twig)
    {
        $this->setTwig($twig);
    }

    public function getTwig()
    {
        return $this->twig;
    }

    public function setTwig(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function setVariable($key, $value, $merge = true)
    {
        if (isset($this->variables[$key]) && !empty($this->variables[$key]) && is_array($this->variables[$key]) && $merge === true && is_array($value)) {
            $this->variables[$key] = array_merge_recursive($this->variables[$key], $value);
        } else {
            $this->variables[$key] = $value;
        }
        return $this;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function setVariables($variables)
    {
        $this->variables = $variables;
    }

    public function clearVariables()
    {
        $this->variables = [];
    }

    public function getCacheDirectory()
    {
        if (!empty($this->cacheDirectory)) {
            return $this->cacheDirectory;
        } else {
            return self::CACHE_DIR;
        }
    }

    public function setCacheDirectory($cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

}
