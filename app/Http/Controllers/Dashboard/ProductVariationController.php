<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use App\Models\Size;
use App\Models\Color;
use App\Exports\ProductVariationsExport;
use Maatwebsite\Excel\Facades\Excel;


class ProductVariationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductVariation  $productVariation
     * @return \Illuminate\Http\Response
     */
    public function show(ProductVariation $productVariation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductVariation  $productVariation
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductVariation $productVariation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductVariation  $productVariation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)


    {

        $product_variation_data = $request->product_variations;
        $productVariation = ProductVariation::findOrFail($product_variation_data['product_variation_id']);

        $color = Color::where('name->en', $product_variation_data['color'])->orWhere('name->ar', $product_variation_data['color'])->first();
        $size = Size::where('value->en', $product_variation_data['size'])->orWhere('value->ar', $product_variation_data['size'])->first();

        $productVariation->size()->associate($size->id);

        $productVariation->color()->associate($color->id);
        $productVariation->group_id = $product_variation_data['group_id'];
        $productVariation->group_id = $product_variation_data['sku_code'];

        $productVariation->save();

        return $productVariation;
    }
	
	public function export()
    {
        return Excel::download(new ProductVariationsExport, 'product_variations.xlsx');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductVariation  $productVariation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductVariation $productVariation)
    {
        //
    }
}
