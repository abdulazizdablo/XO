<?php

namespace App\Traits;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait DateScope
{


    public static function scopeDateRange($query, $model_name, $dateScope, $from_date, $to_date)
    {
        if ($dateScope == 'custom') {
            $query->whereBetween('created_at', [$from_date, $to_date]);
        } else {

            if ($dateScope == 'Today') {
                $sort = 'dayToNow';
            } elseif ($dateScope == 'last_week') {
                $sort = 'weekToDate';
            } elseif ($dateScope == 'last_month') {
                $sort = 'monthToDate';
            } elseif ($dateScope == 'last_quarter') {
                $sort = 'quarterToDate';
            } elseif ($dateScope == 'last_year') {
                $sort = 'yearToDate';
            } else {
                $sort = 'monthToDate';
            }

         
            $query = $model_name::$sort();
            
        }

        return $query;
    }
}
