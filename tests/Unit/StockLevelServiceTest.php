<?php

use App\Models\StockLevel;
use App\Services\StockLevelService;
use Illuminate\Foundation\Testing\RefreshDatabase;



it('filters inventory products by status', function () {
    $inventory = createInventoryForTests();

    $lowStock = createStockLevelForTests([
        'inventory' => $inventory,
        'product_variation' => createProductVariationForTests(['sku_code' => 'SKU-LOW-FILTER']),
        'status' => 'low-inventory',
    ]);

    createStockLevelForTests([
        'inventory' => $inventory,
        'product_variation' => createProductVariationForTests(['sku_code' => 'SKU-FAST-FILTER']),
        'status' => 'fast-movement',
    ]);

    $service = new StockLevelService();

    $results = $service->getInventoryProducts([], ['status' => ['low-inventory']], $inventory->id, null);

    expect($results->total())->toBe(1)
        ->and($results->items()[0]->id)->toBe($lowStock->id);
});

it('searches inventory products by SKU', function () {
    $inventory = createInventoryForTests();
    $sku = 'SKU-SEARCH-01';

    $matching = createStockLevelForTests([
        'inventory' => $inventory,
        'product_variation' => createProductVariationForTests(['sku_code' => $sku]),
        'status' => 'slow-movement',
    ]);

    createStockLevelForTests([
        'inventory' => $inventory,
        'product_variation' => createProductVariationForTests(['sku_code' => 'SKU-NO-MATCH']),
    ]);

    $service = new StockLevelService();

    $results = $service->getInventoryProducts([], ['search' => 'SEARCH-01'], $inventory->id, null);

    expect($results->total())->toBe(1)
        ->and($results->items()[0]->product_variation_id)->toBe($matching->product_variation_id);
});

it('updates stock level attributes and returns the model', function () {
    $stockLevel = createStockLevelForTests([
        'name' => 'Initial',
        'current_stock_level' => 10,
    ]);

    $service = new StockLevelService();

    $updated = $service->updateStockLevel([
        'name' => 'Updated Name',
        'current_stock_level' => 25,
    ], $stockLevel->id);

    expect($updated->name)->toBe('Updated Name')
        ->and($updated->current_stock_level)->toBe(25)
        ->and(StockLevel::find($stockLevel->id)->current_stock_level)->toBe(25);
});

it('throws an exception when updating a missing stock level', function () {
    $service = new StockLevelService();

    $service->updateStockLevel(['name' => 'Missing'], 999);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
