<?php

namespace Zoop\Theme\Test\Manager;

use \SplFileInfo;
use Zoop\Theme\DataModel\PrivateTheme;
use Zoop\Theme\Test\AbstractTest;
use Zoop\Theme\LegacyTemplateManager as TemplateManager;
use Zoop\Test\Helper\DataHelper;

class TemplateManagerTest extends AbstractTest
{
    public function testStorefrontTemplateManagerMissing()
    {
        DataHelper::createZoopUser(self::getNoAuthDocumentManager(), self::getDbName());
        DataHelper::createStores(self::getNoAuthDocumentManager(), self::getDbName());

        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('apple.zoopcommerce.local');

        $this->setExpectedException('Exception');

        $manager = $this->getApplicationServiceLocator()->get('zoop.commerce.theme.template.storefront');
    }

    public function testStorefrontTemplateManager()
    {
        $creator = self::getThemeCreatorImport();
        $creator->setMaxFileUploadSize(1024 * 1024 * 20);

        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('apple.zoopcommerce.local');

        //insert templates
        $uploadedFile = new SplFileInfo(__DIR__ . '/../Assets/complex-theme.zip');

        $theme = $creator->import($uploadedFile);
        $this->assertTrue($theme instanceof PrivateTheme);

        $theme->addStore('apple');
        $theme->setIsActive(true);

        self::saveTheme($theme);

        $manager = $this->getApplicationServiceLocator()->get('zoop.commerce.theme.template.storefront');

        $this->assertTrue($manager instanceof TemplateManager);
        $manager->setFile('index.html');

        $html = $manager->render();
        $this->assertNotEmpty($html);
    }
}
