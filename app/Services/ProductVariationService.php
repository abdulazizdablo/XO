<?php

namespace App\Services;

use App\Models\ProductVariation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\data;
use Illuminate\Support\ItemNotFoundException;
use InvalidArgumentException;

class ProductVariationService
{

    public function getAllProductVariations()
    {
        $product_variations = ProductVariation::paginate();

        if (!$product_variations) {
            throw new ItemNotFoundException('There Is No product variations Available');
        }

        return $product_variations;
    }

    public function getProductVariation(int $product_id) : ProductVariation
    {
        $product_variation = ProductVariation::findOrFail($product_id);

        return $product_variation;
    }

    public function create(int $variation_id,int $product_id ): ProductVariation
    {
        $productVariation = ProductVariation::create([
            'product_id' => $product_id,
            'variation_id' => $variation_id,
        ]);

        return $productVariation;
    }

    public function update(array $data,int $product_variation_id ): ProductVariation
    {
        $product_variation = ProductVariation::findOrFail($product_variation_id);
        $product_variation->update([
            'hex_code' => $data['hex_code'],
            'color' => $data['color'],
            'size' => $data['size'],
        ]);

        return $product_variation;
    }

    public function delete(int $product_id) : void
    {
        $product_variation = ProductVariation::findOrFail($product_id);

        $product_variation->delete();
    }

    public function forceDelete(int $product_id) : void
    {
        $product_variation = ProductVariation::findOrFail($product_id);

        $product_variation->forceDelete();
    }
}
