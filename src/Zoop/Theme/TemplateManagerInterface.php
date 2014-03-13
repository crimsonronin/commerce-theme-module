<?php

namespace Zoop\Theme;

interface TemplateManagerInterface
{

    public function setFile($file);

    public function render();

    public function setVariable($key, $value, $merge = true);
}
