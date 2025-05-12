<?php

namespace App\Exports;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductVariationsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithMapping
{
    use Exportable;

    // a place to store the product dependency
    // private $product;

    // // use constructor to handle dependency injection
    // public function __construct(Collection $product)
    // {
    //     $this->product = $product;
    // }

    protected $languages = ['en', 'ar'];

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // return Product::with('variations')->get();
        return ProductVariation::with('product', 'product.subCategory')->get();
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        $headings =
            [
                'Product Variation Id',
                'SKU Code',
                'Product ID',
				'Color',
				'Size'
            ];

        foreach ($this->languages as $locale) {
            $headings[] = "Category Name ($locale)";
            $headings[] = "Product Name ($locale)";
            $headings[] = "Description ($locale)";
            $headings[] = "Material ($locale)";
            $headings[] = "Composition ($locale)";
           // $headings[] = "Fabric ($locale)";
            $headings[] = "Care instructions ($locale)";
            $headings[] = "Fit ($locale)";
            $headings[] = "Style ($locale)";
            $headings[] = "Season ($locale)";
        }

        $headings[] = 'Product Variation Count';

        return $headings;
    }

    public function title(): string
    {
        return 'Product Variations - منتجات';
    }

    public function map($productVariation): array
    {
        // dd("dasda");
        $export_data = [
            $productVariation->id,
            $productVariation->sku_code,
            $productVariation->product_id,
            $productVariation->color->getTranslation('name','en'),
            $productVariation->size->getTranslation('value','en'),
        ];

        foreach ($this->languages as $locale) {
            $category_name = $productVariation->product->subCategory->getTranslation('name', $locale);
            $name = $productVariation->product->getTranslation('name', $locale);
            $description = $productVariation->product->getTranslation('description', $locale);
            $material = $productVariation->product->getTranslation('material', $locale);
            $composition = $productVariation->product->getTranslation('composition', $locale);
           // $fabric = $product->getTranslation('fabric', $locale);
            $care_instructions = $productVariation->product->getTranslation('care_instructions', $locale);
            $fit = $productVariation->product->getTranslation('fit', $locale);
            $style = $productVariation->product->getTranslation('style', $locale);
            $season = $productVariation->product->getTranslation('season', $locale);
            $export_data[] = $category_name;
            $export_data[] = $name;
            $export_data[] = $description;
            $export_data[] = $material;
            $export_data[] = $composition;
           // $export_data[] = $fabric;
            $export_data[] = $care_instructions;
            $export_data[] = $fit;
            $export_data[] = $style;
            $export_data[] = $season;
        }
        array_push($export_data , $productVariation->count());

        // dd($export_data);
        return $export_data;
    }
}
