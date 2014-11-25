<?php

namespace Zoop\Theme\Lexer;

use \Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Theme\Lexer\Regex\RegexInterface;
use Zoop\Theme\Lexer\Regex\ImageRegexInterface;
use Zoop\Theme\Lexer\Regex\CssRegexInterface;
use Zoop\Theme\Lexer\Regex\JavascriptRegexInterface;
use Zoop\Theme\Tokenizer\TokenStream;
use Zoop\Theme\Tokenizer\Token\Absolute;
use Zoop\Theme\Tokenizer\Token\AbstractRelativeFileToken;
use Zoop\Theme\Tokenizer\Token\Relative;
use Zoop\Theme\Tokenizer\Token\TextToken;
use Zoop\Theme\Tokenizer\Token\TokenInterface;

/**
 * The Lexer consumes content and creates a token stream
 * depending on the lexer regexes supplied. These tokens can
 * be css, images, js etc.
 *
 * This then allows the Tokenizer to recompile the original
 * content with the replaced assets.
 *
 * TODO: Look at making the dependencies use invokables or interfaces instead
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

                        $token = $this->tokenizeLine($regex, $matches);

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

    /**
     * @param string $regex
     * @param array $matches
     * @return TokenInterface
     */
    private function tokenizeLine($regex, array $matches)
    {
        $isRemote = isset(parse_url($matches[1])['scheme']);

        //add asset token
        if ($regex instanceof ImageRegexInterface) {
            return $this->getImageToken($matches[1], $isRemote);
        } elseif ($regex instanceof CssRegexInterface) {
            return $this->getCssToken($matches[1], $isRemote);
        } elseif ($regex instanceof JavascriptRegexInterface) {
            return $this->getJavascriptToken($matches[1], $isRemote);
        } else {
            throw new Exception('Could not identify the token of: ' . $regex);
        }
    }

    /**
     * @param string $url
     * @param boolean $isRemote
     * @return TokenInterface
     */
    private function getImageToken($url, $isRemote = false)
    {
        if ($isRemote) {
            return new Absolute\ImageToken($url);
        } else {
            return new Relative\ImageToken($url);
        }
    }

    /**
     * @param string $url
     * @param boolean $isRemote
     * @return TokenInterface
     */
    private function getCssToken($url, $isRemote = false)
    {
        if ($isRemote) {
            return new Absolute\CssToken($url);
        } else {
            return new Relative\CssToken($url);
        }
    }

    /**
     * @param string $url
     * @param boolean $isRemote
     * @return TokenInterface
     */
    private function getJavascriptToken($url, $isRemote = false)
    {
        if ($isRemote) {
            return new Absolute\JavascriptToken($url);
        } else {
            return new Relative\JavascriptToken($url);
        }
    }
}
