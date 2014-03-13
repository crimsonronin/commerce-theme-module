<?php

namespace Zoop\Theme;

use \Twig_Environment;
use \Twig_Error_Syntax;
use Zoop\Theme\DataModel\AbstractTheme as AbstractThemeThemeModel;
use Zoop\Theme\DataModel\Css as CssModel;
use Zoop\Theme\DataModel\Folder as FolderModel;
use Zoop\Theme\DataModel\Image as ImageModel;
use Zoop\Theme\DataModel\Javascript as JavascriptModel;
use Zoop\Theme\DataModel\Less as LessModel;
use Zoop\Theme\DataModel\Template as TemplateModel;

class Validator
{

    private $twig;
    private $errors = [];
    private $hasErrors = false;

    public function __construct(Twig_Environment $twig)
    {
        $this->setTwig($twig);
    }

    public function validate(AbstractThemeThemeModel $theme)
    {
        $assets = $theme->getAssets();
        if (!empty($assets)) {
            $this->validateAssets($assets);
        }

        if ($this->getHasErrors() === true) {
            return false;
        }
        return true;
    }

    public function validateAssets($assets)
    {
        if (!empty($assets)) {
            foreach ($assets as $asset) {
                if ($asset instanceof FolderModel) {
                    $this->validateAssets($asset->getAssets());
                } elseif ($asset instanceof TemplateModel) {
                    $this->validateTwig($asset);
                }
            }
        }
    }

    public function validateTwig(TemplateModel $template)
    {
        try {
            /* @var $twig Twig_Environment */
            $twig = $this->getTwig();
            $twig->parse($twig->tokenize($template->getContent(), $template->getName()));

            // the $template is valid
        } catch (Twig_Error_Syntax $e) {
            // $template contains one or more syntax errors
            $this->addError([
                'message' => 'Template "' . $template->getName() . '" contains errors: ' . $e->getRawMessage() . ' at line: ' . $e->getLine(),
                'asset' => $template
            ]);
            $this->setHasErrors(true);
        }
    }

    public function getTwig()
    {
        return $this->twig;
    }

    public function setTwig(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function getHasErrors()
    {
        return $this->hasErrors;
    }

    public function setHasErrors($hasErrors)
    {
        $this->hasErrors = (bool) $hasErrors;
    }

}
