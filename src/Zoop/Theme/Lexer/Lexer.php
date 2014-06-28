<?php

namespace Zoop\Theme\Lexer;

use \Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\Lexer\Regex\AbstractRegex;
use Zoop\Theme\Lexer\Regex\ImageRegexInterface;
use Zoop\Theme\Lexer\Regex\CssRegexInterface;
use Zoop\Theme\Lexer\Regex\JavascriptRegexInterface;
use Zoop\Theme\Tokenizer\TokenStream;
use Zoop\Theme\Tokenizer\Token\AbstractRelativeFileToken;
use Zoop\Theme\Tokenizer\Token\TextToken;
use Zoop\Theme\Tokenizer\Token\Relative;
use Zoop\Theme\Tokenizer\Token\Absolute;

class Lexer
{
    protected $regexes;
    protected $relativeFilePath;
    protected $tempDirectory;

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
     * @return string
     */
    public function getRelativeFilePath()
    {
        return $this->relativeFilePath;
    }

    /**
     * @param string $relativeFilePath
     */
    public function setRelativeFilePath($relativeFilePath)
    {
        $this->relativeFilePath = $relativeFilePath;
    }

    /**
     * @return string
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * @param string $tempDirectory
     */
    public function setTempDirectory($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
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

                        $isRemote = isset(parse_url($matches[1])['scheme']);

                        //add asset token
                        if ($regex instanceof ImageRegexInterface) {
                            if ($isRemote) {
                                $token = new Absolute\ImageToken($matches[1]);
                            } else {
                                $token = new Relative\ImageToken($matches[1]);
                            }
                        } elseif ($regex instanceof CssRegexInterface) {
                            if ($isRemote) {
                                $token = new Absolute\CssToken($matches[1]);
                            } else {
                                $token = new Relative\CssToken($matches[1]);
                            }
                        } elseif ($regex instanceof JavascriptRegexInterface) {
                            if ($isRemote) {
                                $token = new Absolute\JavascriptToken($matches[1]);
                            } else {
                                $token = new Relative\JavascriptToken($matches[1]);
                            }
                        } else {
                            throw new Exception('Could not identify the token of: ' . $regex);
                        }

                        if (isset($token)) {
                            if ($token instanceof AbstractRelativeFileToken) {
                                $token->setFilePath($this->getRelativeFilePath());
                            }
                            $token->setTempDirectory($this->getTempDirectory());
                            $tokenStream->addToken($token);
                            unset($token);
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
