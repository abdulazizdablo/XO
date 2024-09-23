<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class StringHelper
{
    public static function transformDateCreated($inputString)
    {
        $parts = explode('_', $inputString);
        
        if (count($parts) < 2) {
            return $inputString;
        }
        
        $datePart = Str::studly($parts[0]);
        $createdPart = Str::studly($parts[1]);
        
        return $datePart . $createdPart;
    }
}
