<?php

namespace Zoop\Theme\Parser;

class Compiler implements CompilerInterface
{

    private static $LEXER = ['{#%', '%#}'];

    public static function compile($content, $variables = [])
    {
        if (!empty($content) && is_array($variables) && !empty($variables)) {
            foreach ($variables as $key => $var) {
                $content = str_replace((self::$LEXER[0] . $key . self::$LEXER[1]), $var, $content);
            }
        }
        return $content;
    }

}
