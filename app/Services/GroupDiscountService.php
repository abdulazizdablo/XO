<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupDiscount;
use App\Models\Product;
// use App\Traits\TranslateFields;
use InvalidArgumentException;

class GroupDiscountService
{
    // use TranslateFields;
    public function getAllGroupDiscounts()
    {
        $group_products = Group::with([
            'products:id,promotionable_type,promotionable_id,sub_category_id',
            'products.main_photos:id,product_id,color_id,path,main_photo'
        ])->valid()->get();

        if (!$group_products) {
            throw new InvalidArgumentException('There Is No GroupDiscounts Available');
        }

        return $group_products;
    }

    public function getGroupDiscountProducts($group_id)
    {
        $group = Group::findOrFail($group_id);
        // $group_products = GroupDiscount::where($group_offer_id);

       /* if (!$group) {
            throw new InvalidArgumentException('group product not found');
        }*/
        
        if (!$group->valid()) {
            throw new InvalidArgumentException('group product not found');
        }

        $products = $group->load([
            'products'
        ])->get()->pluck('products')->flatten();

        $discount = $group->load('discount')->get()->pluck('discount')->flatten();

        $group_products = [
            'products' => $products,
            'discount' => $discount
        ];

        return $group_products;
    }

    public function createGroupDiscount(array $data)
    {
        $group_product = GroupDiscount::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'city' => $data['city'],
        ]);

        if (!$group_product) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $group_product;
    }

    public function updateGroupDiscount(array $data, $group_product_id)
    {
        $group_product = GroupDiscount::find($group_product_id);

        if (!$group_product) {
            throw new InvalidArgumentException('There Is No GroupDiscounts Available');
        }

        $group_product->update($data);

        return $group_product;
    }

    public function show($group_product_id)
    {
        $group_product = GroupDiscount::findOrFail($group_product_id);

      /*  if (!$group_product) {
            throw new InvalidArgumentException('group product not found');
        }*/

        return $group_product;
    }

    public function delete(int $group_product_id): void
    {
        $group_product = GroupDiscount::findOrFail($group_product_id);

      /*  if (!$group_product) {
            throw new InvalidArgumentException('group product not found');
        }*/

        $group_product->delete();
    }

    public function forceDelete(int $group_product_id): void
    {
        $group_product = GroupDiscount::findOrFail($group_product_id);

       /* if (!$group_product) {
            throw new InvalidArgumentException('Group Product not found');
        }*/

        $group_product->forceDelete();
    }

    public function attach(Group $group, Product $product)
    {
        try {
            $group->products()->attach($product->id);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function detach(Group $group, Product $product)
    {
        try {
            $group->products()->detach($product->id);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
