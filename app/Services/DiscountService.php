<?php

namespace App\Services;

use App\Models\Discount;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DiscountService
{
    public function getAllDiscounts()
    {
        $discounts = Discount::paginate();

        if (!$discounts) {
            throw new InvalidArgumentException('There Is No Discounts Available');
        }

        return $discounts;
    }

    public function getDiscount(int $discount_id) : Discount
    {
        $discount = Discount::findOrFail($discount_id);

        return $discount;
    }

    public function createDiscount(array $data): Discount
    {
        $discount = Discount::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'city' => $data['city'],
        ]);

        if(!$discount){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $discount;
    }

    public function updateDiscount(array $data, int $discount_id): Discount
    {
        $discount = Discount::findOrFail($discount_id);

        $discount->update($data);

        return $discount;
    }

    public function show(int $discount_id): Discount
    {
        $discount = Discount::findOrFail($discount_id);

        return $discount;
    }

    public function delete(int $discount_id) : void
    {
        $discount = Discount::findOrFail($discount_id);

        $discount->delete();
    }

    public function forceDelete(int $discount_id) : void
    {
        $discount = Discount::findOrFail($discount_id);

        $discount->forceDelete();
    }
}
