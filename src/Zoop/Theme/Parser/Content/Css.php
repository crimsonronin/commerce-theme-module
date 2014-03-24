<?php

namespace Zoop\Theme\Parser\Content;

use Zoop\Theme\Parser\Tokenizer;
use Zoop\Theme\Parser\Token;

/**
 * This class accepts a CSS model and parses it
 * looking for additional CSS file references or Images.
 *
 * We can then process those files separately to ensure
 * that all content lives on our CDN
 *
 * @package    Zoop Commerce Theme
 * @author     Josh Stuart <josh.stuart@zoopcommerce.com>
 *
 * @copyright  Zoop Pty Ltd
 */
class Css extends AbstractContentParser
{

    private $images = [];
    private $imports = [];

    public function __construct()
    {
        $tokenizer = new Tokenizer();
        $tokenizer->addToken(new Token("/url\([\'|\"]*(.*)[\'|\"]*\)/"));
        $tokenizer->addToken(new Token("/\@import\([\'|\"]*(.*)[\'|\"]*\)/"));
    }

}
