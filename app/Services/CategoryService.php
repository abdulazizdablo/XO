<?php

namespace App\Services;

use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use App\Traits\CloudinaryTrait;
use App\Traits\TranslateFields;
use Exception;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    use CloudinaryTrait, TranslateFields;

    public function createCategory($data, int $section_id): Category //si
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

    public function updateCategory($data, $category_id, $section_id, $has_photo) //si
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

    public function delete(int $category_id): void //si
    {
		$category = Category::findOrFail($category_id);
		$category->subCategories()->delete();
		$category->delete();		
    }

    public function getSubDataForCategory(int $category_id) //si
    {
        try {
            $category = Category::withCount('subCategories')
                ->findOrFail($category_id)
                ->load('section:id,name');

            $sub_categories = $category->subCategories()->withCount('products')
                ->orderBy('products_count', 'desc')
                ->get();

            if (!$sub_categories) {
                throw new InvalidArgumentException('There Is No sub Categories Available');
            }

            return $sub_categories;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function getSubForCategory(int $category_id) //si
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
    }


    public function getCategoryCounts($inventory_id, int $product_id) //si
    {
        if ($inventory_id == null) {
            /*$product = Product::where('id', $product_id)
                ->with(['order_items' => function ($query) {
                    $query->whereHas('order', function ($query) {
                        $query->whereIn('status', ['closed','received']);
                    });
                }])
                ->withCount(['order_items' => function ($query) {
                    $query->whereHas('order', function ($query) {
                        $query->whereIn('status', ['closed','received']);
                    });
                }]);*/
			//return $product->get();
			$orderItems = OrderItem::whereNull('status')->wherehas('product_variation.product', function($query) use($product_id){
                    $query->where('id',$product_id);
                });
            //$pieces_sold = $product->pluck('order_items_count');
            $pieces_sold = OrderItem::whereNull('status')->wherehas('product_variation.product', function($query) use($product_id){
                    $query->where('id',$product_id);
                })->count();
			$product = Product::find($product_id);
            $profits = 0;
            foreach ($orderItems->get() as $orderItem) {
                $profits += $orderItem->price * $orderItem->quantity;
            }
            $num_stock = Product::where('id', $product_id)->withCount(['product_variations' => function ($query) {
                $query->whereHas('stock_levels', function ($query) {});
            }])->pluck('product_variations_count');
        } elseif ($inventory_id != null) {
            /*$product = Product::where('id', $product_id)
                ->with(['order_items' => function ($query) use ($inventory_id) {
                    $query->whereHas('order', function ($query) use ($inventory_id) {
                        $query->where('status', 'closed')
                            ->where('inventory_id', $inventory_id);
                    });
                }])
                ->withCount(['order_items' => function ($query) use ($inventory_id) {
                    $query->whereHas('order', function ($query)  use ($inventory_id) {
                        $query->where('status', 'closed')
                            ->where('inventory_id', $inventory_id);
                    });
                }]);*/
			$orderItems = OrderItem::wherehas('product_variation.product', function($query) use($product_id){
                    $query->where('id',$product_id);
                });
            //$pieces_sold = $product->pluck('order_items_count');
            $pieces_sold = OrderItem::whereNull('status')->wherehas('product_variation.product', function($query) use($product_id){
                    $query->where('id',$product_id);
                })->count();
            //$pieces_sold = $product->pluck('order_items_count');
			$product = Product::find($product_id);
            $profits = 0;

            foreach ($orderItems->get() as $orderItem) {
                $profits += $orderItem->price * $orderItem->quantity;
            }

            $num_stock = Product::where('id', $product_id)->withCount(['product_variations' => function ($query)  use ($inventory_id) {
                $query->whereHas('stock_levels', function ($query) use ($inventory_id) {
                    $query->where('inventory_id', $inventory_id);
                });
            }])->pluck('product_variations_count');
        }
        $inventory = Inventory::count();
        return [
            'marketPlaces' => $inventory,
            'pieces_sold' => $pieces_sold,
            'profits' => $profits,
            'num_stock' => $num_stock
        ];
    }

    public function getCategoriesBySlug(string $slug)
    { //si
        return $category = Category::where('slug', $slug)->firstOrFail();
    }
}
