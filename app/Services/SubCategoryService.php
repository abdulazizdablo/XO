<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Traits\CloudinaryTrait;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Traits\TranslateFields;


class SubCategoryService
{
    use CloudinaryTrait, TranslateFields;

    public function getAllSubCategories($category_id = null) //si
    {
        $subCategories = SubCategory::when($category_id != null, function ($query) use ($category_id) {
            $query->where('category_id', $category_id);
        })->get();

        $sub_category_fields = [
            'id',
            'name',
            'slug',
            'category_id',
            'name',
            'valid'
        ];

        $subCategories = $this->getTranslatedFields($subCategories, $sub_category_fields);
        if (!$subCategories) {
            throw new InvalidArgumentException('There Is No Sub Categories Available');
        }

        return $subCategories;
    }

    public function assignProductToSub($sub_id, $product_id) //si
    {
        $product = Product::find($product_id);

        if (!$product) {
            throw new InvalidArgumentException('There Is No Sub Product Available');
        }

        $sub_category = SubCategory::find($sub_id);

        if (!$sub_category) {
            throw new InvalidArgumentException('There Is No Sub Categories Available');
        }

        $product->update(['sub_category_id' => $sub_id]);

        return true;
    }

    public function createSubCategory($data, $category_id) //si
    {
        $nameKeys = Arr::where(array_keys($data), function ($key) {
            return strpos($key, 'name_') === 0;
        });
        $names = [];
        foreach ($nameKeys as $key) {
            $locale = str_replace('name_', '', $key);
            $names[$locale] = $data[$key];
        }
        $category = Category::findOrFail($category_id);
        $subCategory = SubCategory::create([
            'category_id' => $category->id,
            'name' => $names,
            'slug' => Str::slug($data['name_en']),
        ]);

        if (!$subCategory) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $subCategory;
    }

    public function updateSubCategory(array $data, int $subCategory_id) //si
    {
        $nameKeys = Arr::where(array_keys($data), function ($key) {
            return strpos($key, 'name_') === 0;
        });

        $names = [];
        foreach ($nameKeys as $key) {
            $locale = str_replace('name_', '', $key);
            $names[$locale] = $data[$key];
        }

        $subCategory = SubCategory::find($subCategory_id);
        if (empty($names)) {
            $names = $subCategory->name;
        }
        $subCategory->update([
            'name' => $names,
            'category_id' => $data['category_id'] ?? $subCategory->category_id
        ]);

        if (!$subCategory) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $subCategory;
    }

}
