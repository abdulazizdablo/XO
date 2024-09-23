<?php

namespace App\Services;

use App\Models\Variation;
use InvalidArgumentException;
use Illuminate\Support\Str;

class VariationService
{
    public function getAllVariations()
    {
        $variations = Variation::paginate();

        if (!$variations) {
            throw new InvalidArgumentException('There Is No Variations Available');
        }

        return $variations;
    }

    public function getVariation($variation_id) : Variation
    {
        $variation = Variation::find($variation_id);

        if (!$variation) {
            throw new InvalidArgumentException('Variation not found');
        }

        return $variation;
    }

    public function createVariation(array $data): Variation
    {
        $variation = Variation::create([
            'type' => Str::lower($data['property']['en']),
            'property' => $data['property'],
            'value' => $data['value'],
            'hex_code' => isset($data['hex_code']) ? $data['hex_code'] : null,
        ]);

        if(!$variation){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $variation;
    }

    public function updateVariation(array $data, $variation_id): Variation
    {
        $variation = Variation::find($variation_id);
        if(!$variation){
            throw new InvalidArgumentException('There Is No Variations Available');
        }
        $variation->update([
            'property' => $data['property'],
            'value' => $data['value'],
            'hex_code' => $data['hex_code'],
        ]);

        return $variation;
    }

    public function show($variation_id): Variation
    {
        $variation = Variation::find($variation_id);

        if(!$variation){
            throw new InvalidArgumentException('Variation not found');
        }

        return $variation;
    }

    public function delete(int $variation_id) : void
    {
        $variation = Variation::find($variation_id);

        if (!$variation) {
            throw new InvalidArgumentException('Variation not found');
        }

        $variation->delete();
    }

    public function forceDelete(int $variation_id) : void
    {
        $variation = Variation::find($variation_id);

        if (!$variation) {
            throw new InvalidArgumentException('Section not found');
        }

        $variation->forceDelete();
    }
}
