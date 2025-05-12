<?php

namespace App\Services;

use App\Models\Size;
use InvalidArgumentException;
use App\Traits\TranslateFields;
class Sizeservice
{
    use TranslateFields;
    
    public function getAllSizes()//si
    {
        $sizes = Size::select('id', 'value', 'sku_code', 'type')->distinct()->get();

        $size_fields = ['id', 'value', 'sku_code', 'type'];

        if (!$sizes) {
            throw new InvalidArgumentException('There Is No Sizes Available');
        }
        return $this->getTranslatedFields($sizes, $size_fields);
    }
}
