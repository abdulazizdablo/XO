Schema::create('inventories', function (Blueprint $table) {
$table->id();
$table->json('name');
$table->json('address');
$table->json('country');
$table->json('city');
$table->softDeletes();
$table->timestamps();
});


Schema::create('stock_levels', function (Blueprint $table) {
$table->id();
$table->foreignId('product_variation_id')->constrained('product_variations')->onDelete('cascade')->onUpdate('cascade');
$table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade')->onUpdate('cascade');

$table->string('name');
$table->integer('min_stock_level');
$table->integer('max_stock_level');
$table->integer('current_stock_level');
$table->dateTime('target_date');
$table->integer('sold_quantity');
$table->string('status');
$table->softDeletes();
$table->timestamps();
});

Schema::create('product_variations', function (Blueprint $table) {
$table->id();
$table->foreignId('product_id')->constrained('products')->onDelete('cascade')->onUpdate('cascade');
$table->foreignId('variation_id')->constrained('variations')->onDelete('cascade')->onUpdate('cascade');
$table->foreignId('color_id')->constrained('colors')->onDelete('cascade')->onUpdate('cascade');
$table->foreignId('size_id')->constrained('sizes')->onDelete('cascade')->onUpdate('cascade');

$table->string('sku_code');
$table->boolean('visible')->default(false);
$table->softDeletes();
$table->timestamps();

});


Schema::create('products', function (Blueprint $table) {
$table->id();
$table->nullableMorphs('promotionable');
$table->foreignId('sub_category_id')->constrained('sub_categories')->onDelete('cascade')->onUpdate('cascade');

$table->string('item_no');
$table->boolean('available')->default(0);
$table->json('name');
$table->text('description');
$table->json('material');
$table->json('composition');
$table->json('fabric');
$table->json('care_instructions');
$table->json('fit');
$table->json('style');
$table->json('season');

$table->softDeletes();
$table->timestamps();

});
