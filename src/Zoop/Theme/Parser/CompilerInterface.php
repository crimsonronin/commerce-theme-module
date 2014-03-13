<?php

namespace Zoop\Theme\Parser;

interface CompilerInterface
{

    public static function compile($content, $variables = []);
}
