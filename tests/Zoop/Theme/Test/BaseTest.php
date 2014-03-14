<?php

namespace Zoop\Theme\Test;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;

abstract class BaseTest extends AbstractHttpControllerTestCase
{
    protected $documentManager;

    public function setUp()
    {
        $this->setApplicationConfig(
            require __DIR__ . '/../../../test.application.config.php'
        );
        $dm = $this->getApplicationServiceLocator()->get('doctrine.odm.documentmanager.commerce');
        $this->setDocumentManager($dm);
    }

    /**
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @param DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }
}
