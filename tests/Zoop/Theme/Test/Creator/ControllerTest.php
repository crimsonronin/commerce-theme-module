<?php

namespace Zoop\Theme\Test\Creator;

use \SplFileInfo;
use Zoop\Theme\Test\BaseTest;

class ControllerTest extends BaseTest
{
    public function testSimpleThemeImport()
    {
        $controller = $this->getApplicationServiceLocator()
            ->get('zoop.commerce.controller.admin.theme');
        /* @var $controller \Zoop\Theme\Controller\ThemeController */

        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/simple-theme.zip');

        $theme = $controller->import($uploadedFile);
        die(var_dump($theme));
    }
}
