<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithMapping
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
        return Product::with('product_variations', 'subCategory')->get();
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
                'Product Id',
                'Item NO',
                'Category ID',
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
        return 'Products - منتجات';
    }

    public function map($product): array
    {
        // dd("dasda");
        $export_data = [
            $product->id,
            $product->item_no,
            $product->subCategory->id,
        ];

        foreach ($this->languages as $locale) {
            $category_name = $product->subCategory->getTranslation('name', $locale);
            $name = $product->getTranslation('name', $locale);
            $description = $product->getTranslation('description', $locale);
            $material = $product->getTranslation('material', $locale);
            $composition = $product->getTranslation('composition', $locale);
           // $fabric = $product->getTranslation('fabric', $locale);
            $care_instructions = $product->getTranslation('care_instructions', $locale);
            $fit = $product->getTranslation('fit', $locale);
            $style = $product->getTranslation('style', $locale);
            $season = $product->getTranslation('season', $locale);
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
        array_push($export_data , $product->product_variations->count());

        // dd($export_data);
        return $export_data;
    }
}
