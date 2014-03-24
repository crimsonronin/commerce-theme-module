<?php

namespace Zoop\Theme\Lexer\Regex;

use Zoop\Theme\Lexer\Regex\AbstractRegex;

class CssImportUrlRegex extends AbstractRegex implements CssRegexInterface
{
    protected $tokenRegex = '/\@import [url]*[\(]?\s*[\'"]?(?<url>[^)]+?)[\'"]?[\)]?\;/';
}
