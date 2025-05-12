<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductResourceMobile;
use App\Http\Resources\ProductTranslatedResource;
use App\Http\Resources\ReviewsCollection;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Color;
use App\Models\Favourite;
use App\Models\Group;
use App\Models\Notify;
use App\Models\Photo;
use App\Models\Order;
use App\Models\Pricing;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Review;
use App\Models\Sku;
use App\Models\User;
use App\Models\Size;
use App\Models\StockLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Benchmark;
use Exception;
use App\Traits\TranslateFields;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use App\Traits\CloudinaryTrait;
use App\Http\Resources\ProductCollectionCopy;
use Umutphp\LaravelModelRecommendation\HasRecommendation;
use App\Http\Resources\ProductResourceCopy;
use App\Models\OrderItem;
use App\Models\LastViewed;
use DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    use CloudinaryTrait, HasRecommendation;
    public $fuzzy_prefix_length  = 2;
    public $fuzzy_max_expansions = 20;
    public $fuzzy_distance       = 10;
    // public function filter($filter_data, $limit){

    // }

    // public function search($search_data, $limit){

    // }

    // public function apply($filter_data, $search_data, $limit)
    // {
    //     if ($filter_data) {
    //         $this->filter($filter_data);
    //     }
    //     if ($search_data) {
    //         $this->search($search_data);
    //     }
    // }

    public function getAllProductsAdmin($filter_data = [], $search = null) {}

    public function getAllAvailableProducts($filter_data = [], $search = null, $user_id) //si
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->error('Unauthorized', 401);
            } else {
                $user->load(['favourites_products', 'notified_products', 'reviews']);
            }
            $notified_products = $user?->notified_products;
            $auth_review = $user?->reviews()->first();
            $favourites_products = $user?->favourites_products->pluck('id')->toArray();
            $products = Product::available()->with(
                [
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->orderBy('id')->inRandomOrder();

            if (!empty($filter_data)) {
                $products = $this->applyFilters($products, $filter_data);
            }

            $products = $products->paginate(6);

            if (!$products) {
                throw new Exception('There Is No Products Available');
            }
            // passing the user : reviews, favourite products, notified products into the product collection constructor
            return ProductCollection::make($products, $user, $notified_products, $favourites_products, $auth_review);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
        // return ProductResource::collection($products);
    }

    public function getAllFlashSalesProducts() //si
    {


        $user_id = 1;
        $end_date = '';
        $user = User::with(['favourites_products', 'notified_products', 'reviews'])->find($user_id);
        $notified_products = $user->notified_products;
        $auth_review = $user->reviews()->first();
        $favourites_products = $user->favourites_products->pluck('id')->toArray();
        $flash_sales_products = Product::whereHas('group', function ($query) {
            $query->where('promotion_type', 'flash_sales');
        })->whereHas('discount', function ($query) use ($end_date) {
            $query->where('end_date', '>=', now());
        })
            ->with([
                'product_variations',
                'product_variations.notifies',
                'product_variations.size',
                'product_variations.color',
                'product_variations.stock_levels',
                'favourites',
                'pricing',
                'reviews',
                'discount',
                'photos:id,product_id,color_id,path,main_photo',
                'group',
            ])->paginate(15);
        if ($flash_sales_products->count() == 0) {
            return 'There is no products';
        }
        $end_date = $flash_sales_products->first()->discount->end_date->format('Y:m:d:H:i:s') ?? null;

        return ['end_date' => $end_date, 'products' => array_values(ProductCollection::make($flash_sales_products, $user, $notified_products, $favourites_products, $auth_review)->values()->all())];
    }
    // public function getGroupProductsBySlug($slug,$filter_data = [],$user_id)
    // {
    //     try {
    //         // getting the user : reviews, favourite products, notified products
    //         $user = User::with(['favourites_products', 'notified_products', 'reviews'])->find($user_id);
    //         $notified_products = $user->notified_products;
    //         $auth_review = $user->reviews()->first();
    //         $favourites_products = $user->favourites_products->pluck('id')->toArray();
    //         // $group = Group::where("slug",$slug)->with('products','products.pricing')->first();
    //       $group_products=Product::available()->with(
    //         [
    //             'product_variations.size',
    //             'product_variations.color',
    //             'product_variations.stock_levels',
    //             'pricing',
    //             'reviews',
    //             'photos:id,product_id,color_id,path,main_photo',
    //             // 'group',
    //         ])->whereHas("group",function($query)use($slug){
    //             $query->where("slug",$slug);
    //       });
    //       if (!empty($filter_data)) {
    //         $group_products = $this->applyFilters($group_products, $filter_data);
    //     }
    //     $group_products =$group_products ->paginate(12);
    //         // if (!$group) {
    //         //     throw new Exception('Group Not Found');
    //         // }
    //         // $products=$group?->products;
    //         $products=  ProductCollection::make($group_products, $user, $notified_products, $favourites_products, $auth_review);
    //         return $products;
    //     } catch (Exception $th) {
    //         throw new Exception($th->getMessage());
    //     }
    // }


    public function getGroupProductsBySlug($slug, $filter_data = [])//si
    {
        try {
            $group_products = Product::available()->with(
                [
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    // 'group',
                ]
            )->whereHas("group", function ($query) use ($slug) {
                $query->where("slug", $slug);
            });
            if (!empty($filter_data)) {
                $group_products = $this->applyFilters($group_products, $filter_data);
            }
            $group_products = $group_products->paginate(12);
            return $group_products;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function attachProductVariationToGroup($product_variation_ids, int $group_id) //si
    {
        $products = ProductVariation::findOrFail($product_variation_ids);

        if ($products->isEmpty()) {
            throw new Exception('There Is No Product Available');
        }

        $group = Group::findOrFail($group_id);


        foreach ($products as $product) {
            $product->group_id = $group_id;
            $product->save();
        }
        return true;
    }
    public function attachProductToGroup(int $product_id, $group_id = null) //si
    {
        $product = Product::findOrFail($product_id);

        if (!$product) {
            throw new Exception('There Is No Product Available');
        }
		Log::debug('group_id'. $group_id);
		Log::debug('==============================');
        $group = Group::find($group_id);
		Log::debug('group: '. $group);
		Log::debug('==============================');
        if (!$group) {
			$product->update(['group_id' => null]); 
			Log::debug('product: '. $product);
			Log::debug('==============================');
			$product_variations = ProductVariation::where('product_id', $product->id)->get();
			Log::debug('product_variations: '. $product_variations);
			Log::debug('==============================');
			foreach ($product_variations as $product_variation) {
				Log::debug('product_variations: '. $product_variation);
				Log::debug('==============================');
				$product_variation->update([
					'group_id' => null,
				]);
			}
			return true;
		}
        $discount_id = optional($group->discounts)->id ?? null;
        //foreach ($products as $product) {
        //	return "hi";
        $product->group_id = $group_id;
        $product->discount_id = $discount_id;
        $product->save();
        $product_variations = ProductVariation::where('product_id', $product->id)->get();
        foreach ($product_variations as $product_variation) {
            $product_variation->update([
                'group_id' => $group_id,
            ]);
        }
        //}

        return true;
    }


    public function changeVisibility(int $product_id, $visible) //si
    {
        $product = Product::findOrFail($product_id);

        if ($product->available == $visible) {
            if ($visible == false)
                throw new Exception('Product is already hidden');
            if ($visible == true)
                throw new Exception('Product is already visible on store');
        }

        $product->available = $visible;
        $product->save();

        return $product;
    }
    public function getALlProductReviews(Product $product)
    {
        $user_id = auth('sanctum')->user()->id;
        $user = User::with(['favourites_products', 'notified_products', 'reviews'])->find($user_id);
        // $notified_products = $user->notified_products;
        $auth_review = $user->reviews()->first();
        //  $favourites_products = $user->favourites_products->pluck('id')->toArray();

        $product_id = $product->id;
        $reviews = $product->reviews()->with('user:id,first_name,last_name')->paginate(10);

        if (!$reviews) {
            throw new Exception('There Is No Reviews Available');
        }

        return ReviewsCollection::make($reviews,  $product_id, $user, $auth_review);
    }

    public function getAllFavouriteProducts(int $user_id, $key) //si
    {
        $user = User::findOrFail($user_id);

        if ($key == 'favourite') {
            $products = $user->favourites_products()->available()->with(
                [
                    'product_variations',
                    'product_variations.notifies',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'favourites',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->get();

            if (!$products) {
                throw new Exception('There Is No Products Available');
            }
        } else if ($key == 'notify') {
            $notifies = Notify::where('user_id', $user_id)->get()->pluck('product_variation_id')->toArray();
            $products = $user->notified_products()->with(
                [
                    'product_variations' => function ($query)  use ($notifies) {
                        $query->whereIn('id', $notifies);
                    },
                    'product_variations.notifies',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'favourites',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                    // 'group',
                    // 'promotionable'
                ]
            )->distinct()->get();

            if (!$products) {
                throw new Exception('There Is No Products Available');
            }
        } else if ($key == null) {
            $products_fav = $user->favourites_products()->available()->with(
                [
                    'product_variations',
                    'product_variations.notifies',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'favourites',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->get();
            //$f = ProductCollection::make($products_fav);
            $notifies = Notify::where('user_id', $user_id)->pluck('product_variation_id')->toArray();
            $products_not = $user->notified_products()->with(
                [
                    'product_variations' => function ($query) use ($notifies) {
                        // Add your conditions here
                        $query->whereIn('id', $notifies);
                    },
                    'product_variations.notifies',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'favourites',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->distinct()->get();
            $products = $products_fav->concat($products_not);
        }
        return ProductCollection::make($products);
    }

    public function getAllNotifiedProducts(int $user_id)
    {
        // replaced with auth user
        // $user = auth()->user();
        $user = User::find($user_id);
        $notified_products = $user->notified_products()->with(
            [
                'product_variations',
                'favourites',
                'pricing:name,currency,value',
                'photos:id,product_id,color_id,path,main_photo',
                'promotionable'
            ]
        )->get();
        // $notified_products = $user->notified_products->map(function($product_variation){
        //     return $product_variation->product()->available()->with(
        //         [
        //             'product_variations',
        //             'favourites',
        //             'pricing:name,currency,value',
        //             'photos:id,product_id,color_id,path,main_photo',
        //             'promotionable'
        //         ]
        //     )->get();
        // });

        if (!$notified_products) {
            throw new Exception('There Is No Products Available');
        }

        // return $notified_products;
        return ProductCollection::make($notified_products);
    }

    public function getProductsByGroup(int $group_id, $filter_data = [], $page_size, $user_id)
    {
        // replaced with auth user

        //$user_id = 1;
        $user = User::with(['favourites_products', 'notified_products', 'reviews'])->find($user_id);
        $notified_products = $user->notified_products;
        $auth_review = $user->reviews()->first();
        $favourites_products = $user->favourites_products->pluck('id')->toArray();
        $products = Product::where('group_id', $group_id)
            ->with(
                [
                    'product_variations',
                    'product_variations.notifies',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'favourites',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )
            ->inRandomOrder();

        // return $products;

        if (!empty($filter_data)) {
            $products = $this->applyFilters($products, $filter_data);
        }


        if ($page_size > $products->count()) {

            if ($products->count() == 0) {

                throw new Exception('There Is No Products Available');
            }

            //  $page_size = 1;

            $products = $products->simplePaginate($page_size);
        } else {

            $products = $products->paginate($page_size);
        }


        //$products = $products->paginate($page_size);

        if (!$products) {
            throw new Exception('There Is No Products Available');
        }
        // $products=  ProductCollection::make($products)->sortBy('id')->values()->all();
        //         return $products;

        $products =  ProductCollection::make($products, $user, $notified_products, $favourites_products, $auth_review)->sortBy('id')->values()->all();
        return [
            'products' => $products
        ];
    }


    public function getProductsByCategory(int $category_id, $filter_data = [], $page_size) //si
    {
        $user = auth('sanctum')->user();
        
        if ($user) {
            $user = auth('sanctum')->user()->load(['favourites_products', 'notified_products', 'reviews']);
        } else {
            $user = null;
        }

        $notified_products = $user?->notified_products;
        $auth_review = $user?->reviews()->first();
        $favourites_products = $user?->favourites_products->pluck('id')->toArray();
        $sub_categories = Category::where('id', $category_id)->first()->subCategories()->pluck('id');
        $products = Product::available()->whereIntegerInRaw('sub_category_id', $sub_categories)
            ->with(
                [
                    'product_variations',
                    'product_variations.notifies',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'favourites',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            );

        if (isset($filter_data['sort'])) {
            if ($filter_data['sort'] == 'price_asc') {
                $products->join('pricings', 'products.id', '=', 'pricings.product_id')
                    ->select('products.*', 'pricings.value as pricing_value');
                $products = $products->orderBy('pricing_value');
            } elseif ($filter_data['sort'] == 'price_desc') {
                $products->join('pricings', 'products.id', '=', 'pricings.product_id')
                    ->select('products.*', 'pricings.value as pricing_value');
                $products = $products->orderBy('pricing_value', 'desc');
            }

            $filter_data['sort'] = null;
        } else {
            $products = $products->inRandomOrder();
        }
        //return $products->get(); 
        if (!empty($filter_data)) {
            $products = $this->applyFilters($products, $filter_data);
        }

        /*  if ($page_size > $products->count()) {

            if ($products->count() == 0) {

                throw new Exception('There Is No Products Available');
            }

            //  $page_size = 1;

            $products = $products->simplePaginate($page_size);
        } else {


            $products = $products->paginate($page_size);
        }

*/
        //  $products = $products->paginate($page_size);


        $products = $products->get();
        if (!$products) {
            throw new Exception('There Is No Products Available');
        }
        // $products=  ProductCollection::make($products)->sortBy('id')->values()->all();
        //         return $products;
        $products =  ProductCollection::make($products, $user, $notified_products, $favourites_products, $auth_review)->values()->all();



        $colors = Color::select('id', 'hex_code')->get();
        $sizes = Size::select('id', 'value')->get();
        $subs = SubCategory::valid()->where('category_id', $category_id)->select('id', 'name')->get();
        return [
            'products' => $products,
            'colors' => $colors,
            'sizes' => $sizes,
            'subs' => $subs
        ];
    }

    protected function applySort($query, array $sort_data)
    {
        // return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);


        return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
    }

    protected function applyFilters($query, array $filters)
    {



        foreach ($filters as $attribute => $value) {
            $column_name = Str::before($attribute, '_');
            $method = 'filterBy' . Str::studly($column_name);

            if (method_exists($this, $method) && isset($value) && $value != null) {
                $query = $this->{$method}($query, $filters);
            }
        }
        return $query;
    }

    protected function filterByTotal($query, $filter_data)
    {
        $total_min = $filter_data['total_min'] ?? 0;
        $total_max = $filter_data['total_max'] ?? 10000000;
        return $query->whereBetween('total_price', [$total_min, $total_max]);
    }

    protected function filterByQuantity($query, $filter_data)
    {
        return $query->where('total_quantity', $filter_data['quantity']);
    }

    protected function filterByStatus($query, $filter_data)
    {
        if ($filter_data['status'] == 'all') {
            return $query;
        }
        // return $query->whereIn('status', $filter_data['status']);
        return $query->where('status', $filter_data['status']);
    }

    public function filterByDate($query, $filter_data)
    {
        $date_min = $filter_data['date_min'] ?? 0;
        $date_max = $filter_data['date_max'] ?? date('Y-m-d');

        return $query->whereBetween('orders.created_at', [$date_min, $date_max]);
    }

    protected function filterByPrice($query, $filter_data)
    {

		


        $price_min = $filter_data['price_min'] ?? 0;
        $price_max = $filter_data['price_max'] ?? 10000000;
		
	//	dd($price_max);
        $query->whereHas('pricing', function ($query) use ($price_min, $price_max) {
            return $query->whereBetween('value', [$price_min, $price_max]);
        });
        return $query;
    }

    protected function filterBySub($query, $filter_data)
    {
        $query->where('sub_category_id', $filter_data['sub_category']);
        return $query;
    }

    protected function filterByColors($query, $filter_data)
    {
        $colors = $filter_data['colors'];

        $query->whereHas('colors', function ($query) use ($colors) {
            $query->whereIn('colors.id', $colors);
        });

        return $query;
    }

    protected function filterByColor($query, $filter_data)
    {
        $colors = $filter_data['color'];
        if (gettype($colors[0]) == 'string') {
            $query->whereHas('colors', function ($query) use ($colors) {
                $query->whereIn('colors.hex_code', $colors);
            });
        } else {
            $query->whereHas('colors', function ($query) use ($colors) {
                $query->whereIn('colors.id', $colors);
            });
        }

        return $query;
    }

    protected function filterBySize($query, $filter_data)
    {
        $sizes = $filter_data['size'];

        $query->whereHas('sizes', function ($query) use ($sizes) {
            $query->whereIntegerInRaw('size_id', $sizes);
        });

        return $query;
    }
    protected function filterBySizes($query, $filter_data)
    {
        $sizes = $filter_data['sizes'];

        $query->whereHas('sizes', function ($query) use ($sizes) {
            $query->whereIntegerInRaw('size_id', $sizes);
        });

        return $query;
    }

    protected function filterBySort($query, $filter_data)
    {
        $query->whereHas('pricing', function ($query) use ($filter_data) {
            if (($filter_data['sort']) === 'price_desc') {
                $query->orderBy('value', 'DESC');
            } elseif (($filter_data['sort']) === 'price_asc') {
                return $query->orderBy('value', 'asc');
            }
        });

        return $query;
    }

    public function fuzzySearch($keyword, $filter_data = [], $category_id = null, $sub_category_id = null) //si
    {
        $prefix = mb_substr($keyword, 0, $this->fuzzy_prefix_length);

        $user = auth('sanctum')->user();

        $matches = Product::with('product_variations', 'pricing', 'group')->available()
            ->where(function ($query) use ($prefix, $category_id) {
                $query->where(function ($query) use ($prefix) {
					$query->where('products.name->ar', 'like', '%' . mb_strtolower($prefix) . '%')
						->orWhere('products.name->en', 'like', '%' . mb_strtolower($prefix) . '%')
                      //  ->orWhere('description->en', 'like', '%' . mb_strtolower($prefix) . '%')
                       // ->orWhere('description->en', 'like', '%' . mb_strtolower($prefix) . '%')
                        ->orWhere('products.item_no', 'like', '%' . mb_strtolower($prefix) . '%');
                     //   ->orWhere('season->en', 'like', '%' . mb_strtolower($prefix) . '%')
                       // ->orWhere('season->ar', 'like', '%' . mb_strtolower($prefix) . '%');
                     /*  ->orWhereHas('product_variations', function ($query) use ($prefix) {
                            $query->where('sku_code', 'like', '%' . mb_strtolower($prefix) . '%');
                        });*/
                });
            });

        if ($sub_category_id) {
            $matches = $matches->where('sub_category_id', $sub_category_id);
        } else {
            $matches = $matches->whereIn('sub_category_id', function ($query) use ($category_id) {
                $query->select('sub_categories.id')
                    ->from('sub_categories')
                    ->join('categories', 'sub_categories.category_id', '=', 'categories.id');
                if ($category_id) { // check if category_id is not null
                    $query->where('categories.id', $category_id);
                }
            });
        }

		if (isset($filter_data['sort'])) {
			if ($filter_data['sort'] == 'price_asc') {
				$matches->join('pricings', 'products.id', '=', 'pricings.product_id')
					->select('products.*', 'pricings.value as pricing_value');
				$products = $matches->orderBy('pricing_value')->get();
			} elseif ($filter_data['sort'] == 'price_desc') {
				$matches->join('pricings', 'products.id', '=', 'pricings.product_id')
					->select('products.*', 'pricings.value as pricing_value');
				$products = $matches->orderBy('pricing_value', 'desc')->get();
			}
			$filter_data['sort'] = null;
			//$matches = $matches->all();
		}
        if (!empty($filter_data)) {
			$filter_data;
            $matches = $this->applyFilters($matches, $filter_data);
        }
		//dd($matches);
        $matches = $matches->get();
		
		//dd($matches);
        //  return $matches;
        $resultSet = [];
        foreach ($matches as $match) {

            $product = json_decode($match);
            if (!empty($match->product_variations) && count($match->product_variations) > 0) {
                $sku_code = $match->product_variations[0]->sku_code;
            } else ($sku_code = 0
            );
            $attributes = [
                $product->name->en,
                $product->name->ar,
                $product->description->ar,
                $product->description->en,
                $product->season->en,
                $product->season->ar,
                $product->season->ar,
                $product->item_no,
                $sku_code,
            ];
            //  $attributes;
            //  $minDistance = 1000 ;
            $minDistance = PHP_INT_MAX;

            // $closestMatch = null;

            foreach ($attributes as $attribute) {
                $distance1 = levenshtein($attribute, $keyword);
                if ($distance1 < $minDistance) {
                    $minDistance = $distance1;
                    $distance = $distance1;
                }
            }

            if ($distance <= $this->fuzzy_distance) {
                $match->distance = $distance;
                $resultSet[] = $match;
            }
        }

        $distance = [];
        $hits = [];

        $resultSet = collect($resultSet)->map(function ($item, $key) use (&$distance, &$hits) {
            $distance[$key] = $item->distance;
            $hits[$key] = $item->created_at;
            return $item;
        });
        //   return $resultSet;
        array_multisort($distance, SORT_ASC, $hits, SORT_DESC, $resultSet->toArray());

        // $result = ProductCollection::make($resultSet->values()->all());





        $result =  ProductCollection::make($resultSet, $user)/*->sortBy('id')*/->values()->all();
        $colors = Color::select('id', 'hex_code')->get();
        $sizes = Size::select('id', 'value')->get();
        $subs = SubCategory::select('id', 'name')->get();
        return   [
            'products' => $result,
            'colors' => $colors,
            'sizes' => $sizes,
            'subs' => $subs
        ];

        // dd($resultSet->values()->all());
        //return response()->success($response,200);
    }

    public function searchProduct($search_data)
    {
        // return response()->json([
        //     'data' => $search_data,
        // 'data' => $search_data,
        // 'condition' => array_key_exists('name',$search_data)
        // ]);
        $products = Product::when((isset($search_data)), function ($query) use ($search_data) {
            // $query->whereLocales('name', ['en', 'ar']);
            $langs = ['en', 'ar'];
            foreach ($langs as $value) {
                return $query->where('name->' . $value, 'LIKE', '%' . $search_data['search'] . '%')
                    ->orWhere('description->' . $value, 'LIKE', '%' . $search_data['search'] . '%');
            }
        })->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->pricings->map(function ($price) {
                        return [
                            'name'   => $price->name,
                            'value'   => $price->value,
                            'currency'   => $price->currency,
                        ];
                    }),
                ];
            });

        if (!$products) {
            throw new Exception('Product not found');
        }

        return $products;
    }

    public function getProduct($product_id): Product
    {

        $product = Product::findOrFail($product_id);



        return $product;
    }

    public function create($product_data, int $sub_category_id) //si
    {
        $product = Product::create([
            'sub_category_id' => $sub_category_id,
            'item_no' => $product_data['product_item_no'],
            'name' => ["en" => $product_data['product_name_en'], "ar" => $product_data['product_name_ar']],
            'description' => ["en" => $product_data['product_description_en'], "ar" => $product_data['product_description_ar']],
            'material' => ["en" => $product_data['product_material_en'], "ar" => $product_data['product_material_ar']],
            'composition' => ["en" => $product_data['product_composition_en'], "ar" => $product_data['product_composition_ar']],
            'care_instructions' => ["en" => $product_data['product_care_instructions_en'], "ar" => $product_data['product_care_instructions_ar']],
            'fit' => ["en" => $product_data['product_fit_en'], "ar" => $product_data['product_fit_ar']],
            'style' => ["en" => $product_data['product_style_en'], "ar" => $product_data['product_style_ar']],
            'season' => ["en" => $product_data['product_season_en'], "ar" => $product_data['product_season_ar']],
        ]);

        return $product;
    }

    public function createPrice($pricing_data, int $product_id) //si
    {
        $pricings = [];
        foreach ($pricing_data as $pricing_item) {
            $pricing = Pricing::create([
                'product_id' => $product_id,
                'name' => $pricing_item['price_name'],
                'location' => $pricing_item['price_location'],
                'currency' => $pricing_item['price_currency'],
                'value' => $pricing_item['price_value']
            ]);

            if (!$pricing) {
                throw new Exception("something wrong happeened while creating the price");
            }

            $pricings[] = $pricing;
        }

        return $pricings;
    }


    public function createProductVariation(int $product_id, $item_no, $variations_data) //si
    {

		 $variations = [];
		
        foreach ($variations_data as $variations_item) {
            $sku_code = $item_no . '-' . $variations_item['color_sku'] . '-' . $variations_item['size_sku'];

            $variation = ProductVariation::firstOrCreate([
                'product_id' => $product_id,
                'color_id' => $variations_item['color_id'],
                'size_id' => $variations_item['size_id'],
                'sku_code' => $sku_code,
            ]);

            // Check if a photo already exists for this product_id and color_id
            $existingPhoto = Photo::where('product_id', $product_id)
                ->where('color_id', $variations_item['color_id'])
                ->first();

            // If no photo exists, create a new one
            if (!$existingPhoto) {
                Photo::create([
                    'product_id' => $product_id,
                    'color_id' => $variations_item['color_id'],
                    'thumbnail' => "https://api.xo-textile.sy/public/images/xo-logo.webp",
                    'path' => "https://api.xo-textile.sy/public/images/xo-logo.webp",
                    'main_photo' => 1,
                ]);

                $variations[] = $variation;
            }
        }
        return $variations;
    }

    public function storePhotos(int $product_id, $photos_data) //si
    {
        $rejected_photos = [];
        foreach ($photos_data as $photo) {
            $xo_photo = Photo::where([
                ['color_id', $photo['color_id']],
                ['path', "https://api.xo-textile.sy/public/images/xo-logo.webp"],
                ['product_id', $product_id]
            ])->first();

            if ($xo_photo) {
                $xo_photo->forceDelete();
            }

            $image = $photo['image'];
            $color_id = $photo['color_id'];
            $main_photo = $photo['main_photo'];
            $image_size = ($photo['image']->getSize()) / 1024;
            $image_name = $photo['image']->getClientOriginalName();

            if ($image_size >= 1024) {
                array_push($rejected_photos, "Image '" .  $image_name . "' must be smaller than 2048 kb");
            } elseif (getimagesize($image)[0] < 1000 && getimagesize($image)[1] < 500) {
                array_push($rejected_photos, "Image '" . $image_name . "' is too small, upload image with size of larger than 512 * 512");
            } else {
                $photo_path = $this->saveImage($image, 'photo', 'products');
                $photo = Photo::create([
                    'product_id' => $product_id,
                    'color_id' => $color_id,
                    'path' => $photo_path,
                    'thumbnail' => $photo_path,
                    'main_photo' => $main_photo
                ]);
            }
        }
        return $rejected_photos;
    }

    public function updatePhotos(int $product_id, $photos_data) //si
    {
        foreach ($photos_data as $photo) {
            $image = $photo['image'];
            $color_id = $photo['color_id'];
            $main_photo = $photo['main_photo'];

            $photo_path = $this->saveImage($image, 'photo', 'products');
            $photo = Photo::create([
                'product_id' => $product_id,
                'color_id' => $color_id,
                'path' => $photo_path,
                'thumbnail' => $photo_path,
                'main_photo' => $main_photo
            ]);
        }
    }

    // 
    public function update(array $data, int $product_id)
    {
        $product = Product::findOrFail($product_id);
        $product->update([
            'name' => ["en" => $data['product_name_en'], "ar" => $data['product_name_ar']],
            'description' => ["en" => $data['product_description_en'], "ar" => $data['product_description_ar']],
            'material' => ["en" => $data['product_material_en'], "ar" => $data['product_material_ar']],
            'composition' => ["en" => $data['product_composition_en'], "ar" => $data['product_composition_ar']],
            'care_instructions' => ["en" => $data['product_care_instructions_en'], "ar" => $data['product_care_instructions_ar']],
            'fit' => ["en" => $data['product_fit_en'], "ar" => $data['product_fit_ar']],
            'style' => ["en" => $data['product_style_en'], "ar" => $data['product_style_ar']],
            'season' => ["en" => $data['product_season_en'], "ar" => $data['product_season_ar']],
            //'sub_category_id' => $data['sub_category_id'],
            //'item_no' => $data['item_no'],
            //'name' => $data['product_name'],
            //'description' => $data['description'],
            //'pricing' => $data['pricing'],
            //'material' => $data['material'],
            //'composition' => $data['composition'],
            //'care_instructions' => $data['care_instructions'],
            //'fit' => $data['fit'],
            //'style' => $data['style'],
            //'season' => $data['season'],
        ]);

        return $product;
    }
    public function updatePricing(Pricing $pricing, int $pricing_data) //si
    {

        $pricing->update(['value' => $pricing_data]);
    }
    public function showCounts($inventory_id, $product_id, $date)
    {
        try {
            $currentDate = Carbon::now();

            if ($inventory_id != null && isset($inventory_id)) {
                $product = Product::whereHas('inventories', function (Builder $query) use ($inventory_id) {
                    $query->where('inventories.id', $inventory_id);
                })
                    ->withCount('inventories')
                    ->withSum('stocks as num_stock', 'current_stock_level')
                    ->withSum('order_items as total_sold', function ($query) use ($currentDate, $date) {
                        if ($date == 'month' && $date == null) {
                            $query->whereMonth('created_at', '=', $currentDate->format('m'));
                        } elseif ($date == 'week') {
                            $query->whereBetween('created_at', [
                                $currentDate->startOfWeek(),
                                $currentDate->endOfWeek()
                            ]);
                        }
                    })->withSum('order_items as total_profits', function ($query) use ($currentDate, $date) {
                        if ($date === 'month' && $date == null) {
                            $query->whereMonth('created_at', '=', $currentDate->format('m'));
                        } elseif ($date === 'week') {
                            $query->whereBetween('created_at', [
                                $currentDate->startOfWeek(),
                                $currentDate->endOfWeek()
                            ]);
                        }
                    })
                    ->findOrFail($product_id);
                return $product;
            } else {
                $product = Product::withCount('inventories')
                    ->withSum('stocks as num_stock', 'current_stock_level')
                    ->withSum('order_items as total_sold', 'quantity')
                    ->withSum('order_items as total_profits', 'price')
                    ->findOrFail($product_id);
            }

            return  [
                'inventories_count' => $product->inventories_count,
                'pieces_sold' => (int) $product->total_sold ?? 0,
                'profits' => (int) $product->total_profits ?? 0,
                'num_stock' => (int) $product->num_stock ?? 0
            ];
        } catch (\Exception $th) {
            throw new Exception($th);
        }
    }

    public function showDashboard($product_id) //si
    {
        try {
            $product = Product::with(['subCategory', 'main_photos'])
                ->withCount(['reviews', 'orders', 'stocks'])
                ->findOrFail($product_id);

            $product_details = $product->subCategory()
                ->select('id', 'category_id', 'name')->with([
                    'category:id,section_id,name',
                    'category.section:id,name',
                ])->get();

            $product_details = $product_details->map(function ($item) use ($product) {
                $item['counts'] = [
                    'orders' => Order::whereHas('order_items.product_variation.product', function($query) use ($product) {
				$query->where('id', $product->id);
			})->count(),
                    'reviews' => $product->reviews_count,
                    'stocks' => $product->stocks_count,
                ];
                $item['product'] = [
                    'product_name' => $product->getTranslations('name'),
                    'product_created_at' => $product->created_at,
                    'photo' => $product->main_photos()->first()->path ??  $product->photos()->first()->path,
                    'visible' => $product->available,
                    'group_id' => $product->group_id,
                ];
                return $item;
            });

            return $product_details;
        } catch (\Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function show(int $product_id) //si
    {

        $product = Product::findOrFail($product_id);

        $product = $product->load([
            'product_variations',
            'product_variations.color',
            'product_variations.size',
            'reviews:comment,rating',
            'photos',
            'group'
        ]);
        return ProductTranslatedResource::make($product);
    }

    public function showProduct($product_slug = null, $product_sku = null, $width = null, $height = null, $enable) //si
    {
        $product = Product::where('slug', $product_slug)->first();
        $user = auth('sanctum')->user();

        if ($user) {
            $user->lastViewed()->syncWithoutDetaching($product->id);
            $viewed_poduct_ids = $user->lastViewed()->pluck('product_id');
            if (count($viewed_poduct_ids) > 10) {
                $idsToRemove = $viewed_poduct_ids->slice(0, count($viewed_poduct_ids) - 10);

                $user->lastViewed()->detach($idsToRemove);
            }
        }

        if (!$product_slug && !$product_sku) {
            throw new Exception('Either product_slug or product_sku must be provided.');
        }

        // Start building the query
        $query = Product::query();

        // Add condition for product_slug if it's provided
        if ($product_slug) {
            $query->where('slug', $product_slug);
        }

        // Add condition for product_sku if it's provided
        if ($product_sku) {
            $query->orWhere('item_no', $product_sku);
        }

        // Execute the query and get the first result
        $product = $query->firstOrFail();

        if (!$product) {
            throw new Exception('Product is not found');
        }

        if (!$product->isAvailable()) {
            throw new Exception('Product is unavailable');
        }

        $product = $product->load([
            'product_variations',
            'product_variations.color',
            'product_variations.size',
            'reviews:comment,rating',
            'photos',
            'group'
        ]);


        // return $product;


	if(isset($enable) && $enable == 'on'){
		
		        $product_resault = ProductResourceMobile::make($product);

}
		
		else {
		        $product_resault = ProductResourceMobile::make($product,$width,$height);

		}


        return $product_resault;
    }

    public function info(int $product_id)
    {
        try {
            $product = Product::findOrFail($product_id)->load('colors', 'sizes');

            return $product;
        } catch (\Exception $th) {
            throw $th->getMessage();
        }
    }

    public function getReviews(int $product_id, $filter_data) //si
    {
        try {
            $product = Product::findOrFail($product_id)
                ->load(['reviews']);

            $product_reviews = $product->reviews()
                ->with('user:id,first_name,last_name,phone,banned')
                ->when($filter_data['content'] != null, function ($query) use ($filter_data) {
                    $query->where('comment', 'LIKE', '%' . $filter_data['content'] . '%');
                })
                ->when($filter_data['rating'] != null, function ($query) use ($filter_data) {
                    $query->where('rating', '>=', $filter_data['rating']);
                })
                ->when($filter_data['date_min'] != null || $filter_data['date_max'] != null, function ($query) use ($filter_data) {
                    return $query->whereBetween('created_at', [$filter_data['date_min'], $filter_data['date_max']]);
                })->paginate(8);

            return [
                'reviews_count' => $product->reviews()->count(),
                // 'reviews_count' => count($product_reviews),
                'product_reviews' => $product_reviews
            ];
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function getOrders(int $product_id, $filter_data, $sort_data) //si
    {
        try {
			/*$product_orders = Order::whereHas('order_items', function($query) use($product_id){
				$query->whereHas('product_variation', function($query) use($product_id){
					$query->whereHas('product', function($query) use($product_id){
						$query->where('id', $product_id);
					});
				});
			});*/
			
			$product_orders = Order::whereHas('order_items.product_variation.product', function($query) use ($product_id) {
				$query->where('id', $product_id);
			})->with(['user:id,first_name,last_name,phone', 'shipment:id,order_id,city,street,neighborhood']);

            if (!$product_orders->exists()) {
                return [
                    'orders_count' => 0,
                    //   'orders_count' => count($product_orders),
                    'product_orders' => $product_orders->paginate(8)
                ];
                //throw new Exception('There is no orders');
            }
			
			if(!empty($filter_data['status'])){
                if ($filter_data['status'] == 'all') {
                    $product_orders = $product_orders;
                }else{
					$product_orders = $product_orders->where('orders.status', $filter_data['status']);
                }
                $filter_data['status'] = null;
            }
			
            if (!empty($filter_data)) {
                $product_orders = $this->applyFilters($product_orders, $filter_data);
            }

            if ((isset($sort_data['sort_key']) && isset($sort_data['sort_value'])) && ($sort_data['sort_key'] != null && $sort_data['sort_value'] != null)) {
                $product_orders = $this->applySort($product_orders, $sort_data);
            }

            return [
                'orders_count' => $product_orders->count(),
                //   'orders_count' => count($product_orders),
                'product_orders' => $product_orders->paginate(8)
            ];
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function getStocks(int $product_id, $inventory_id = null, $filter_data)
    {
        // return $filter_data;
        try {
            if ($inventory_id == 0) {
                $inventory_id = null;
            }
            $product = Product::findOrFail($product_id);
            $product_stocks = $product->stocks()
                ->with([
                    'product_variation',
                    'product_variation.size:id,value,sku_code',
                    'product_variation.color:id,name,hex_code,sku_code',
                ])->when($inventory_id, function ($query, $inventory_id) {
                    return $query->where('inventory_id', $inventory_id);
                });
            if (!empty($filter_data)) {
				if($filter_data['status'] == 'all'){
					$product_stocks = $product_stocks;	
				}else{
					$product_stocks = $product_stocks->where('status',$filter_data['status']);	
					//$product_stocks = $product_stocks->where('status',$filter_data['status']);	
				}
				//return $product_stocks->get();
				
				//return $filter_data['status'];
                //$filter_data['status'] = $filter_data;
                //$product_stocks = $this->applyFilters($product_stocks, $filter_data);
            }

            return [
                'stocks_count' => $product_stocks->count(),
                'product_stocks' => $product_stocks->paginate(8)
            ];
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function getProductByItem_no($item_no) //si
    {
        $user_id  = 1;
        $user = User::with(['favourites_products', 'notified_products', 'reviews'])->find($user_id);
        $notified_products = $user->notified_products;
        $auth_review = $user->reviews()->first();
        $favourites_products = $user->favourites_products->pluck('id')->toArray();
        $product = Product::where('item_no', $item_no)->with(
            [
                'product_variations.size',
                'product_variations.color',
                'product_variations.stock_levels',
                'pricing',
                'reviews',
                'photos:id,product_id,color_id,path,main_photo',
                'group',
            ]
        )->get();
        if ($product->isEmpty()) {
            $product = Product::whereHas('product_variations', function ($query) use ($item_no) {
                $query->where('sku_code', $item_no);
            })->with(
                [
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->get();
            /*if ($product->isEmpty()){
				throw new Exception('Product is not found');
			}	*/
        }

        return ProductCollection::make($product, $user, $notified_products, $favourites_products, $auth_review);
        //return ProductCollectionCopy::make($product, $user, $notified_products, $favourites_products, $auth_review);


    }

    public function removeFavourite($user, $product_id)
    {
        $user_favourites = $user->favourites_products()->pluck('product_id')->toArray();
        if (!in_array($product_id, $user_favourites)) {
            throw new Exception('This product is not in your favourites');
        } else {
            $user->favourites_products()->detach($product_id);
        }
        return $user->favourites_products()->paginate(6);
    }

    public function delete(int $product_id): void //si
    {
        $product = Product::findOrFail($product_id);
        $product->stocks()->delete();
        $product->product_variations()->delete();
        $product->delete();
    }

    public function deleteMany($product_ids) //si
    {

        foreach ($product_ids as $product_id) {
            $product = Product::find($product_id);

            if (!$product) {
                throw new Exception('Product with ID ' . $product_id . ' does not exist');
            }
            $product->delete();
        }
        return true;
    }

    public function forceDelete(int $product_id): void
    {
        $product = Product::findOrFail($product_id);


        $product->forceDelete();
    }


    public function similar_products($product_id) //si
    {
        // Product::generateRecommendations('sold_together');
        $product = Product::findOrFail($product_id);
        $recommendations = $product->getRecommendations('sold_together');

        if (!$recommendations) {
            throw new Exception('Product not found');
        }
        $recommendations->load(
            'product_variations',
            'product_variations.color',
            'product_variations.size',
            'reviews:comment,rating',
            'photos',
            'group'
        );
        $arr = [];
        foreach ($recommendations as $recommendation) {
            array_push($arr, ProductResource::make($recommendation));
        }
        return collect($arr);
    }


    public function recommendation_products($user = null)//si
    {
        $recommendations = [];
        $notified_products = $user?->notified_products;
        $auth_review = $user?->reviews()->first();
        $favourites_products = $user?->favourites_products;

        try {
            //$user = auth('sanctum')->user()->load(['favourites_products', 'notified_products', 'reviews']);

            if ($user != null) {
                $orders = User::find($user->id)->orders()->with(
                    'order_items',
                    'order_items.product_variation',
                    'order_items.product_variation.product.photos',
                    'order_items.product_variation.size',
                    'order_items.product_variation.color'
                )->get();
                // return $orders;
                $productIds = [];
                if (isset($orders) && $orders->isNotEmpty()) {
                    foreach ($orders as $order) {
                        foreach ($order->order_items as $orderItem) {
                            $productIds[] = $orderItem->product_variation->product_id;
                        }
                    }
                    // return $productIds;
                    foreach ($productIds as $productId) {
                      $product = Product::available()
    ->with([
        'product_variations',
        'product_variations.size',
        'product_variations.color',
        'product_variations.stock_levels',
        'pricing',
        'reviews',
        'photos:id,product_id,color_id,path,main_photo',
        'group',
    ])
    ->distinct()
    ->findOrFail($productId);

						
	
                  /*      $recommendations[] = $product->getRecommendationsWithRelationships('sold_together', [
                            'product_variations',
                            'product_variations.size',
                            'product_variations.color',
                            'product_variations.stock_levels',
                            'pricing',
                            'reviews',
                            'photos:id,product_id,color_id,path,main_photo',
                            'group',
                        ]);
						
					*/	
$recommendations[] = Product::available()->with('product_variations',
                            'product_variations.size',
                            'product_variations.color',
                            'product_variations.stock_levels',
                            'pricing',
                            'reviews',
                            'photos:id,product_id,color_id,path,main_photo',
                            'group')->where('sub_category_id', $product->sub_category_id)
    ->where('id', '!=', $productId) // Exclude the current product
    ->limit(3)->distinct()->get(); // Match 'recommendation_count';
						

                    }
                }
            }
			
		//	dd($products);
            if (count($recommendations) == 0) {
                throw new Exception('no recommendations based on user orders');
            }
        } catch (Exception $e) {
            $recommendations = Product::available()->with(
                [
                    'product_variations',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->inRandomOrder()->distinct()->get();
        }

        // foreach ($products as $product) {
        //     $productId = Product::find($product->id);
        //     $recommendations[] = $product->getRecommendations('sold_together');
        // }
        // return 123 ;

        /*  $favourites_products= $user->favourites_products->map(function ($product) {
            unset($product->pivot);
            return $product;
        });
        
*/


        //$favourites_products = $favourites_products->first();
        //dd($recommendations->whenLoaded('product_variations'));
        $recommendations = collect($recommendations)->flatten();

        /* return( Product::available()->with(
            [
                'product_variations',
                'product_variations.size',
                'product_variations.color',
                'product_variations.stock_levels',
                'pricing',
                'reviews',
                'photos:id,product_id,color_id,path,main_photo',
                'group',
            ]
        )->inRandomOrder()->take(5)->get());*/

//dd($products);
		
		/* dd(Product::where('sub_category_id', $subCategoryId)
    ->where('id', '!=', $productId) // Exclude the current product
    ->limit(3) // Match 'recommendation_count'
    ->get());
		*/
        $products =  ProductCollection::make($recommendations/*,$user,$notified_products,$favourites_products,$auth_review*/)->unique('id')->shuffle()->values()->all();
		
		
        return $products;
    }

    public function addProductToLastViewed($product_id)
    {
        try {
            $user = auth('sanctum')->user();
            //  $user_id  = $user->id;
            // $user = User::find($user_id);
            //$user = User::find(1);

            $product = Product::findOrFail($product_id);


            $user->lastViewed()->syncWithoutDetaching($product_id);
            // get the ids of all viewed products
            $viewed_poduct_ids = $user->lastViewed()->pluck('product_id');

            // if more than 10 products have been viewed, remove the oldest ones
            if (count($viewed_poduct_ids) > 10) {
                $idsToRemove = $viewed_poduct_ids->slice(0, count($viewed_poduct_ids) - 10);

                $user->lastViewed()->detach($idsToRemove);
            }

            return  $product;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function showUserLastViewedProducts() //si
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }

            $user = $user->load(['favourites_products', 'notified_products', 'reviews']);
            $last_viewed_products = $user->lastViewed()->pluck('product_id');
            $notified_products = $user->notified_products;
            $auth_review = $user->reviews()->first();
            $favourites_products = $user->favourites_products->pluck('id')->toArray();
            $products = Product::whereIn('id', $last_viewed_products)
                ->available()
                ->with([
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ])
                ->orderBy('id')
                ->inRandomOrder()
                ->get();

            // Check if any products were found
            if (!$products->isEmpty()) {
                return ProductCollection::make($products, $user, $notified_products, $favourites_products, $auth_review);
            } else {
                throw new Exception('There are no products available.');
            }
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function newIn($section_id = null, $category_id, $filter_data = [], $page_size) //si
    {
        $query = Product::where('available', 1)->whereIn('sub_category_id', function ($query) use ($section_id, $category_id) {
            $query->select('sub_categories.id')
                ->from('sub_categories')
                ->join('categories', 'sub_categories.category_id', '=', 'categories.id');
            if ($section_id) {
                $query->join('sections', 'categories.section_id', '=', 'sections.id')
                    ->where('sections.id', $section_id);
            }
            if ($category_id) {
                $query->where('categories.id', $category_id);
            }
        });
        $query = $query->with(
            [
                'product_variations',
                'product_variations.size',
                'product_variations.color',
                'product_variations.stock_levels',
                'pricing',
                'reviews',
                'photos:id,product_id,color_id,path,main_photo',
                'group',
            ]
        );

        if (isset($filter_data['sort'])) {
            if ($filter_data['sort'] == 'price_asc') {
                $query->join('pricings', 'products.id', '=', 'pricings.product_id')
                    ->select('products.*', 'pricings.value as pricing_value');
                $products = $query->orderBy('pricing_value')->get();
            } elseif ($filter_data['sort'] == 'price_desc') {
                $query->join('pricings', 'products.id', '=', 'pricings.product_id')
                    ->select('products.*', 'pricings.value as pricing_value');
                $products = $query->orderBy('pricing_value', 'desc')->get();
            }
            $filter_data['sort'] = null;
            $query->latest()->limit(24);
        } else {
            $query->latest();
        }


        if (!empty($filter_data)) {
            $query = $this->applyFilters($query, $filter_data);
        }

        $products = $query->get();
        $categories_by_section = Category::where('section_id', $section_id)->get()->pluck('id');
        if ($products->isEmpty()) {
            $products = [];
        } else {
            $products =  ProductCollection::make($products)/*->sortBy('id')*/->values()->all();
        }
        $colors = Color::select('id', 'hex_code')->get();
        $sizes = Size::select('id', 'value')->get();
        $subs = SubCategory::whereIn('id', $categories_by_section)->select('id', 'name')->get();
        //$subs = SubCategory::select('id', 'name')->get();
        $categories = Category::select('id', 'name')->get();
        return [
            'products' => $products,
            'colors' => $colors,
            'sizes' => $sizes,
            'subs' => $subs,
            'categories' => $categories
        ];
    }

    public function top_product($page_size, $user)//si
    {
      /*  $products = Product::available()->with(
            'product_variations',
            'product_variations.size',
            'product_variations.color',
            'product_variations.stock_levels',
            'pricing',
            'reviews',
            'photos:id,product_id,color_id,path,main_photo',
            'group',
        )->get();
        return ProductCollection::make($products)->values()->all();
*/
        $user = auth('sanctum')->user();

        if (!$user) {

            $user = null;
        } else {

            // Assuming $user is an instance of the User model
            $user->load(['orders' => function ($query) {
                $query->with('order_items');
            }]);
        }

        $products = [];
        $products_user_orders = [];
        $product_ids = [];
        $counts = OrderItem::select('product_variation_id', DB::raw('count(*) as count'))
            ->groupBy('product_variation_id')
            ->get();

        $sortedCounts = $counts->sortByDesc('count');
        $topTwoCounts = $sortedCounts->take(2)->values()->pluck('product_variation_id');
        $product_counts = ProductVariation::findOrFail($topTwoCounts)->load('product');
        $product_ids = $product_counts->pluck('product_id');

        $product_orders = $product_counts->map(function ($item) {
            return $item->product()->available()->with(
                'product_variations',
                'product_variations.size',
                'product_variations.color',
                'product_variations.stock_levels',
                'pricing',
                'reviews',
                'photos:id,product_id,color_id,path,main_photo',
                'group',
            )->get();
        });
        $product_ids = $product_ids->toArray();
        $last_viewed = LastViewed::select('user_id', 'product_id');
        $product_ids[count($product_ids)] = ($last_viewed->pluck('product_id')->flatten())->toArray();

        $product_last_viewed = Product::findOrFail($last_viewed->pluck('product_id'))->load(
            'product_variations',
            'product_variations.size',
            'product_variations.color',
            'product_variations.stock_levels',
            'pricing',
            'reviews',
            'photos:id,product_id,color_id,path,main_photo',
            'group',
        );


        if (!$counts && !$last_viewed) {
            $products = Product::withCount('orders')->available()->orderByDesc('orders_count')
                ->with(
                    [
                        'product_variations',
                        'product_variations.size',
                        'product_variations.color',
                        'product_variations.stock_levels',
                        'pricing',
                        'reviews',
                        'photos:id,product_id,color_id,path,main_photo',
                        'group',
                    ]
                )->orderBy('id')->inRandomOrder()->get();
            if (!$products) {
                throw new Exception('There Is No Products Available');
            }
            $products =  ProductCollection::make($products, $user)->sortBy('id')->values()->shuffle();
        }

        if ($user?->orders) {

            $products_user_orders = $user->orders->flatMap(function ($order) {

			//dd($products_user_orders);

                return $order->order_items->pluck('product_variation_id');
            });
        } else {
            $products = Product::available()->whereIn('id', collect($product_ids)->flatten())->with(
                'product_variations',
                'product_variations.size',
                'product_variations.color',
                'product_variations.stock_levels',
                'pricing',
                'reviews',
                'photos:id,product_id,color_id,path,main_photo',
                'group'
            )->inRandomOrder()->get();
            $products = $products->unique('id');
			//dd($products);

            return ProductCollection::make($products)->values()->shuffle();
        }

        $product_variations = ProductVariation::whereIn('id', $products_user_orders)->get();
        $product_ids = collect($product_ids)->push($product_variations->pluck('product_id'));

        // Assuming $product_variations is your collection of ProductVariation models
        // with relationships loaded as per your previous query

        // Group by category and count occurrences
        $categoryCounts = $product_variations->groupBy(function ($variation) {
            return $variation->product->subCategory->category;
        })->map(function ($variations) {
            return $variations->count();
        })->sortByDesc(function ($count) {
            return $count;
        });

        // Get the top N most common categories
        $topNCommonCategories = $categoryCounts->take(3); // Change 10 to however many categories you want to list
        $products_categories = $topNCommonCategories->map(function ($item, $key) {

            return Category::where('id', json_decode($key, true)['id'])->first()->products()->available()->with(
                'product_variations',
                'product_variations.size',
                'product_variations.color',
                'product_variations.stock_levels',
                'pricing',
                'reviews',
                'photos:id,product_id,color_id,path,main_photo',
                'group',
            )->get();
        })->values()->values();


        // Now $topNCommonCategories contains the IDs or names of the most common categories,
        // sorted by their occurrence count in descending order.


        /*$product_ids = $product_variations->pluck('product.id');

// Retrieve the products directly using the extracted IDs
$products = Product::with('subCategory.category')->whereIn('id', $product_ids)->get();
	
		// Assuming $products is a collection of Product models
$groupedProducts = $products->groupBy(function ($product) {
    // Access the subCategory and then the category of the product
		
    return $product->subCategory->category;

});
	
dd($groupedProducts);

*/
        // Assuming $groupedProducts is your collection of product groups
        /*$categoryCounts = $groupedProducts->mapWithKeys(function ($products, $category) {
    // Count the number of products in the group
    $count = $products->count();
    
    // Return an array or an object with the category name and the count
    return [$category => ['name' => $category, 'count' => $count]];
})->all();
*/
        // Now $categoryCounts is a collection where each item is an array/object with the category name and the count

        /* $category = new Category(json_decode(collect($categoryCounts)->pluck('name')->values()->first(),true));  
	   dd ($category->id);
	   dd(json_decode(collect($categoryCounts)->pluck('name')->values()->first(),true));
	   
//dd($categoryCounts);
// Step 2: Sort the categories by count in descending order
$sortedCategories = $categoryCounts->GroupByDesc(function ($count, $category) {
    return $count;
});
*/
        // Step 3: Select the top two categories
        /*$topTwoCategories = $sortedCategories->take(2);
	  
	  dd($topTwoCategories->keys()->first());
	  $product_categories = $topTwoCategories->map(function($item,$key){
	
		  return $key->product()->get();
	  
		  
	  });
dd($product_categories);
*/



        $combined = $products_categories->concat($product_last_viewed)->concat($product_orders);
        $combined = $combined->unique('id');

        $products = Product::available()->whereIn('id', collect($product_ids)->flatten())->with(
            'product_variations',
            'product_variations.size',
            'product_variations.color',
            'product_variations.stock_levels',
            'pricing',
            'reviews',
            'photos:id,product_id,color_id,path,main_photo',
            'group'
        )->inRandomOrder()->get();
$products = $products->unique('id');

        /* $products = Product::withCount('orders')->orderByDesc('orders_count')
            ->with(
                [
                    'product_variations',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->orderBy('id')->inRandomOrder()->paginate($page_size);
        if (!$products) {
            throw new Exception('There Is No Products Available');
        }*/
        //return $products =  ProductCollection::make($products, $user)->sortBy('id')->values()->all();




        return ProductCollection::make($products)->values()->shuffle();

        /*		
if(!$counts && !$last_viewed && $groupedProducts ){

  $products = Product::withCount('orders')->orderByDesc('orders_count')
            ->with(
                [
                    'product_variations',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->orderBy('id')->inRandomOrder()->paginate($page_size);
        if (!$products) {
            throw new Exception('There Is No Products Available');
        }
        $products =  ProductCollection::make($products, $user)->sortBy('id')->values()->all();
        return $products;
		}
		*/
    }












    public function newlyAddedProducts($page_size, $filter_data = [])
    {

        $oneMonthAgo = Carbon::now()->subMonth();

        $products = Product::where('created_at', '>=', $oneMonthAgo)
            ->with(
                [
                    'product_variations',
                    'product_variations.size',
                    'product_variations.color',
                    'product_variations.stock_levels',
                    'pricing',
                    'reviews',
                    'photos:id,product_id,color_id,path,main_photo',
                    'group',
                ]
            )->latest();
        if (!$products) {
            throw new Exception('There Is No Products Available');
        }




        if (!empty($filter_data)) {
            $products = $this->applyFilters($products, $filter_data);
        }
        $products = $products->paginate($page_size);

        $products =  ProductCollection::make($products)->values()->all();
        return $products;
    }
}
