<?php

namespace Zoop\Theme\Lexer;

use \Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\Lexer\Regex\RegexInterface;
use Zoop\Theme\Lexer\Regex\ImageRegexInterface;
use Zoop\Theme\Lexer\Regex\CssRegexInterface;
use Zoop\Theme\Lexer\Regex\JavascriptRegexInterface;
use Zoop\Theme\Tokenizer\TokenStream;
use Zoop\Theme\Tokenizer\Token\AbstractRelativeFileToken;
use Zoop\Theme\Tokenizer\Token\TextToken;
use Zoop\Theme\Tokenizer\Token\Relative;
use Zoop\Theme\Tokenizer\Token\Absolute;

/**
 * The Lexer consumes content and creates a token stream
 * depending on the lexer regexes supplied. These tokens can
 * be css, images, js etc.
 *
 * This then allows the Tokenizer to recompile the original
 * content with the replaced assets.
 *
 */
class Lexer implements LexerInterface
{
    protected $regexes;
    protected $relativeFilePath;
    protected $tempDirectory;

    /**
     * {@inheritdoc}
     */
    public function getRegexes()
    {
        if (!isset($this->regexes)) {
            $this->regexes = new ArrayCollection;
        }
        return $this->regexes;
    }

    /**
     * {@inheritdoc}
     */
    public function setRegexes($regexes = [])
    {
        if (!$regexes instanceof ArrayCollection) {
            $regexes= new ArrayCollection($regexes);
        }
        $this->regexes = $regexes;
    }

    /**
     * {@inheritdoc}
     */
    public function addRegex(RegexInterface $regex)
    {
        $this->getRegexes()->add($regex);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeFilePath()
    {
        return $this->relativeFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelativeFilePath($relativeFilePath)
    {
        $this->relativeFilePath = $relativeFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function setTempDirectory($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }

    /**
     * {@inheritdoc}
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
