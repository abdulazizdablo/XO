<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\Section;
use InvalidArgumentException;
use App\Traits\CloudinaryTrait;
use App\Traits\TranslateFields;
use Exception;

class SectionService
{
    use CloudinaryTrait, TranslateFields;

    public function getAllSections() //si
    {
        $sections = Section::withCount('categories')->get();

        if (!$sections) {
            throw new InvalidArgumentException('There Is No Sections Available');
        }

        return $sections;
    }

    public function comparePopularCategories($categories_id, $section_id = 1, $filter_data, $dateScope) //si
    {
        try {
            $date = $filter_data['date'] ?? 0;

            $section_id = $section_id ?? 1;

            if (empty($categories_id)) {
                $counts = Category::where('section_id', $section_id)
                    ->select('id', 'section_id', 'name')
                    ->with(
                        'categoryOrders',
                        function ($query) use ($date) {
                            $query->when($date != null, function ($query) use ($date) {
                                $query->whereDate('orders.created_at', $date);
                            });
                        }
                    )->withCount([
                        'categoryOrders as orders_count'
                    ])

                    ->orderBy('orders_count', 'desc')
                    ->take(6)
                    ->get();

                $total_orders_count = $counts->sum('orders_count');

                $counts = $counts->map(function ($category) use ($total_orders_count) {

                    if ($total_orders_count == 0) {
                        $category->percentage = 0;
                    } else {
                        $category->percentage = $category->orders_count * 100 / $total_orders_count;
                    }
                    return collect($category->toArray())
                        ->only(['id', 'section_id', 'name', 'orders_count', 'percentage'])
                        ->all();
                });
            } else {
                $counts = Category::where('section_id', $section_id)
                    ->whereIn('id', $categories_id)
                    ->select('id', 'section_id', 'name')
                    ->with(
                        'categoryOrders',
                        function ($query) use ($date) {
                            $query->when($date != null, function ($query) use ($date) {
                                $query->whereDate('orders.created_at', $date);
                            });
                        }
                    )->withCount(['categoryOrders as orders_count'])
                    ->orderBy('percentage', 'desc')
                    ->get();
                $total_orders_count = $counts->sum('orders_count');
                $counts = $counts->map(function ($category) use ($total_orders_count) {
                    if ($total_orders_count == 0) {
                        $category->percentage = 0;
                    } else {
                        $category->percentage = $category->orders_count * 100 / $total_orders_count;
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



    public function getSectionsInfo() //si
    {
        $sections = Section::get();

        if (!$sections) {
            throw new InvalidArgumentException('There Is No Sections Available');
        }
        return $sections;
        // return $sections->getFields($section_fields);

    }

    public function getSectionCategories($section_id) //si
    {
        $categories = Section::where('id', $section_id)
            ->select('id', 'name')
            ->with(['categories' => function ($query) {
                $query->where('valid',1)->select('id', 'section_id', 'name', 'slug','valid', 'photo_url', 'thumbnail')
                    ->withCount('subCategories');
            }])
            ->withCount('categories')
            ->get();

        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $categories;
    }
	
	public function getDashSectionCategories($section_id) //si
    {
        $categories = Section::where('id', $section_id)
            ->select('id', 'name')
            ->with(['categories' => function ($query) {
                $query/*->where('valid',1)*/->select('id', 'section_id', 'name', 'slug','valid', 'photo_url', 'thumbnail')
                    ->withCount('subCategories');
            }])
            ->withCount('categories')
            ->get();

        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $categories;
    }

    public function getSectionCategoriesSubs($section_id)//si
    {
        /*$categories = Section::where('id', $section_id)
            ->select('id', 'name')
            ->with(['categories' => function ($query) {
                $query->select('id', 'section_id', 'name', 'slug')
                    ->withCount('subCategories');
            }])
            ->with('categories.subCategories')
            ->get();
		*/
		
/*$categories = Section::where('id', $section_id)
    ->select('id', 'name')
    ->with(['categories' => function ($query) {
        $query->select('id', 'section_id', 'name', 'slug')
            ->where('valid', 1) // Filter categories where valid = 1
            ->withCount('subCategories')
            ->with(['subCategories']); // Load subcategories in the same query
    }])
    ->get();
		*/
		
		$categories = Section::where('id', $section_id)
    ->select('id', 'name')
    ->with(['categories' => function ($query) {
        $query->select('id', 'section_id', 'name', 'slug')
            ->where('valid', 1) // Filter categories where valid = 1
            ->withCount('subCategories')
            ->with(['subCategories' => function ($subQuery) {
                $subQuery->where('valid', 1); // Filter subcategories where valid = 1
            }]);
    }])
    ->get();




        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $categories;
    }

    public function getSectionCategoriesInfo($section_id) //si
    {
        $categories = Category::valid()->where('section_id', $section_id)->get();

		
        if (!$categories) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        return $categories;
    }


    public function getSectionSubCategories($section_id) //si
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
}
