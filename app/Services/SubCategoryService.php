<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\PhotoTrait;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Exception;
use App\Traits\TranslateFields;


class SubCategoryService
{
    use PhotoTrait, TranslateFields;

    public function getAllSubCategories($category_id=null)
    {
        $subCategories = SubCategory::when($category_id!=null, function ($query) use ($category_id) {
            $query->where('category_id', $category_id);})->get();

        $sub_category_fields = [
            'id',
            'name',
            'slug',
            'category_id',
            'name',
            'photo_url',
            'thumbnail',
            'valid'
        ];

        $subCategories= $this->getTranslatedFields($subCategories, $sub_category_fields);
        if (!$subCategories) {
            throw new InvalidArgumentException('There Is No Sub Categories Available');
        }

        return $subCategories;
    }

    public function assignProductToSub($sub_id, $product_id)
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

    public function getSubCategory(int $subCategory_id): SubCategory
    {
        $subCategory = SubCategory::findOrFail($subCategory_id);

        return $subCategory;
    }

    public function createSubCategory($data, $category_id)
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

        $photo_path = $this->saveImage($data['image'], 'photo', 'sub_categories');
        // $thumbnail = $this->saveImage($data['image'], 'photo', 'sub_categories');
        $thumbnail = "abcd";

        $subCategory = SubCategory::create([
            'category_id' => $category->id,
            'name' => $names,
            'slug' => Str::slug($data['name_en']),
            'photo_url' => $photo_path,
            'thumbnail' => $thumbnail,
        ]);

        if (!$subCategory) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $subCategory;
    }

    public function updateSubCategory(array $data, int $subCategory_id): SubCategory
    {
        $subCategory = SubCategory::find($subCategory_id);
        $subCategory->update([
            'name' => $data['name'],
        ]);

        if (!$subCategory) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $subCategory;
    }

    public function show(int $subCategory_id): SubCategory
    {
        $subCategory = SubCategory::findOrFail($subCategory_id);

        return $subCategory;
    }

    public function delete(int $subCategory_id): void
    {
        $subCategory = SubCategory::findOrFail($subCategory_id);
        $subCategory->delete();
    }

    public function forceDelete(int $subCategory_id): void
    {
        $subCategory = SubCategory::findOrFail($subCategory_id);

        $subCategory->forceDelete();
    }



    public function getProductForSubCategory($sub_category_id)
    {

        $sub_categorys = Product::where('sub_category_id', $sub_category_id)->get();


        if (!$sub_categorys) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $sub_categorys;
    }
	
	
	public function getSubCategoryBySlug(string $slug){
	
	return $sub_category = SubCategory::where('slug->en',$slug)->firstOrFail();
		
		
		
	
	}
}
