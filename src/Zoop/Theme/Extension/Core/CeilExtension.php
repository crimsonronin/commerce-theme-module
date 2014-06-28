<?php

namespace Zoop\Theme\Extension\Core;

use \Twig_Extension;
use \Twig_SimpleFilter;

class CeilExtension extends Twig_Extension
{
    public function getFilters()
    {
        return [
            'ceil' => new Twig_SimpleFilter('ceil', [$this, 'ceil']),
        ];
    }

    public function ceil($number)
    {
        return ceil($number);
    }

    public function getName()
    {
        return 'ceil_extension';
    }
}
