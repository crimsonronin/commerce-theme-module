<?php

namespace Zoop\Theme\Linter;

use \Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\DataModel\PrivateThemeInterface;
use Zoop\Theme\Helper\FileHelperTrait;
use Zoop\Theme\Helper\ThemeHelperTrait;
use Zoop\Theme\Lexer\Lexer;
use Zoop\Theme\Parser\Parser;
use Zoop\Theme\DataModel\ContentAssetInterface;

class Linter implements ServiceLocatorAwareInterface
{

    use FileHelperTrait;

use ThemeHelperTrait;

use ServiceLocatorAwareTrait;

    protected $themeStructure;

    /**
     * @param string  $tempDirectory
     * @param PrivateThemeInterface $themeStructure
     */
    public function __construct($tempDirectory, PrivateThemeInterface $themeStructure)
    {
        $this->setTempDirectory($tempDirectory);
        $this->setThemeStructure($themeStructure);
    }

    public function lint(ThemeInterface $theme)
    {
        $lintedAssets = $this->lintAssets($theme->getAssets());

        //get embedded assets
    }

    protected function parseEmbeddedAssets(array $assets)
    {
        $lexer = $this->getLexer();
        $parser = $this->getParser();

        foreach ($assets as $asset) {
            if ($asset instanceof ContentAssetInterface) {
                $tokenStream = $lexer->tokenize($asset->getContent());

                $nodeTree = $parser->parse($tokenStream);
                
                $cssNodes = $nodeTree->getCssNodes();
                $jsNodes = $nodeTree->getJavascriptNodes();
                $imgNodes = $nodeTree->getImageNodes();
            }
        }
    }

    /**
     * This loops through existing assets and checks against a reference
     * theme structure.
     * 
     * @param array $assets
     * @return array
     */
    protected function lintAssets(array $assets)
    {
        $themeStructure = $this->getThemeStructure();

        //get the valid assets as compared with the default theme structure
        $structureAsset = $themeStructure->getAssets()->toArray();

        //get all valid assets
        $validatedAssets = $this->getValidAssets($structureAsset, $assets);

        //merge the valid assets with the default theme
        return $this->addMissingAssets($structureAsset, $validatedAssets);
    }

    /**
     * @return PrivateThemeInterface
     */
    public function getThemeStructure()
    {
        return $this->themeStructure;
    }

    /**
     * @param PrivateThemeInterface $themeStructure
     */
    public function setThemeStructure(PrivateThemeInterface $themeStructure)
    {
        $this->themeStructure = $themeStructure;
    }

    /**
     * @return Lexer
     */
    protected function getLexer()
    {
        return $this->getServiceLocator()
                        ->get('zoop.commerce.theme.lexer.full');
    }

    /**
     * @return Parser
     */
    protected function getParser()
    {
        return $this->getServiceLocator()
                        ->get('zoop.commerce.theme.parser');
    }

}
