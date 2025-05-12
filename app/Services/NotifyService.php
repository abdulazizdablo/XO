<?php

namespace App\Services;

use App\Models\Notify;
use App\Models\User;
use App\Models\StockLevel;
use App\Http\Resources\ProductCollection;
use InvalidArgumentException;

class NotifyService
{
    public function getAllNotifys()
    {
        $notifies = Notify::paginate();

        if (!$notifies) {
            throw new InvalidArgumentException('There Is No Notifys Available');
        }

        return $notifies;
    }

    public function getAllNotifysbyUserId(int $user_id)
    {
        $notifies = User::findOrFail($user_id)
            ->favorates_product_variations()
            ->product()
            ->get();


        $products =  $notifies->map(function ($item) {

            return Product::findOrFail($item->product_id);
        });

        if (!$notifies) {
            throw new InvalidArgumentException('There Is No Notifys Available');
        }

        return    ProductCollection::make($products);
    }

    public function getNotify(int $notify_id): Notify
    {
        $notify = Notify::findOrFail($notify_id);

        return $notify;
    }

    public function createNotify($user_id, $product_variation_id) //si
    {
        $user = User::findOrFail($user_id)->load('notifies');
        $product_stock_level = StockLevel::where('product_variation_id', $product_variation_id)->get();

        if (!$product_stock_level) {
            return response()->error(['message' => 'There is no quantites in stock'], 204);
        } else if (($product_stock_level) && ($product_stock_level->sum('current_stock_level') != 0)) {

            return ['is_notified' => false, 'message' => 'This product can not be notified '];
        }

        if (!$user->notifies()->where('product_variation_id', $product_variation_id)->exists()) {
            $user->notifies()->attach($product_variation_id);
            return ['is_notified' => true, 'message' => 'Product has been notified to this size'];
        } else {
            $user->notifies()->detach($product_variation_id);
            return  ['is_notified' => false, 'message' => 'Product notify for this size has been removed'];
        }
    }

    public function updateNotify(array $data, $notify_id, $user_id, $product_variation_id): Notify
    {
        $notify = Notify::findOrFail($notify_id);
        $data['user_id'] = $user_id;
        $data['product_variation_id'] = $product_variation_id;

        if (!$notify) {
            throw new InvalidArgumentException('There Is No Notifys Available');
        }

        $notify->update($data);

        return $notify;
    }

    public function show(int $notify_id): Notify
    {
        $notify = Notify::findOrFail($notify_id);

        return $notify;
    }

    public function delete(int $notify_id): void
    {
        $notify = Notify::findOrFail($notify_id);

        $notify->delete();
    }

    public function forceDelete(int $notify_id): void
    {
        $notify = Notify::findOrFail($notify_id);

        $notify->forceDelete();
    }
}
