<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;use Illuminate\Support\Facades\Lang;


class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'delivery_date' => $this->delivery_date,
            'created_at' => $this->created_at,
            'total_price' => $this->total_price,
            'total_quantity' => $this->total_quantity,
            'paid' => $this->paid,
            'status' => Lang::get("enums.status.$this->status"),
            'payment_method' => $this->payment_method,
            'need_packaging' => $this->need_packaging,
            'vat_fee' => $this->vat_fee,
            'shipping_fee' => $this->shipping_fee,
            'qr_code' => $this->qr_code,
            'employee' => $this->whenLoaded('employee'),
            'address' => $this->whenLoaded('address'),
            'order_items' => OrderItemResource::collection($this->whenLoaded('order_items')),
        ];
    }
}
