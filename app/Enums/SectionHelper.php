<?php

namespace App\Enums;

use ReflectionClass;

class SectionHelper
{
    public static function getIdNameFromSections(int $id): ?string
    {
        // Get the ReflectionClass instance for the Sections enum
        $reflection = new ReflectionClass(Sections::class);

        // Get all constants and their values
        $constants = $reflection->getConstants();

        // Check if the ID exists in the constants
        foreach ($constants as $constant => $value) {
            if ($value === $id) {
                // Return the name of the constant if a match is found
                return $constant;
            }
        }

        // Return null if no match is found
        return null;
    }
}
