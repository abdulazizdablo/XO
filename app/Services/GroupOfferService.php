<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupOffer;
use App\Models\Product;
// use App\Traits\TranslateFields;
use InvalidArgumentException;
use Illuminate\Support\Str;

class GroupOfferService
{
    // use TranslateFields;
    public function getAllGroupOffers()
    {
        $group_offers = GroupOffer::with(['offer'])->get();

        $offers = array();
        foreach ($group_offers as $group_offer) {
            $offer = $group_offer->offer->valid()->select('id', 'valid', 'name', 'type', 'percentage')->get();
            array_push($offers, $offer);
        }

        if (empty($offers)) {
            throw new InvalidArgumentException('There Is No GroupOffers Available');
        }

        return $offers;
    }

    public function getGroupOfferProducts($filter_data)
    {
        $offers_products = Group::where('type', 'offer')
            ->select('id', 'name', 'expired', 'image_path')
            ->valid()
            ->with([
                'products' => function ($query) {
                    $query->select('products.id','products.name','products.available')
                    ->where('products.available', true);
                },
                'products.photos' => function ($query) {
                    $query->where('main_photo', true);
                }
            ])
            ->get();

        if (!empty($filter_data)) {
            $products = $this->applyFilters($offers_products, $filter_data);
        }

        // $products = array();
        // foreach ($offers_products as $offer) {
        //     // $items = $offer->products;
        //     $product = $offer->products()->available()
        //         ->select('products.id', 'products.name', 'products.available')
        //         ->get();
        //     array_push($products, $product);
        // }

        return $offers_products;
        // $group_products = GroupOffer::where($group_offer_id);

        if (!$group) {
            throw new InvalidArgumentException('group product not found');
        }

        if (!$group->valid()) {
            throw new InvalidArgumentException('group product not found');
        }

        $products = $group->load([
            'products'
        ])->get()->pluck('products')->flatten();

        $offer = $group->load('offer')->get()->pluck('offer')->flatten();

        $group_products = [
            'products' => $products,
            'offer' => $offer
        ];

        return $group_products;
    }

    public function createGroupOffer(array $data)
    {
        $group_product = GroupOffer::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'city' => $data['city'],
        ]);

        if (!$group_product) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $group_product;
    }

    public function updateGroupOffer(array $data, $group_product_id)
    {
        $group_product = GroupOffer::find($group_product_id);

        if (!$group_product) {
            throw new InvalidArgumentException('There Is No GroupOffers Available');
        }

        $group_product->update($data);

        return $group_product;
    }

    public function show($group_product_id)
    {
        $group_product = GroupOffer::findOrFail($group_product_id);

      /*  if (!$group_product) {
            throw new InvalidArgumentException('group product not found');
        }*/

        return $group_product;
    }

    public function delete(int $group_product_id): void
    {
        $group_product = GroupOffer::findOrFail($group_product_id);

     /*   if (!$group_product) {
            throw new InvalidArgumentException('group product not found');
        }
*/
        $group_product->delete();
    }

    public function forceDelete(int $group_product_id): void
    {
        $group_product = GroupOffer::findOrFail($group_product_id);

      /*  if (!$group_product) {
            throw new InvalidArgumentException('Section not found');
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


    protected function applyFilters(Builder $query, array $filters)
    {
        foreach ($filters as $attribute => $value) {
            $column_name = Str::before($attribute, '_');
            $method = 'filterBy' . Str::studly($column_name);

            if (method_exists($this, $method) && isset($value) && $value != null) {
                $query = $this->{$method}($query, $filters);
            }
        }
        return $query;
    }

    protected function filterByPrice($query, $filter_data)
    {
        $price_min = $filter_data['price_min'] ?? 0;
        $price_max = $filter_data['price_max'] ?? 10000000;
        $query->whereHas('pricing', function ($query) use ($price_min, $price_max) {
            return $query->whereBetween('value', [$price_min, $price_max]);
        });
        return $query;
    }
}
