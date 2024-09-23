<?php

namespace App\Services;

use App\Models\Size;
use InvalidArgumentException;
use App\Traits\TranslateFields;
use Illuminate\Http\Response;
class Sizeservice
{
    use TranslateFields;
    public function getAllSizes()
    {
        $sizes = Size::select('id', 'value', 'sku_code', 'type')->distinct()->get();

        $size_fields = ['id', 'value', 'sku_code', 'type'];

        if (!$sizes) {
            throw new InvalidArgumentException('There Is No Sizes Available');
        }
        return $this->getTranslatedFields($sizes, $size_fields);
    }



    public function getSize(int $size_id)
    {
        $size = Size::findOrFail($size_id);
        $size_fields = ['id', 'value', 'sku_code', 'type'];

        /*    if (!$size) {
            throw new InvalidArgumentException('Size not found');
        }
*/
        return $size->getFields($size_fields);
    }

    public function createSize(array $data, int $user_id): Size
    {
        $data['user_id'] = $user_id;
        $size = Size::create($data);

        if (!$size) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $size;
    }

    public function updateSize(array $data, int $size_id, int $user_id): Size
    {
        $size = Size::findOrFail($size_id);
        $data['user_id'] = $user_id;
        $size->update($data);
        if (!$size) {
            throw new InvalidArgumentException('There Is No Sizes Available');
        }

        return $size;
    }

    public function show(int $size_id): Size
    {
        $size = Size::findOrFail($size_id);
        $size_fields = ['id', 'value', 'sku_code', 'type'];
        return $size->getFields($size_fields);
    }

    public function delete(int $size_id): void
    {
        $size = Size::findOrFail($size_id);
        $size->delete();
    }

    public function forceDelete(int $size_id): void
    {
        $size = Size::findOrFail($size_id);

        $size->forceDelete();
    }


    public function searchSize(string $search)
    {

        $search_parameter = "%$search%";

        $searched_size = Size::where('value->ar', 'LIKE', $search_parameter)->orWhere('value->en', 'LIKE', $search_parameter)->orWhere('sku_code', 'LIKE', $search_parameter)->get();

        if (!$searched_size) {

            return response()->success($empty= [],Response::HTTP_CREATED);;
        }

        return $searched_size;
    }
}
