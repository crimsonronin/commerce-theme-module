<?php

namespace Zoop\Theme\Bridge;

abstract class AbstractBridge
{
    protected $data;
    protected $format;
    private static $allowedImageSizes = [
        'raw',
        'extraLarge',
        'large',
        'medium',
        'small',
        'thumbnail'
    ];

    public function getVariables()
    {
        return $this->parse($this->getData());
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    protected function find($key, $legacyData)
    {
        if (isset($legacyData[$key])) {
            return $legacyData[$key];
        } else {
            foreach ($legacyData as $dbKey => $val) {
                if (is_array($val)) {
                    $foundVal = $this->find($key, $val);
                    if ($foundVal !== null) {
                        return $foundVal;
                    }
                }
            }
        }
        return null;
    }

    protected function parseImages($legacyImageData)
    {
        $imageSets = [];
        if (is_array($legacyImageData) && !empty($legacyImageData)) {
            foreach ($legacyImageData as $legacyImageSet) {
                $imageSet = [];
                foreach ($legacyImageSet as $type => $legacyImage) {
                    if (in_array($type, self::$allowedImageSizes)) {
                        $imageSet[$type] = [
                            'alt' => $legacyImage['fileAlt'],
                            'mime' => $legacyImage['fileType'],
                            'extension' => $legacyImage['fileExt'],
                            'height' => $legacyImage['fileHeight'],
                            'width' => $legacyImage['fileWidth'],
                            'src' => $legacyImage['fileSrc']
                        ];
                    }
                }
                $imageSets[] = $imageSet;
            }
        }
        return $imageSets;
    }

    protected function getKey($key)
    {
        return lcfirst(str_replace(' ', '', ucwords(strtolower(str_replace(['_', '-'], ' ', $key)))));
    }

}
