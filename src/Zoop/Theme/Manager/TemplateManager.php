<?php

namespace Zoop\Theme\Manager;

class TemplateManager extends AbstractTemplateManager implements TemplateManagerInterface
{
    public function render()
    {
        return $this->load($this->getFile(), $this->getVariables());
    }

    public function load($file, $data = [])
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        $template = $this->getTwig()->loadTemplate($file);
        return $template->render($data);
    }
}
