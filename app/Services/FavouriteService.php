<?php

namespace App\Services;

use App\Models\Favourite;
use App\Models\User;
use InvalidArgumentException;

class FavouriteService
{
    public function getAllFavourites()
    {
        $favourites = Favourite::paginate();

        if (!$favourites) {
            throw new InvalidArgumentException('There Is No Favourites Available');
        }

        return $favourites;
    }

    public function getAllFavouritesbyUserId($user_id)
    {
        $favourites = User::findOrFail($user_id)
            ->favorates_product_variations()
            ->product()
            ->get();

        if (!$favourites) {
            throw new InvalidArgumentException('There Is No Favourites Available');
        }

        return $favourites;
    }

    public function getFavourite($favourite_id): Favourite
    {
        $favourite = Favourite::findOrFail($favourite_id);

        return $favourite;
    }

    public function createFavourite(int $user_id, int $product_id)//si
    {
        $user = User::findOrFail($user_id);

        if ($user->favourites_products()->where('product_id', $product_id)->exists()) {
            $user->favourites_products()->detach($product_id);
            return 'Product is removed from favourite';
        } else {
            $user->favourites_products()->attach($product_id);
            return 'Product is added to favourite';
        }

    }

    public function updateFavourite(array $data, $favourite_id,  int $user_id,  int $product_variation_id): Favourite
    {
        $favourite = Favourite::findOrFail($favourite_id);
        $data['user_id'] = $user_id;
        $data['product_variation_id'] = $product_variation_id;

        if (!$favourite) {
            throw new InvalidArgumentException('There Is No Favourites Available');
        }

        $favourite->update($data);

        return $favourite;
    }

    public function show(int $favourite_id): Favourite
    {
        $favourite = Favourite::findOrFail($favourite_id);

        return $favourite;
    }

    public function delete(int $favourite_id): void
    {
        $favourite = Favourite::findOrFail($favourite_id);


        $favourite->delete();
    }

    public function forceDelete(int $favourite_id): void
    {
        $favourite = Favourite::findOrFail($favourite_id);


        $favourite->forceDelete();
    }
}
