<?php

namespace Zoop\Theme\Extension\Core;

use \Twig_Extension;
use \Twig_SimpleFilter;

class SortExtension extends Twig_Extension
{

    public function getFilters()
    {
        return [
            'sort_by' => new Twig_SimpleFilter('sort_by', [$this, 'sortBy']),
        ];
    }

    public function getName()
    {
        return 'sort_by';
    }

    public function sortBy($collections, $varName, $orderBy = 'asc')
    {
        if (is_array($collections)) {
            $tempSortCollection = [];
            $sortedCollection = [];
            foreach ($collections as $key => $collection) {
                if (isset($collection[$varName])) {
                    $tempSortCollection[$key] = $collection[$varName];
                }
            }

            if ($orderBy == 'desc') {
                arsort($tempSortCollection);
            } else {
                asort($tempSortCollection);
            }

            foreach ($tempSortCollection as $key => $orderName) {
                $sortedCollection[$key] = $collections[$key];
            }
            return $sortedCollection;
        }
        return $collection;
    }

}
