<?php

namespace App\Services;

use App\Models\Offer;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class OfferService
{
    public function getAllOffers()
    {
        $offers = Offer::valid()->paginate(8);

        if (!$offers) {
            throw new InvalidArgumentException('There Is No Offers Available');
        }

        return $offers;
    }

    public function getOffer($offer_id) : Offer
    {
        $offer = Offer::find($offer_id);

        if (!$offer) {
            throw new InvalidArgumentException('Offer not found');
        }

        return $offer;
    }

    public function createOffer(array $data): Offer
    {
        $offer = Offer::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'valid' => $data['valid'],
            'description' => $data['description'],
            'expired_at' => $data['expired_at'],
        ]);

        if(!$offer){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $offer;
    }

    public function updateOffer(array $data, $offer_id): Offer
    {
        $offer = Offer::find($offer_id);

        if(!$offer){
            throw new InvalidArgumentException('There Is No Offers Available');
        }

        $offer->update($data);

        return $offer;
    }

    public function show($offer_id): Offer
    {
        $offer = Offer::find($offer_id);

        if(!$offer){
            throw new InvalidArgumentException('Offer not found');
        }

        return $offer;
    }

    public function delete(int $offer_id) : void
    {
        $offer = Offer::find($offer_id);

        if (!$offer) {
            throw new InvalidArgumentException('Offer not found');
        }

        $offer->delete();
    }

    public function forceDelete(int $offer_id) : void
    {
        $offer = Offer::find($offer_id);

        if (!$offer) {
            throw new InvalidArgumentException('Offer not found');
        }

        $offer->forceDelete();
    }
}
