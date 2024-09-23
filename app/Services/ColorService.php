<?php

namespace App\Services;

use App\Models\Color;
use InvalidArgumentException;
use App\Traits\TranslateFields;
use Exception;
use Illuminate\Http\Response;
class ColorService
{
    use TranslateFields;

    public function getAllColors()
    {
        try {
            $colors = Color::get();
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
        return $colors;

    }



    public function getColor(int $color_id)
    {
        $color = Color::findOrFail($color_id);
        $color_fields = ['id', 'value', 'hex_code', 'sku_code'];

        return $color->getFields($color_fields);
    }

    public function createColor(array $data)
    {
        $color = Color::create([
            "name" => $data["name"],
            "hex_code" => $data["hex_code"],
            "sku_code" => $data["sku_code"],
        ]);

        if (!$color) {
            throw new Exception('color not created successfully');
        }

        return $color;
    }

    public function updateColor(array $data, int $color_id, int $user_id): Color
    {
        $color = Color::findOrFail($color_id);
        $data['user_id'] = $user_id;
        $color->update($data);
       

        return $color;
    }

    public function show(int $color_id): Color
    {
        $color = Color::findOrFail($color_id);
        $color_fields = ['id', 'value', 'hex_code', 'sku_code'];

     
        return $color->getFields($color_fields);
    }

    public function delete(int $color_id): void
    {
        $color = Color::findOrFail($color_id);
        $color->delete();
    }

    public function forceDelete(int $color_id): void
    {
        $color = Color::findOrFail($color_id);


        $color->forceDelete();
    }

    public function searchColor(string $search)
    {


        $searched_color = Color::where('name', 'LIKE', '%' . $search . '%')
        ->orWhere('hex_code', 'LIKE', '%' . $search . '%')
        ->orWhere('sku_code', 'LIKE', '%' . $search . '%')
         ->get();

        if (!$searched_color) {

           return response()->success($empty= [],Response::HTTP_CREATED);
        }
        return $searched_color;
    }
}
