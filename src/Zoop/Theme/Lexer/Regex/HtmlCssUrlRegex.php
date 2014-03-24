<?php

namespace Zoop\Theme\Lexer\Regex;

use Zoop\Theme\Lexer\Regex\AbstractRegex;

class HtmlCssUrlRegex extends AbstractRegex implements CssRegexInterface
{
    protected $tokenRegex = '/\<link.*href\=[\'|\"]([^\"\'\{\}]*)[\'|\"]/';
}
