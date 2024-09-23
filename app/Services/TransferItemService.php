<?php

namespace App\Services;

use App\Models\TransferItem;
use InvalidArgumentException;

class TransferItemService
{
    public function getAllTransferItems()
    {
        $transferItems = TransferItem::paginate();

        if (!$transferItems) {
            throw new InvalidArgumentException('There Is No TransferItems Available');
        }

        return $transferItems;
    }

    public function getTransferItem(int $transferItem_id) : TransferItem
    {
        $transferItem = TransferItem::findOrFail($transferItem_id);

        return $transferItem;
    }

    public function createTransferItem(array $data, int $transfer_id): TransferItem
    {
        $transferItem = TransferItem::create([
            'transfer_id' => $transfer_id,
            'quantity' => $data['quantity'],
        ]);

        if(!$transferItem){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $transferItem;
    }

    public function updateTransferItem(array $data, int $transferItem_id, int $transfer_id): TransferItem
    {
        $transferItem = TransferItem::findOrFail($transferItem_id);
        if(!$transferItem){
            throw new InvalidArgumentException('There Is No Transfer Items Available');
        }
        $transferItem->update([
            'transfer_id' => $transfer_id,
            'quantity' => $data['quantity'],
        ]);

        return $transferItem;
    }

    public function show(int $transferItem_id): TransferItem
    {
        $transferItem = TransferItem::findOrFail($transferItem_id);

        return $transferItem;
    }

    public function delete(int $transferItem_id) : void
    {
        $transferItem = TransferItem::findOrFail($transferItem_id);

        $transferItem->delete();
    }

    public function forceDelete(int $transferItem_id) : void
    {
        $transferItem = TransferItem::findOrFail($transferItem_id);

        $transferItem->forceDelete();
    }
}
