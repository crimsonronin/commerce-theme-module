<?php

namespace Zoop\Theme\Lexer\Regex;

use Zoop\Theme\Lexer\Regex\AbstractRegex;

class HtmlJavascriptUrlRegex extends AbstractRegex implements JavascriptRegexInterface
{
    protected $tokenRegex = '/\<script.*src\=[\'|\"]{1}([^\"\']+)[\'|\"]{1}/';
}
