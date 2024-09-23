<?php

namespace App\Services;

use App\Models\Pricing;
use App\Models\Product;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\Price;

class PricingService
{
    public function getAllPricings()
    {
        $pricings = Pricing::paginate();

        if (!$pricings) {
            throw new InvalidArgumentException('There Is No Pricings Available');
        }

        return $pricings;
    }

    public function getPricing(int $pricing_id): Pricing
    {
        $pricing = Pricing::findOrFail($pricing_id);

        return $pricing;
    }

    public function createPricing(array $data, int $product_id): Pricing
    {
        $pricing = Pricing::create([
            'product_id' => $product_id,
            'name' => $data['name'],
            'currency' => $data['currency'],
            'value' => $data['value'],
            'locale' => $data['locale'],
        ]);

        if (!$pricing) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $pricing;
    }

    public function updatePricing(array $data, $pricing_id, int $product_id): Pricing
    {
        $pricing = Pricing::findOrFail($pricing_id);
		
        $data['product_id'] = $product_id;

        $pricing->update($data);

        return $pricing;
    }

    public function bulkUpdatePricing($product_data)
    {
        try {

            $pricings = Pricing::whereIntegerInRaw('product_id', $product_data['products_ids'])->get();

            foreach ($pricings as $pricing) {
                $pricing->update(['value' => $product_data['price']]);
            }

            $message = "Pricings Updated Successfully";
            return $message;

        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function show(int $pricing_id): Pricing
    {
        $pricing = Pricing::findOrFail($pricing_id);

        return $pricing;
    }

    public function delete(int $pricing_id): void
    {
        $pricing = Pricing::findOrFail($pricing_id);

        $pricing->delete();
    }

    public function forceDelete(int $pricing_id): void
    {
        $pricing = Pricing::findOrFail($pricing_id);

        $pricing->forceDelete();
    }
}
