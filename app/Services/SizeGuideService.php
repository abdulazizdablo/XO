<?php

namespace App\Services;

use App\Models\SizeGuide;
use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SizeGuideservice
{
    use TranslateFields;



    public function getAllSizeGuides()//si
    {
        try {
            $sizes = SizeGuide::all();
            $valuesArray = json_decode($sizes->first()->values, true);
			$length = count($valuesArray['sizes']);

            $world_size = array_slice($valuesArray['sizes'], 0, $length);
            $world_size = (array_column($world_size, 'value'));
            $modifiedResponse = $sizes->map(function ($sizeGuide) use ($world_size, $length) {
                $values = json_decode($sizeGuide->values, true);
                $bust_items = array_column($values['Bust'], 'value');
                $neck = [];
                $chest = [];
                $sleeve = [];
                $arm = [];
                foreach (array_column($values['Waist'], 'value') as $key => $waist_item) {
                   
                    if ($key < $length) {
                        $neck[] = $waist_item . '-' . $bust_items[$key];
                    } else if ($key  < ($length * 2)) {
                        $chest[] = $waist_item . '-' . $bust_items[$key];
                    } else if ($key < ($length * 3)) {

                        $sleeve[] = $waist_item . '-' . $bust_items[$key];
                    } else if ($key < ($length *4)) {

                        $arm[] = $waist_item . '-' . $bust_items[$key];
                    }
                }

                return [$sizeGuide->name => [
                    "world_size" => $world_size,
                    "Neck" => $neck,
                    "Chest" => $chest,
                    "Sleeve" => $sleeve,
                    "Arm" => $arm

                ]];
            })->groupBy('name')->values()->values(); 

            if ($sizes->isEmpty()) {
                throw new ModelNotFoundException('There Is No Sizes Guide Available');
            }

            return $modifiedResponse->values()->values();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()],  404);
        }
    }
}
