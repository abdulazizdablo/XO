<?php

namespace App\Services;

use App\Models\Size;
use App\Models\SizeGuide;
use InvalidArgumentException;
use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SizeGuideservice
{
    use TranslateFields;



    public function getAllSizeGuides()
    {


      /*  $sizes = SizeGuide::all();

        $minBust = 78.0;
$maxBust = 180.0;
$minWaist = 59.0;
$maxWaist = 180.0;

        foreach($sizes as $size) {

            $size->update(['value' => json_encode(['sizes' => ]) ])
        }

*/
        try {
            // Retrieve all records of the SizeGuide model
            $sizes = SizeGuide::all();

            $valuesArray = json_decode($sizes->first()->values, true);
			$length = count($valuesArray['sizes']);

            $world_size = array_slice($valuesArray['sizes'], 0, $length);
            $world_size = (array_column($world_size, 'value'));





            $modifiedResponse = $sizes->map(function ($sizeGuide) use ($world_size, $length) {
                // Parse the 'values' JSON string
                $values = json_decode($sizeGuide->values, true);




                // Extract the measurements
                /* $world_size = array_map(function ($item) {
                    return $item['value'];
                }, $values['sizes'] ?? []);*/



                // Assuming 'Bust' and 'Waist' are the measurements you want to include


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

                    //   $chest[] = $waist_item . '-' . $bust_items[$key];
                    // $sleeve[] = $waist_item . '-' . $bust_items[$key];
                    // $arm[] = $waist_item . '-' . $bust_items[$key];
                }


                /*  $neck = array_map(function ($item) {
                    return $item['value'];
                }, $values['Bust'] ?? []);

                $chest = array_map(function ($item) {
                    return $item['value'];
                }, $values['Waist'] ?? []);
*/
                // You can add more measurements as needed

                return [$sizeGuide->name => [
                    "world_size" => $world_size,
                    "Neck" => $neck,
                    "Chest" => $chest,
                    "Sleeve" => $sleeve,
                    "Arm" => $arm

                    // Add more measurements here
                ]];
            })->groupBy('name')->values()->values(); // Group by category name and convert to array

            // Check if there are any size guides
            if ($sizes->isEmpty()) {
                throw new ModelNotFoundException('There Is No Sizes Guide Available');
            }

            // Transform each record's JSON data
            /* $transformedData = $sizes->map(function ($record) {
                $jsonData = json_decode($record->values, true); // Assuming 'value' is the column name

                return collect($jsonData['sizes'])->mapWithKeys(function ($size, $index) use ($jsonData,$record) {
                    $sizeValue = $size['value'];
                    $sizeName = $record->name;
                    return [
                        $sizeName => [
                            "Bust" => $jsonData['Bust'][$index]['value'],
                            "Waist" => $jsonData['Waist'][$index]['value']
                        ]
                    ];
                })->toArray();
            });
*/
            return $modifiedResponse->values()->values();
        } catch (ModelNotFoundException $e) {
            // Handle the exception, e.g., log the error or return a custom response
            return response()->json(['error' => $e->getMessage()],  404);
        }
    }



    public function getSizeGuide(int $size_id)
    {
        $size = SizeGuide::findOrFail($size_id);

        $sizeguide_fields = ['id', 'category_id', 'name', 'values'];

        return $size->getFields($sizeguide_fields);
    }

    //     public function createSize(array $data, int $user_id): Size
    //     {
    //         $data['user_id'] = $user_id;
    //         $size = Size::create($data);

    //         if (!$size) {
    //             throw new InvalidArgumentException('Something Wrong Happend');
    //         }

    //         return $size;
    //     }

    //     public function updateSize(array $data, $size_id, $user_id): Size
    //     {
    //         $size = Size::find($size_id);
    //         $data['user_id'] = $user_id;
    //         $size->update($data);
    //         if (!$size) {
    //             throw new InvalidArgumentException('There Is No Sizes Available');
    //         }

    //         return $size;
    //     }

    //     public function show($size_id): Size
    //     {
    //         $size = Size::find($size_id);
    //         $size_fields = ['id','value','sku_code'];

    //         if (!$size) {
    //             throw new InvalidArgumentException('Size not found');
    //         }

    //         return $size->getFields($size_fields);
    //     }

    //     public function delete(int $size_id): void
    //     {
    //         $size = Size::find($size_id);

    //         if (!$size) {
    //             throw new InvalidArgumentException('Size not found');
    //         }

    //         $size->delete();
    //     }

    //     public function forceDelete(int $size_id): void
    //     {
    //         $size = Size::find($size_id);

    //         if (!$size) {
    //             throw new InvalidArgumentException('Size not found');
    //         }

    //         $size->forceDelete();
    //     }
    // }

}
