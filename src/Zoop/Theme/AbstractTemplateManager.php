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

    /**
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->setTwig($twig);
    }

    /**
     * @return Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * @param Twig_Environment $twig
     */
    public function setTwig(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * 
     * @return type
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * 
     * @param type $file
     * @return AbstractTemplateManager
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param true $merge
     * @return AbstractTemplateManager
     */
    public function setVariable($key, $value, $merge = true)
    {
        if (isset($this->variables[$key]) &&
            !empty($this->variables[$key]) &&
            is_array($this->variables[$key]) &&
            $merge === true &&
            is_array($value)
        ) {
            $this->variables[$key] = array_merge_recursive($this->variables[$key], $value);
        } else {
            $this->variables[$key] = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * Reset variables
     */
    public function clearVariables()
    {
        $this->variables = [];
    }

    /**
     * @return string
     */
    public function getCacheDirectory()
    {
        if (!empty($this->cacheDirectory)) {
            return $this->cacheDirectory;
        } else {
            return self::CACHE_DIR;
        }
    }

    /**
     * @param string $cacheDirectory
     */
    public function setCacheDirectory($cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }
}
