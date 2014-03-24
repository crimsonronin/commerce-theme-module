<?php

namespace Zoop\Theme\Lexer\Regex;

use Zoop\Theme\Lexer\Regex\AbstractRegex;

class CssImageUrlRegex extends AbstractRegex implements ImageRegexInterface
{
    protected $tokenRegex = '/^(?!\@import).*url\s*\([\'"]?(?<url>[^)]+?)[\'"]?\)/';
}
