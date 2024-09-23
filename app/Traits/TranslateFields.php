<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

trait TranslateFields
{
    private function castBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value) && in_array(strtolower($value), ['true', 'false'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }
    public function getField($field)
    {
        // Check if the given field is marked as translatable
        if (in_array($field, $this->translatable)) {
            // If the field is translatable, get its value in the current locale using the getTranslation method
            return $this->getTranslation($field, app()->getLocale());
        }

        // If the field is not translatable, simply return its value
        return $this->{$field};
    }

    public function getFields($fields)
    {
        // Initialize an empty array to hold the result
        $result = [];

        // Loop over the given array of field names
        foreach ($fields as $field) {
            // For each field, call the getField method to get its value
            // and add it to the result array under the same field name
            $result[$field] = $this->getField($field);
        }

        // Return the result array containing the values of the requested fields
        return $result;
    }

    public static function getTranslatedFields($collection, array $fields)
    {
        // Use the map method to apply a callback function to each model in the collection
        return $collection->map(function ($model) use ($fields) {
            // For each model, call the getFields method to get the translated values of the requested fields
            // and return the result as a new array
            return $model->getFields($fields);
        });
    }

    public function getPaginatedTranslatedFields($collection, array $fields, $perPage = 10, $page = 1)
    {
        // Use the map method to apply a callback function to each model in the collection
        $result = $collection->map(function ($model) use ($fields) {
            // For each model, call the getFields method to get the translated values of the requested fields
            // and return the result as a new array
            return $model->getFields($fields);
        });

        // Define an array containing pagination information for the collection
        $pagination = [
            "current_page" => $collection->currentPage(),
            "first_page_url" =>  $collection->getOptions()['path'] . '?' . $collection->getOptions()['pageName'] . '=1',
            "prev_page_url" =>  $collection->previousPageUrl(),
            "next_page_url" =>  $collection->nextPageUrl(),
            "last_page_url" =>  $collection->getOptions()['path'] . '?' . $collection->getOptions()['pageName'] . '=' . $collection->lastPage(),
            "last_page" =>  $collection->lastPage(),
            "per_page" =>  $collection->perPage(),
            "total" =>  $collection->total(),
            "path" =>  $collection->getOptions()['path'],
        ];

        // Return an array containing the translated fields and pagination information
        return [
            'data' => $result,
            'pagination' => $pagination,
        ];

        // The following line of code is unreachable, so it has been commented out:
        return $result->paginate($perPage, $page);
    }
}
