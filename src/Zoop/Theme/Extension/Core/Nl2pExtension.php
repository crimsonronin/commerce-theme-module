<?php

namespace Zoop\Theme\Extension\Core;

use \Twig_Extension;
use \Twig_SimpleFilter;

class Nl2pExtension extends Twig_Extension
{
    public function getFilters()
    {
        return [
            'nl2p' => new Twig_SimpleFilter('nl2p', [$this, 'nl2p']),
        ];
    }

    public function nl2p($value)
    {
        $paragraphs = [];

        foreach (explode("\n", $value) as $line) {
            if (trim($line)) {
                $paragraphs[] = '<p>' . $line . '</p>';
            }
        }

        return implode('', $paragraphs);
    }

    public function getName()
    {
        return 'nl2p';
    }

}
