<?php

namespace Zoop\Theme\Extension\Node\Collection;

use \Twig_Node;
use \Twig_Node_Expression;
use \Twig_Compiler;

class Get extends Twig_Node
{

    public function __construct(Twig_Node_Expression $collection, $line, $tag = null)
    {
        parent::__construct(['collection' => $collection], [], $line, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
       die(var_dump($this->getNode('collection')));
        $compiler->addDebugInfo($this)
                ->write('$context[\'_collection\'] = ')
                ->subcompile($this->getNode('collection'))
                ->raw(";\n");

        $compiler->write("\$context['productService'] = \$this->env->getProductService();\n");

        $compiler->write("if(!empty(\$context['productService']) && isset(\$context['_collection']['legacyId'])) {\n")
                ->indent()
                ->write("\$context['test'] = \$context['productService']->getFromCategory(\$context['_collection']['legacyId']);\n")
                ->write("var_dump(\$context['test']);\n")
                ->outdent()
                ->write("}\n");
    }

}
