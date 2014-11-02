<?php

namespace Zoop\Theme\Creator;

use Zoop\Theme\Helper\ThemeHelperTrait;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\Theme\DataModel\PrivateThemeInterface;
use Zoop\Theme\DataModel\ThemeInterface;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
abstract class AbstractThemeCreator implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ThemeHelperTrait;

    protected $theme;
    protected $unserializer;

    /**
     * @return Unserializer
     */
    public function getUnserializer()
    {
        return $this->unserializer;
    }

    /**
     * @param Unserializer $unserializer
     */
    public function setUnserializer(Unserializer $unserializer)
    {
        $this->unserializer = $unserializer;
    }

    /**
     * @return ThemeInterface
     */
    public function getTheme()
    {
        if (empty($this->theme)) {
            $privateTheme = $this->getServiceLocator()
                ->get('zoop.commerce.theme.private');
            $this->setTheme($privateTheme);
        }
        return $this->theme;
    }

    /**
     * @param ThemeInterface $theme
     */
    public function setTheme(ThemeInterface $theme)
    {
        $this->theme = $theme;
    }
}
