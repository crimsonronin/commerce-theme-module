<?php

namespace Zoop\Theme\Creator;

use \SplFileInfo;

/**
 * Description of AbstractCreator
 *
 * @author Josh
 */
interface ThemeCreatorImportInterface
{
    public function create(SplFileInfo $uploadedFile);
}
