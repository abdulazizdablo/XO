<?php

namespace App\Services;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Arr;
use InvalidArgumentException;
//use App\Traits\CloudinaryTrait;
use App\Traits\PhotoTrait;
use App\Traits\TranslateFields;
use Exception;
use Illuminate\Support\Facades\File;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Inventory;

class CategoryService
{
    use PhotoTrait, TranslateFields;

    public function getAllCategories()
    {
        $categories = Category::valid()
            ->paginate();

        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        $category_fields = [
            'id',
            'name',
            'photo_url',
        ];

        return $this->getTranslatedFields($categories, $category_fields);
    }

    public function getAllAvailableCategories($per_page, $page)
    {
        $categories = Category::valid()
            ->paginate($per_page);

        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        $category_fields = [
            'id',
            'name',
            'photo_url',
        ];

        return $this->getPaginatedTranslatedFields($categories, $category_fields);
    }

    public function getCategory(int $category_id)
    {
        $category = Category::findOrFail($category_id);

        $category_fields = [
            'id',
            'name',
            'photo_url',
        ];

        return $category->getFields($category_fields);
    }

    public function createCategory($data, int $section_id): Category
    {

        $photo_path = $this->saveImage($data['image'], 'category', 'categories');

        $category = Category::create([
            'section_id' => $section_id,
            'name' => ["en" => $data['name_en'], "ar" => $data['name_ar']],
            'photo_url' => $photo_path,
            'thumbnail' => 'dasd',
        ]);

        if (!$category) {
            throw new InvalidArgumentException('Category not found');
        }

        return $category;
    }
    
    
    public function getcImageUrl($publicId){
        
        return $this->getImageUrl($publicId);
    }
    

    public function updateCategory($data, $category_id, $section_id, $has_photo)
    {
        try {
            $nameKeys = Arr::where(array_keys($data), function ($key) {
                return strpos($key, 'name_') === 0;
            });

            $names = [];
            foreach ($nameKeys as $key) {
                $locale = str_replace('name_', '', $key);
                $names[$locale] = $data[$key];
            }

            $category = Category::findOrFail($category_id);

            if ($has_photo == true) {
                $destination = $category->photo_url;
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $photo_url = $this->saveImage($data['image'], 'photo', 'categories/products');
            } else {
                $photo_url = $category->photo_url;
            }

            $category->update([
                'section_id' => $section_id,
                'name' => $names,
                'photo_url' => $photo_url,
                'thumbnail' => 'dasd',
            ]);

            $category->update($data);

            return $category;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function show(int $category_id): Category
    {
        $category = Category::findOrFail($category_id);

        return $category;
    }

    public function delete(int $category_id): void
    {
        $category = Category::findOrFail($category_id);

        $category->delete();
    }

    public function forceDelete(int $category_id): void
    {
        $category = Category::findOrFail($category_id);

        $category->forceDelete();
    }

    public function getSubDataForCategory(int $category_id)
    {
        try {
            $category = Category::withCount('subCategories')
                ->findOrFail($category_id)
                ->load('section:id,name');

            $sub_categories = $category->subCategories()->get();

            if (!$sub_categories) {
                throw new InvalidArgumentException('There Is No sub Categories Available');
            }

            return $sub_categories;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
        // return $this->getTranslatedFields($sub_categories, $sub_category_fields, $page, $per_page);
    }

    public function getSubForCategory(int $category_id)
    {
        try {
            $category = Category::withCount('subCategories')
                ->findOrFail($category_id)
                ->load('section:id,name');

            $sub_categories = $category->subCategories()
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->paginate(8);

            if (!$sub_categories) {
                throw new InvalidArgumentException('There Is No sub Categories Available');
            }

            return [
                'category' => $category->loadCount('subCategories'),
                'sub_categories' => $sub_categories
            ];
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
        // return $this->getTranslatedFields($sub_categories, $sub_category_fields, $page, $per_page);
    }


    public function getCategoryCounts($inventory_id ,int $product_id)
    {
      
        if ($inventory_id == null) {

            $product = Product::where('id', $product_id)
            ->with(['order_items' => function ($query) {
                $query->whereHas('order', function ($query) {
                    $query->where('status', 'closed');
                });
            }])
            ->withCount(['order_items' => function ($query) {
                $query->whereHas('order', function ($query) {
                    $query->where('status', 'closed');
                });
            }]);
            
            $pieces_sold = $product->pluck('order_items_count');
            $product = $product->first();
            $profits = 0;
            foreach ($product->order_items as $orderItem) {
                $profits += $orderItem->price * $orderItem->quantity;
            }
           
                $num_stock = Product::where('id' ,$product_id)->withCount(['product_variations' => function ($query) {
                    $query->whereHas('stock_levels', function ($query) {
                       
                    });
                }])->pluck('product_variations_count');


           
        } elseif ($inventory_id != null) {
            $product = Product::where('id', $product_id)
            ->with(['order_items' => function ($query) use ($inventory_id)  {
                $query->whereHas('order', function ($query) use ($inventory_id)  {
                    $query->where('status', 'closed')
                    ->where('inventory_id', $inventory_id);
                });
            }])
            ->withCount(['order_items' => function ($query) use ($inventory_id)  {
                $query->whereHas('order', function ($query)  use ($inventory_id) {
                    $query->where('status', 'closed')
                    ->where('inventory_id', $inventory_id);
                });
            }]);
           

            $pieces_sold = $product->pluck('order_items_count');
            $product = $product->first();
            $profits = 0;
            foreach ($product->order_items as $orderItem) {
                $profits += $orderItem->price * $orderItem->quantity;
            }
            $num_stock = Product::where('id' ,$product_id)->withCount(['product_variations' => function ($query)  use ($inventory_id){
                $query->whereHas('stock_levels', function ($query) use ($inventory_id) {
                    $query->where('inventory_id', $inventory_id);
                });
            }])->pluck('product_variations_count');
        }

        $inventory = Inventory::count();

    

        return ['marketPlaces' => $inventory,
        'pieces_sold' => $pieces_sold,
        'profits' => $profits,
        'num_stock' => $num_stock];
    }
	
	public function getCategoriesBySlug(string $slug){
	
	return $category = Category::where('slug',$slug)->firstOrFail();
		
		
		
	
	}
	
}
