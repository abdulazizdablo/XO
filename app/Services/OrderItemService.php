<?php

namespace App\Services;

use App\Models\OrderItem;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderItemService
{
    public function getAllOrderItems($order_id)
    {
        $order_items = OrderItem::where('order_id',$order_id)->paginate();

        if (!$order_items) {
            throw new InvalidArgumentException('There Is No OrderItems Available');
        }
        return $order_items;
    }

    public function getOrderItem($order_item_id) : OrderItem
    {
        $order_items = OrderItem::findOrFail($order_item_id);

      

        return $order_items;
    }
    public function createOrderItem($order_id ,$product_variation_id ,$quantity,$price ): OrderItem
    {
        $order_item = OrderItem::create([
            'order_id' => $order_id,
            'product_variation_id' => $product_variation_id,
            'return_order_id' => $return_order_id ?? null,
            'quantity' => $quantity,
            'price' => $price,
        ]);

        if(!$order_item){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $order_item;
    }

    public function updateOrderItem(array $data, $order_item_id): OrderItem
    {
        $order_item = OrderItem::find($order_item_id);

        if(!$order_item){
            throw new NotFoundHttpException('There Is No OrderItem Available');
        }

        $order_item->update($data);

        return $order_item;
    }

    public function show($order_item_id): OrderItem
    {
        $order_item = OrderItem::findOrFail($order_item_id);

       
        return $order_item;
    }

    public function delete(int $order_item_id) : void
    {
        $order_item = OrderItem::findOrFail($order_item_id);

       

        $order_item->delete();
    }

    public function forceDelete(int $order_item_id) : void
    {
        $order_item = OrderItem::findOrFail($order_item_id);

        

        $order_item->forceDelete();
    }
}
