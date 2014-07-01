<?php

namespace Zoop\Theme;

use Zoop\Theme\DataModel\AssetInterface;
use Zoop\Theme\DataModel\Css as CssModel;
use Zoop\Theme\DataModel\GzippedCss as GzippedCssModel;
use Zoop\Theme\DataModel\Image as ImageModel;
use Zoop\Theme\DataModel\Javascript as JavascriptModel;
use Zoop\Theme\DataModel\GzippedJavascript as GzippedJavascriptModel;
use Zoop\Theme\DataModel\Template as TemplateModel;
use Zoop\Theme\Parser\Html as HtmlParser;

/**
 *
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class AssetManager
{
    private function getAssetUrl(AssetInterface $asset)
    {
        if ($asset instanceof ImageModel) {
            return $asset->getSrc();
        } elseif ($asset instanceof JavascriptModel || $asset instanceof GzippedJavascriptModel) {
            return $asset->getSrc();
        } elseif ($asset instanceof CssModel || $asset instanceof GzippedCssModel) {
            return $asset->getHref();
        }
    }

    public function getAssetsFromContent(TemplateModel $asset)
    {
        $htmlParser = new HtmlParser();
        $htmlParser->setContent($asset->getContent());
        $htmlParser->parse();

        $cssAssets = $htmlParser->getParsedCssAssets();
        $jsAssets = $htmlParser->getParsedJavascriptAssets();
        $imageAssets = $htmlParser->getParsedImageAssets();

        $parsedAssets = array_merge($cssAssets, $jsAssets, $imageAssets);

        if (!empty($parsedAssets)) {
            $asset->setContent($htmlParser->compileContent($parsedAssets));
        }
    }

    public function getAssetsFromContentLegacy(TemplateModel $asset)
    {
        $htmlParser = new HtmlParser();
        $htmlParser->setContent($asset->getContent());
        $htmlParser->parse();

        $cssAssets = $this->getAssetFromContent(
            $htmlParser->getParsedCssAssets(),
            'assets/css',
            '/text\/(plain|html|css)/',
            'css'
        );

        $jsAssets = $this->getAssetFromContent(
            $htmlParser->getParsedJavascriptAssets(),
            'assets/javascript',
            '/text\/(plain|html|js)/',
            'javascript'
        );

        $imageAssets = $this->getAssetFromContent(
            $htmlParser->getParsedImageAssets(),
            'assets/images',
            '/image\/(.*)/',
            'image'
        );

        $parsedAssets = array_merge($cssAssets, $jsAssets, $imageAssets);

        if (!empty($parsedAssets)) {
            $asset->setContent($htmlParser->compileContent($parsedAssets));
        }
    }
}
