<?php

namespace Zoop\Theme\Parser;

class Parser extends AbstractParser implements ParserInterface
{

    private static $LEXER = ['{#%', '%#}'];
    private $parserRegEx;

    public function __construct($parserRegEx)
    {
        $this->setParserRegEx($parserRegEx);
    }

    public function getParserRegEx()
    {
        return $this->parserRegEx;
    }

    public function setParserRegEx($parserRegEx)
    {
        $this->parserRegEx = $parserRegEx;
    }

    public function parse()
    {
        $parsedContent = [];
        $content = $this->getContent();
        if (!empty($content)) {
            $regEx = $this->getParserRegEx();
            if (!empty($regEx)) {
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    if (preg_match($regEx, $line, $matches)) {
                        $key = md5($matches[1]);
                        $parsedContent[] = str_replace($matches[1], (self::$LEXER[0] . $key . self::$LEXER[1]), $line);
                        $this->addParsedAsset($key, $matches[1]);
                    } else {
                        $parsedContent[] = $line;
                    }
                }
            }
            $this->setParsedContent(implode("\n", $parsedContent));
        }
    }

}
