<?php

namespace Zoop\Theme;

use \Twig_Environment;
use Zoop\Catalog\Product;

class TwigEnvironment extends Twig_Environment
{

    protected $productService;

    public function getProductService()
    {
        return $this->productService;
    }

    public function setProductService(Product $productService)
    {
        $this->productService = $productService;
    }

}
