<?php

namespace Zoop\Theme\Lexer\Regex;

use Zoop\Theme\Lexer\Regex\AbstractRegex;

class HtmlImageUrlRegex extends AbstractRegex implements ImageRegexInterface
{
    protected $tokenRegex = '/\<img.*src\=[\'|\"]{1}([^\"\']+)[\'|\"]/';
}
