<?php

namespace App\Services;

use App\Models\Transfer;
use InvalidArgumentException;

class TransferService
{
    public function getAllTransfers()
    {
        $transfers = Transfer::paginate();

        if (!$transfers) {
            throw new InvalidArgumentException('There Is No Transfers Available');
        }

        return $transfers;
    }

    public function getTransfer($transfer_id) : Transfer
    {
        $transfer = Transfer::findOrFail($transfer_id);

        return $transfer;
    }

    public function createTransfer(array $data, $from_location_id, $to_location_id): Transfer
    {
        $transfer = Transfer::create([
            'from_location_id' => $from_location_id,
            'to_location_id' => $to_location_id,
            'deliver_date' => $data['deliver_date'],
            'quantity' => $data['quantity'],
        ]);

        if(!$transfer){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $transfer;
    }

    public function updateTransfer(array $data, $transfer_id, $from_location_id, $to_location_id): Transfer
    {
        $transfer = Transfer::findOrFail($transfer_id);

        $transfer->update([
            'from_location_id' => $from_location_id,
            'to_location_id' => $to_location_id,
            'deliver_date' => $data['deliver_date'],
            'quantity' => $data['quantity'],
        ]);

        return $transfer;
    }

    public function show($transfer_id): Transfer
    {
        $transfer = Transfer::findOrFail($transfer_id);

        return $transfer;
    }

    public function delete(int $transfer_id) : void
    {
        $transfer = Transfer::findOrFail($transfer_id);

        $transfer->delete();
    }

    public function forceDelete(int $transfer_id) : void
    {
        $transfer = Transfer::findOrFail($transfer_id);

        $transfer->forceDelete();
    }
}
