<?php

namespace Zoop\Theme\Lexer;

use \Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\Lexer\Regex\AbstractRegex;
use Zoop\Theme\Lexer\Regex\ImageRegexInterface;
use Zoop\Theme\Lexer\Regex\CssRegexInterface;
use Zoop\Theme\Lexer\Regex\JavascriptRegexInterface;
use Zoop\Theme\Tokenizer\TokenStream;
use Zoop\Theme\Tokenizer\Token\TextToken;
use Zoop\Theme\Tokenizer\Token\ImageToken;
use Zoop\Theme\Tokenizer\Token\CssToken;
use Zoop\Theme\Tokenizer\Token\JavascriptToken;

class Lexer
{
    protected $regexes;

    public function __construct()
    {
        $this->regexes = new ArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getRegexes()
    {
        return $this->regexes;
    }

    /**
     * @param ArrayCollection $regexes
     */
    public function setRegexes(ArrayCollection $regexes)
    {
        $this->regexes = $regexes;
    }

    /**
     * @param AbstractRegex $regex
     */
    public function addRegex(AbstractRegex $regex)
    {
        $this->regexes->add($regex);
    }

    /**
     * @param string $content
     * @return TokenStream
     * @throws Exception
     */
    public function tokenize($content)
    {
        $tokenStream = new TokenStream;
        
        if (!empty($content)) {
            foreach ($this->getRegexes() as $regex) {
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    if (preg_match($regex, $line, $matches)) {
                        $tokens = explode($matches[1], $line);
                        $tokenStream->addToken(new TextToken($tokens[0]));

                        //add asset token
                        if ($regex instanceof ImageRegexInterface) {
                            $tokenStream->addToken(new ImageToken($matches[1]));
                        } elseif ($regex instanceof CssRegexInterface) {
                            $tokenStream->addToken(new CssToken($matches[1]));
                        } elseif ($regex instanceof JavascriptRegexInterface) {
                            $tokenStream->addToken(new JavascriptToken($matches[1]));
                        } else {
                            throw new Exception('Could not identify the token of: ' . $regex);
                        }

                        $tokenStream->addToken(new TextToken($tokens[1]));
                    } else {
                        $tokenStream->addToken(new TextToken($line));
                    }
                }
            }
        }
        
        return $tokenStream;
    }
}
