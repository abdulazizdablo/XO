<?php

use App\Services\StockMovementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;



it('retrieves stock movements filtered by shipment name', function () {
    createStockMovementForTests([
        'shipment_name' => 'Alpha Shipment',
        'status' => 'open',
    ]);

    $matching = createStockMovementForTests([
        'shipment_name' => 'Beta Movement',
        'status' => 'closed',
    ]);

    $service = new StockMovementService();

    $results = $service->getAllStockMovements([
        'search' => 'Beta',
    ]);

    expect($results->total())->toBe(1)
        ->and($results->items()[0]->id)->toBe($matching->id);
});

it('filters stock movements by status and shipped date range', function () {
    $now = Carbon::now();

    $inside = createStockMovementForTests([
        'shipment_name' => 'Inside Range',
        'status' => 'closed',
        'shipped_date' => $now->copy()->subDays(2),
    ]);

    createStockMovementForTests([
        'shipment_name' => 'Outside Range',
        'status' => 'closed',
        'shipped_date' => $now->copy()->subMonths(3),
    ]);

    $service = new StockMovementService();

    $results = $service->getAllStockMovements([
        'status' => 'closed',
        'shipping_min' => $now->copy()->subWeek()->format('Y-m-d'),
        'shipping_max' => $now->format('Y-m-d'),
    ]);

    expect($results->total())->toBe(1)
        ->and($results->items()[0]->id)->toBe($inside->id);
});

it('loads inventory relations when retrieving a single stock movement', function () {
    $movement = createStockMovementForTests([
        'shipment_name' => 'Load Relations',
    ]);

    $service = new StockMovementService();

    $fetched = $service->getStockMovement($movement->id);

    expect($fetched->source_inventory)->not->toBeNull()
        ->and($fetched->destination_inventory)->not->toBeNull();
});

it('throws when deleting a missing stock movement', function () {
    $service = new StockMovementService();

    $service->delete(999);
})->throws(\InvalidArgumentException::class);

it('throws when force deleting a missing stock movement', function () {
    $service = new StockMovementService();

    $service->forceDelete(999);
})->throws(\InvalidArgumentException::class);

it('summarizes counts across stock movement statuses', function () {
    createStockMovementForTests([
        'status' => 'open',
        'expected' => 6,
        'received' => 0,
        'shipment_name' => 'Count Open',
    ]);

    createStockMovementForTests([
        'status' => 'closed',
        'expected' => 4,
        'received' => 3,
        'shipment_name' => 'Count Closed',
    ]);

    request()->merge(['date_scope' => 'Today']);

    $service = new StockMovementService();

    $counts = $service->getCounts();

    expect($counts['open'])->toBe(1)
        ->and($counts['closed'])->toBe(1)
        ->and($counts['shipments'])->toBe(2)
        ->and($counts['expected'])->toBe(7);
});