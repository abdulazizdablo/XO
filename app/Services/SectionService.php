<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Section;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use App\Traits\CloudinaryTrait;
use App\Traits\TranslateFields;
use Exception;
use Illuminate\Support\Facades\DB;

class SectionService
{
    use CloudinaryTrait, TranslateFields;

    public function getAllSections()
    {
        $sections = Section::withCount('categories')->get();

        if (!$sections) {
            throw new InvalidArgumentException('There Is No Sections Available');
        }

        return $sections;
    }
    // getSectionsInfo

    public function getSectionSales()
    {
        try {
            $counts = Section::withCount('orders')->get();

            $totalOrders = $counts->sum('orders_count');

            $percentageCounts = $counts->map(function ($section) use ($totalOrders) {
                $percentage = ($section->orders_count / $totalOrders) * 100;
                $section->percentage = $percentage;
                return $section;
            });

            return $percentageCounts;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function comparePopularCategories($categories_id, $section_id = 1, $filter_data, $dateScope)
    {
           try {
            $date = $filter_data['date'] ?? 0;

            $section_id = $section_id ?? 1;

            if(empty($categories_id)){
                $counts = Category::where('section_id', $section_id)
                ->select('id', 'section_id', 'name')
                ->with(
                    'categoryOrders' , function ($query) use ($date) {
                        $query->when($date != null, function($query) use ($date){
                            $query->whereDate('orders.created_at', $date);
                        });
                    }
                )->withCount(['categoryOrders as orders_count'
                // ,'categoryOrders as percentage' => function ($query) {
                //     $query->select(DB::raw('COUNT(*) * 100 / (SELECT COUNT(*) FROM orders)'));}
                    // If you have a condition for orders, you can add it here as well
                    // Example: $query->where('status', 'completed'); 
                ])
                
                ->orderBy('orders_count', 'desc')
                ->take(6)
                ->get();

                  $total_orders_count= $counts->sum('orders_count');

               $counts =$counts->map(function ($category)use($total_orders_count) {
				   
				   if($total_orders_count == 0){
				    $category->percentage = 0;
				   
				   }
				   
				   else {
					                           $category->percentage=$category->orders_count*100/$total_orders_count;

				   }
                    return collect($category->toArray())
                        ->only(['id', 'section_id', 'name', 'orders_count', 'percentage'])
                        ->all();
                });
            }else{
                $counts = Category::where('section_id', $section_id)
                ->whereIn('id', $categories_id)
                ->select('id', 'section_id', 'name')
                ->with(
                    'categoryOrders' , function ($query) use ($date) {
                        $query->when($date != null, function($query) use ($date){
                            $query->whereDate('orders.created_at', $date);
                        });
                    }
                )->withCount(['categoryOrders as orders_count'])
                ->orderBy('percentage', 'desc')
                ->get();
                $total_orders_count= $counts->sum('orders_count');
                $counts =$counts->map(function ($category) use($total_orders_count)  {
						   if($total_orders_count == 0){
				    $category->percentage = 0;
				   
				   }
                        else {
						                    $category->percentage=$category->orders_count*100/$total_orders_count;

						}
					
                    return collect($category->toArray())
                        ->only(['id', 'section_id', 'name', 'orders_count', 'percentage'])
                        ->all();
                });
            }

            return $counts;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function applyFilters($query, array $filters)
    {
        $appliedFilters = [];
        foreach ($filters as $attribute => $value) {
            $column_name = Str::before($attribute, '_');
            $method = 'filterBy' . Str::studly($column_name);
            if (method_exists($this, $method) && isset($value) && !in_array($column_name, $appliedFilters)) {
                $query = $this->{$method}($query, $filters);
                $appliedFilters[] = $column_name;
            }
        }

        return $query;
    }

    public function getSectionsInfo()
    {
        $sections = Section::get();

        if (!$sections) {
            throw new InvalidArgumentException('There Is No Sections Available');
        }
        return $sections;
        // return $sections->getFields($section_fields);

    }

    public function getSection($section_id)
    {
        $section = Section::findOrFail($section_id);

     

        $section_fields = [
            'name',
            'photo_url',

        ];
        return $section->getFields($section_fields);
    }

    public function createSection(array $data): Section
    {
        $nameKeys = Arr::where(array_keys($data), function ($key) {
            return strpos($key, 'name_') === 0;
        });

        $names = [];
        foreach ($nameKeys as $key) {
            $locale = str_replace('name_', '', $key);
            $names[$locale] = $data[$key];
        }

        $photo_path = $this->saveImage($data['image'], 'photo', 'images/sections');
        // $thumbnail_path = $this->saveThumbnail($file,'photo','images/products/thumbnails');

        $section = Section::create([
            'name' => $names,
            'type' => $data['type'],
            'photo_url' => $photo_path,
            'thumbnail' => 'dasd',
        ]);

        // $section->categries()->saveMany([$categories]);

        if (!$section) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $section;
    }

    public function updateSection(array $data, $section_id): Section
    {
        $section = Section::findOrFail($section_id);
        $section->update([
            'name' => $data['name'],
            'type' => $data['type'],
        ]);

    

        return $section;
    }

    public function show($section_id): Section
    {
        $section = Section::find($section_id);

        if (!$section) {
            throw new InvalidArgumentException('Section not found');
        }

        return $section;
    }

    public function delete(int $section_id): void
    {
        $section = Section::find($section_id);

        if (!$section) {
            throw new InvalidArgumentException('Section not found');
        }

        $section->delete();
    }

    public function forceDelete(int $section_id): void
    {
        $section = Section::find($section_id);

        if (!$section) {
            throw new InvalidArgumentException('Section not found');
        }

        $section->forceDelete();
    }

    public function getSectionChart($section_id)
    {
        $counts = Category::withCount('orders')->get();


        return $counts;
    }

    public function getSectionCategories($section_id)
    {
        $categories = Section::where('id', $section_id)
            ->select('id', 'name')
            ->with(['categories' => function ($query) {
                $query->select('id', 'section_id', 'name', 'slug','photo_url','thumbnail')
                    ->withCount('subCategories');
            }])
            ->withCount('categories')
            ->get();

        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $categories;
    }
    
    public function getSectionCategoriesSubs($section_id)
    {
        $categories = Section::where('id', $section_id)
            ->select('id', 'name')
            ->with(['categories' => function ($query) {
                $query->select('id', 'section_id', 'name', 'slug')
                    ->withCount('subCategories');
            }])
            ->with('categories.subCategories')
            ->get();

        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $categories;
    }
    
    
    public function getSubCategoriesProducts($sub_category_id, $date, $date_min, $date_max, $visible,$key)
    {

        $products = Product::where('sub_category_id', $sub_category_id)->where(function ($query) use($key){
			$query->where('item_no','LIKE','%'.$key.'%')
                ->orWhere('name->ar','LIKE','%'.$key.'%')
                ->orWhere('name->en','LIKE','%'.$key.'%')
                ->orWhereHas('product_variations', function ($query) use ($key) {
                    $query->where('sku_code', 'LIKE', '%' .$key. '%');
			});
                })
            ->select('id', 'available','sub_category_id', 'group_id', 'name', 'item_no','created_at')
            ->with(['main_photos:id,product_id,thumbnail','subCategory'])
            ->when($date != null, function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
			->when(($date_min != null && $date_max != null ), function($query) use ($date_min, $date_max ){
				$query->whereBetween('created_at', [$date_min, $date_max]);
			})
            ->when(($visible == 'visible' || $visible == 'hidden') && $visible != null , function($query) use ($visible) {
                if(Str::lower($visible) == 'visible'){
                    $query->where('available', true);
                }elseif(Str::lower($visible) == 'hidden'){
                    $query->where('available', false);
                }
            })
            ->withSum('stocks as total_stock', 'current_stock_level')
            ->withAvg('reviews as average_rating', 'rating')
            ->paginate(8);

        if (!$products) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $products;
    }
    public function getSectionsCategories($section_id)
    {
        $categories = Category::where('section_id', $section_id)->withCount('subCategories')
            ->valid()
            ->paginate(10);

        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $categories;
    }

    public function getSectionCategoriesInfo($section_id)
    {
        $categories = Category::where('section_id', $section_id)->get();

        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $categories;
    }


    public function getSectionSubCategories($section_id)
    {
        $sub_categories = Section::where('id', $section_id)->with('subCategories')->first()->subCategories;
        // return $sub_categories;

        if (!$sub_categories) {
            throw new InvalidArgumentException('There Is No Sub Categories Available');
        }

        $sub_category_fields = [
            'id',
            'name',
        ];

        return $this->getTranslatedFields($sub_categories, $sub_category_fields);
    }

    protected function filterByDate($query, $filter_data)
    {
        $date_min = $filter_data['date_from'] ?? 0;
        $date_max = $filter_data['date_to'] ?? date('Y-m-d');

        return $query->whereHas('created_at', [$date_min, $date_max]);
    }
}
