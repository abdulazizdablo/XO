<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Product;
use App\Models\Discount;
use App\Models\Offer;
use App\Traits\CloudinaryTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\TranslateFields;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductCollectionCo;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Size;
use App\Models\Color;
use App\Models\SubCategory;
use App\Models\Category;
use  App\Models\FcmToken;
use App\Models\ProductVariation;
use App\Traits\FirebaseNotificationTrait;


class GroupService
{
    use CloudinaryTrait, TranslateFields,FirebaseNotificationTrait;

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
        if (in_array('all', $filter_data['status'])) {
            return $query;
        }
        return $query->whereIn('status', $filter_data['status']);
    }

    protected function filterByPrice($query, $filter_data)
    {
        $price_min = $filter_data['price_min'] ?? 0;
        $price_max = $filter_data['price_max'] ?? 10000000;



        $query->whereHas('pricing', function ($query) use ($price_min, $price_max) {
            return $query->whereBetween('value', [$price_min, $price_max]);
        });
        return $query;
    }
	
	protected function filterByFlutter($query, $filter_data)
    {

	$query->whereHas('group', function ($subQuery) use ($filter_data) {



    $subQuery->where('id',  $filter_data['flutter'])->whereNot('type','discount');
});
		return $query;
	}

    protected function filterBySub($query, $filter_data)
    {
	if(isset($filter_data['sub_category'])){
		$query->whereHas('subCategory', function ($item) use ($filter_data) {

					$item->where('id', $filter_data['sub_category']);
		});
	}else{
		$query->whereHas('subCategory', function ($item) use ($filter_data) {

					$item->where('id', $filter_data['sub_category_id']);
		});	
	}
	/*	
	$query->whereHas('group', function ($subQuery) use ($filter_data) {

	if(isset($filter_data['sub_category'])){
		$subQuery->where('id',  $filter_data['sub_category'])->whereNot('type','discount');	
	}
	else{
		$subQuery->where('id',  $filter_data['sub_category_id'])->whereNot('type','discount');
	}
});*/
//dd($query->toSql(), $filter_data['sub_category']);
        return $query;

    }

    protected function filterByColor($query, $filter_data)
    {
        $colors = $filter_data['color'];

        $query->whereHas('colors', function ($query) use ($colors) {
            $query->whereIn('hex_code', $colors);
        });

        return $query;
    }
    
	 protected function filterBySection($query, $filter_data)
{
	//dd($query->whereHas('subCategory')->get());
    $query->whereHas('subCategory', function ($categoryQuery) use ($filter_data) {
        $categoryQuery->whereHas('category', function ($querySection) use ($filter_data) {
            $querySection->whereHas('section', function ($item) use ($filter_data) {
                $item->where('id', $filter_data['section']);
            });
        });
    });

    return $query;
}


  protected function filterBySubCategory($query, $filter_data)
    {


$query->whereHas('group', function ($subQuery) use ($filter_data) {



    $subQuery->where('id',  $filter_data['sub_category']);
});

        return $query;
    }
    
    
     protected function filterByCategory($query, $filter_data)
    {


$query->whereHas('subCategory', function ($subQuery) use ($filter_data) {



    $subQuery->whereHas('category', function ($query) use ($filter_data) {

       
        $query->where('slug', $filter_data['category']);
    });
});

        return $query;
    }
    
    

    protected function filterBySize($query, $filter_data)
    {
        $sizes = $filter_data['size'];

        $query->whereHas('sizes', function ($query) use ($sizes) {
            $query->whereIn('size_id', $sizes);
        });

        return $query;
    }
    protected function filterByGroup($query, $filter_data)
    {
		//return $filter_data['group'];
       $query->whereHas('group',function($q) use($filter_data){
          
           //$q->where('slug',$filter_data['group']);
           $q->where('promotion_type',$filter_data['group']);
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



    public function getAllIndexGroups($type)
    {
        try {
            $groups = Group::when($type != null && isset($type) && $type != 'all', function ($query) use ($type) {
                $query->where('type', $type);
            })->withCount([
                'productVariations as items_count',
                'products as items_count'
            ])->orderBy('created_at', 'desc')->paginate(12);


            if (!$groups) {
                throw new Exception('There Is No Groups Available');
            }

            return $groups;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }
    // 

    public function ShowGroup($group_slug)
    {
        $group = Group::where('slug', $group_slug)->with('discounts', 'offers')->withCount('products')->withCount('productVariations')->first();

        if (!$group) {
            throw new Exception('group not found');
        }

        return $group;
    }
    
    public function getLatestOffers(){
        $latest_offers = Group::select(['id','name'])
        ->with([
            'productVariations:id,product_id,color_id,group_id',
            'productVariations.photos'
        ])
        ->where('type','offer')
        ->latest()
        ->get();
    
        // Randomly select two photos from each set of photos
        foreach ($latest_offers as $offer) {
            foreach ($offer->productVariations as $variation) {
                $photos = $variation->photos;
                if ($photos->count() >  0) {
                    $random_photos = $photos->random(2);
                    $variation->setRelation('photos', $random_photos);
                }
            }
        }
        return $latest_offers; 
    }
    
    public function getAllValidGroups($type)
    {
        try {
            //////////////////////////Edit By ELie///////////////////////
            $groups = Group::valid()->paginate(12);
            $groups_fields = [
                'id', 'name', 'slug', 'type', 'promotion_type', 'tag', 'valid', 'image_path', 'image_thumbnail', 'created_at', 'updated_at'
            ];

            return $this->getPaginatedTranslatedFields($groups, $groups_fields);

            //////////////////////////////////////////////////////////////
            $groups = Group::query()->valid();

            if ($type == 'pair') {
                $groups = $groups->where('promotion_type', 'pair')
                    ->with([
                        'product.photos' => function ($query) {
                            $query->where('main_photo', 1);
                        }
                    ])->paginate(8);
            } else {
                $groups = $groups->where('type', '!=', 'pair')->with([
                    'productVariations.photos'
                ])->paginate(8);
            }

            // $groups = Group::where('type', 'offer')->with(['productVariations.photos' => function ($query) {
            //     $query->where('main_photo', 1);
            // }, 'offers', function ($query) use ($type) {
            //     $query->where('type', 'pair');
            // }])->paginate(8);

            if (!$groups) {
                throw new Exception('There Is No Groups Available');
            }

            return $groups;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function getAllGroups($type)
    {
        try {
             $groups = Group::select(['id','name'])->get();

            if (!$groups) {
                throw new Exception('There Is No Groups Available');
            }

            return $groups;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function getAllDiscountGroups()
    {
        $groups = Group::discount()->select('id', 'name')->get();

        if (!$groups) {
            throw new Exception('There Is No Groups Available');
        }

        return $groups;
    }

    public function showDashProducts($group_slug, $sort_data, $filter_data)
    {
        try {
            $slug = Group::where('slug', $group_slug)->first();
            $id = $slug->id;
            $group = Group::query()->find($id);
            if ($group->type == 'offer') {
                $data = $group->load('productVariations')
                    ->productVariations()
                    ->with(['product:id,name', 'group', 'product.photos' => function ($query) {
                        $query->where('main_photo', 1)->first();
                    }])
                    ->withSum('stock_levels as num_stock', 'current_stock_level', function ($query) use ($filter_data) {
                        if ((isset($filter_data['stock_min']) && $filter_data['stock_min'] != null) || (isset($filter_data['stock_max']) && $filter_data['stock_max'] != null)) {
                            $query->whereBetween('current_stock_level', [
                                $filter_data['stock_min'],
                                $filter_data['stock_max']
                            ]);
                        }
                    })
                    ->withSum('order_items as total_sold', 'quantity')
                    ->withSum('order_items as total_profits', 'price');
            } elseif ($group->type == 'discount') {
                $data = $group->load('products')
                    ->products()
                    ->with('group')
                    ->withSum('stocks as num_stock', 'current_stock_level', function ($query) use ($filter_data) {
                        if ((isset($filter_data['stock_min']) && $filter_data['stock_min'] != null) || (isset($filter_data['stock_max']) && $filter_data['stock_max'] != null)) {
                            $query->whereBetween('num_stock', [
                                $filter_data['stock_min'] ?? 0,
                                $filter_data['stock_max'] ?? 100
                            ]);
                        }
                    })
                    ->withSum('order_items as total_sold', 'quantity')
                    ->withSum('order_items as total_profits', 'price');
            }

            // if (!empty($filter_data)) {
            //     $data = $this->applyFilters($data, $filter_data);
            // }

            if ((isset($sort_data['sort_key']) && isset($sort_data['sort_value'])) && ($sort_data['sort_key'] != null && $sort_data['sort_value'] != null)) {
                $data = $this->applySort($data, $sort_data);
            }

            if (!$data) {
                throw new Exception('No Data Found');
            }

            return $data->paginate(8);
        } catch (ModelNotFoundException $th) {
            $model = Str::afterLast($th->getModel(), '\\');
            throw new Exception($model . ' not found', 404);
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
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

    protected function filterByStock($query, $filter_data)
    {
        $query->whereHas('orders', function ($query) use ($max) {
            $query->select(DB::raw('SUM(sum) AS total'))->having('total', '<', $max);
        });
        return $query->whereBetween('num_stock', [$filter_data['stock_min'], $filter_data['stock_max']]);
    }


    protected function filterByOffer($query, $filter_data)
    {


        $query
            ->whereHas('group', function ($offerQuery) use ($filter_data) {
                $offerQuery->whereHas('offers', function ($query) use ($filter_data) {

                    $query->where('type', $filter_data['offer']);
                });
            });

        return $query;
    }



    protected function applySort($query, array $sort_data)
    {

        return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
    }

    // public function getGroupsProducts($type = null)
    // {
    //     try {
    //         if ($type == 'pair') {
    //             $groups = Group::query()->valid()->where('promotion_type', 'Pair');
    //             $data = $groups->with([
    //                 'productVariations.photos' => function ($query) {
    //                     $query->where('main_photo', 1);
    //                 }
    //             ])->paginate(24);
    //         } else {
    //             $groups = Group::where('promotion_type', $type)->with('product_variations');
    //             $products = $groups->with(
    //                 [
    //                     'product_variations',
    //                     'product_variations.notifies',
    //                     'product_variations.size',
    //                     'product_variations.color',
    //                     'product_variations.stock_levels',
    //                     'favourites',
    //                     'pricing',
    //                     'reviews',
    //                     'photos:id,product_id,color_id,path,main_photo',
    //                     'group',
    //                 ]
    //             )->paginate(24);

    //             $data = ProductCollection::make($products);
    //         }

    //         if (!$data) {
    //             throw new Exception('Group Not Found');
    //         }

    //         return $data;
    //     } catch (Exception $th) {
    //         throw new Exception($th->getMessage());
    //     }
    // }

    public function getGroupsProducts()
    {
       
          
            $products =Product::whereHas('group')
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
            )->paginate(24);
            if (!$products) {
                throw new Exception('There Is No Products Available');
            }            
            $products=  ProductCollection::make($products)->sortBy('id')->values()->all();
            return $products;
    }

    public function getGroupProducts(int $group_id, $type)
    {
		
        try {
            $group = Group::findOrFail($group_id)->products;

            return $group;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

 public function getOfferProducts(/*$group_id,*/ $order_item_id,$offer_item_ids)
    {
		
        try {
           // $group = Group::findOrFail($group_id)->products;
			
// Assuming $order_item_id is the ID of the product you want to load
$order_item = Product::findOrFail($order_item_id);

// Load the same relationships as specified in the $products query
$order_item->load([
    'product_variations',
    'product_variations.size',
    'product_variations.color',
    'product_variations.stock_levels',
    'pricing',
    'reviews',
    'photos:id,product_id,color_id,path,main_photo',
    'group',
]);
// Assuming $offer_item_ids is an array of product IDs
$offer_items = Product::whereIn('id', $offer_item_ids)->get();

// Load the same relationships as specified in the $products query
$offer_items->load([
    'product_variations',
    'product_variations.size',
    'product_variations.color',
    'product_variations.stock_levels',
    'pricing',
    'reviews',
    'photos:id,product_id,color_id,path,main_photo',
    'group',
]);

$combined_items = collect([$order_item])->concat($offer_items);

// Reset the keys to sequential integers starting from 0
$fully_indexed_collection = $combined_items->values();

// Sort the collection by pricing value
			 
			
$sorted_offer_collection = $fully_indexed_collection->sortBy(function($offer_item) {
	

    return $offer_item->pricing->value;
});

// Set is_applied to false for all items
$sorted_offer_collection->each(function($offer_item) {
    $offer_item->is_applied = false;
});

// Set is_applied to true for the first item
if ($sorted_offer_collection->isNotEmpty()) {
    $sorted_offer_collection->first()->is_applied = true;
}

// Now, $sorted_offer_collection has the first item with is_applied set to true and the rest with is_applied set to false

		
		

            return $sorted_offer_collection;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }
	
	
	
    public function attachProduct(int $group_id, $product_ids)
    {
        try {
            // return array_count_values($product_ids);
            $group = Group::findOrFail($group_id)->load('offers');
            // return $group->offers()->first()->type;
            if ($group->offers()->first()->type == 'Pair' && array_count_values($product_ids) >= 3) {
                throw new Exception('Products counts exceeds 3 limit');
            }
            // dd("das");
            foreach ($product_ids as $productId) {
                $product = Product::findOrFail($productId);
                if (!$group->products()->sync($productId)) {
                    throw new Exception('Attatching process did not work');
                }
            }
            return $group;
        } catch (ModelNotFoundException $th) {
            $model = Str::afterLast($th->getModel(), '\\');
            throw new Exception($model . ' not found', 404);
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function detachProduct(int $group_id, $slug)
    {
		//return $product_ids;
        try {
            $group = Group::findOrFail($group_id);

            $product = Product::where('slug',$slug)->firstOrFail() ;
                if ($product->group_id == $group_id) {
                    $product->update(['group_id' => null]); 
                $product_variations = ProductVariation::where([['group_id', $group_id], ['product_id', $product->id]])->get();
foreach($product_variations as $product_variation) {
$product_variation->update(['group_id' => null]);
} 
				}
else{
    throw new Exception('incorrect input', 400);
} 
        } catch (ModelNotFoundException $th) {
            $model = Str::afterLast($th->getModel(), '\\');
            throw new Exception($model . ' not found', 404);
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function attachDiscount(int $group_id, $discount_ids)
    {
        try {
            $group = Group::findOrFail($group_id);
            foreach ($discount_ids as $discountId) {
                $discount = Discount::findOrFail($discountId);
                if (!$group->discounts()->sync($discountId)) {
                    throw new Exception('Attatching process did not work');
                }
            }
            return $group;
        } catch (ModelNotFoundException $th) {
            $model = Str::afterLast($th->getModel(), '\\');
            throw new Exception($model . ' not found', 404);
        } catch (Exception $th) {
            throw new Exception('Attatching process did not work');
        }
    }

    public function detachDiscount(int $group_id, $discount_ids)
    {
        try {
            $group = Group::findOrFail($group_id);
            // return $discount_ids;
            foreach ($discount_ids as $discountId) {
                $discount = Discount::findOrFail($discountId);
                if (!$group->discounts()->detach($discountId)) {
                    throw new Exception('Detatching process did not work');
                }
            }
            return $group;
        } catch (ModelNotFoundException $th) {
            $model = Str::afterLast($th->getModel(), '\\');
            throw new Exception($model . ' not found', 404);
        }
    }

    public function storeOffer($data)
    {
        try {
            // $group_data = $data;
            $photo_path =  $this->saveImage($data['image'], 'offers');
            $group = Group::create([
                'name' => [
                    'en' => $data['group_name_en'],
                    'ar' => $data['group_name_ar'],
                ],
                'type' => 'offer',
                'promotion_type' => $data['promotion_type'],
                'number_of_items' => $data['number_of_items'],
                'image_path' => $photo_path,
                'valid'=>1
            ]);
            $offer = Offer::create([
                'group_id'=>$group->id,
                'name' => $data['promotion_name'],
                'type' => $data['promotion_type'],
            ]);

            // $group->offers()->sync($offer->id);
			$fcm_tokens = FcmToken::all()->pluck('fcm_token')->toArray();
			$title = ["ar"=>"تم إضافة عرض جديد",
            "en"=>"New offer was added"];
            $body = ["ar"=>"تم إضافة عرض جديد، قم بمشاهدة تفاصيل العرض واستفد منه الآن",
            "en"=>"New offer was added, Check the offer details!!"];
			foreach($fcm_tokens as $fcm){
				$this->send_notification($fcm,'تم إضافة عرض جديد','New offer was added','تم إضافة عرض جديد، قم بمشاهدة تفاصيل العرض واستفد منه الآن','New offer was added, Check the offer details!!', 'Offers','flutter_app');
			}
			User::chunk(200, function ($users) use($title, $body) {
				foreach ($users as $user) {
					$user->notifications()->create([
						'user_id' => $user->id,
						'type'=> 'Offers', // 2 is to redirect to the offers page
						'title' => $title,
						'body' => $body
					]);
				}
			});
            return $group;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function storeDiscount($data)
    {
        try {
            // $group_data = $data;
            $photo_path =  $this->saveImage($data['image'], 'discounts');
            $group = Group::create([
                'name' => [
                    'en' => $data['group_name_en'],
                    'ar' => $data['group_name_ar'],
                ],
                'type' => 'discount',
                'percentage' => $data['percentage'],
                'promotion_type' => $data['promotion_type'],
                'image_path' => $photo_path,
                'valid'=>1
            ]);


            $start_date = Carbon::parse($data['start_date']);
            $end_date = Carbon::parse($data['end_date']);
            $discount = Discount::create([
                'group_id'=>$group->id,
                'name' => $data['promotion_name'],
                'type' => $data['promotion_type'],
                'percentage' => $data['percentage'],
				'valid' => 1,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);

            // $group->discounts()->sync($discount->id);


            return $group;
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }


    public function updateGroup(array $data, $group_slug)
    {
        $group = Group::where('slug', $group_slug)->first();

        if (!$group) {
            throw new Exception('group is not found');
        }

        if ($data['name_en'] != null) {
            $group->update([
                'name' => [
                    'en' => $data['name_en'],
                ],
            ]);
        }
        if ($data['name_ar'] != null) {
            $group->update([
                'name' => [
                    'ar' => $data['name_ar'],
                ],
            ]);
        }

        if ($group->isDirty()) {
            throw new Exception('group is not updated');
        }

        return $group;
    }

    // updateValidGroup
    public function updateValidGroup(array $data, $group_slug)
    {
        $group = Group::where('slug', $group_slug)->first();

        if (!$group) {
            throw new Exception('group is not found');
        }

        if ($data['valid'] != null) {
            $group->update([

                'valid' => $data['valid'],

            ]);
        }

        if ($group->isDirty()) {
            throw new Exception('group is not updated');
        }

        return $group;
    }

    public function show($group_id)
    {
        $group = Group::find($group_id);

        if (!$group) {
            throw new Exception('group not found');
        }

        return $group;
    }

    public function delete($group_slug)
    {
        $group = Group::where('slug', $group_slug)->first();
        // return  $group->products();

        if ($group->products()->count() === 0 && $group->productVariations()->count() == 0) {
            $group->delete();
        } else {
            throw new Exception('You cant delete this group,Because it contains products');
        }
        return $group;
    }
    public function forceDelete(int $group_id): void
    {
        $group = Group::find($group_id);

        if (!$group) {
            throw new Exception('Section not found');
        }

        $group->forceDelete();
    }




    public function getAllValidDiscounts($section_id, $filter_data, $sort_data, int $pagSize)
    {

      
       $user = auth('sanctum')->user();
		
		if(!$user){
		$user = null;
		
		}
		
		else {
		
		  $user = $user->load(['favourites_products', 'notified_products', 'reviews']);
		}
       // $user = User::with(['favourites_products', 'notified_products', 'reviews'])->find($user_id);
        $notified_products = $user?->notified_products;
        $auth_review = $user?->reviews()->first();
        $favourites_products = $user?->favourites_products->pluck('id')->toArray();

        $products = Product::whereHas('discount')->where('available', 1)
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
            );

		if(isset($filter_data['sort'])){
		   if($filter_data['sort'] == 'price_asc'){
				$products->join('pricings', 'products.id', '=', 'pricings.product_id')
							->select('products.*', 'pricings.value as pricing_value');
				$products = $products->orderBy('pricing_value');
			}elseif($filter_data['sort'] == 'price_desc'){
				$products->join('pricings', 'products.id', '=', 'pricings.product_id')
							->select('products.*', 'pricings.value as pricing_value');
				$products = $products->orderBy('pricing_value','desc');
			}
			$filter_data['sort'] = null;
			
		}

        if (!empty($filter_data)) {
            $products = $this->applyFilters($products, $filter_data);
        }

        //if (!empty($sort_data)) {
        //    $products = $this->applySort($products, $sort_data);
        //}

        if (!$products) {
            throw new Exception('There Is No Products Available');
        }
        $products = $products->get();
        $products =  ProductCollection::make($products, $user, $notified_products, $favourites_products, $auth_review)->values()->all();;
        $categories_by_section = Category::where('section_id',$section_id)->get()->pluck('id');
		$colors = Color::select('id', 'hex_code')->get();
        $sizes = Size::select('id', 'value')->get();
		$subs = SubCategory::whereIn('id',$categories_by_section)->select('id', 'name')->get();
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

    public function getAllValidOffers($filter_data = [], $sort_data = [], int $pagSize, $group_id = null)
    {
		//$user = auth('sanctum')->user()->load(['favourites_products', 'notified_products', 'reviews']);
        $products = Product::whereHas('group', function ($query) use ($group_id) {
            $query->where('type', 'offer');
			if($group_id != null){
				$query->where('group_id', $group_id);
			}
        })->where('available', 1)
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
            );
		
        
      //  return $user = auth('sanctum')->user();
       // $user = User::with(['favourites_products', 'notified_products', 'reviews'])->find($user_id);
      //  $notified_products = $user->notified_products;
      //  $auth_review = $user->reviews()->first();
      //  $favourites_products = $user->favourites_products->pluck('id')->toArray();
        
      if(isset($filter_data['sort'])){
		   if($filter_data['sort'] == 'price_asc'){
				$products->join('pricings', 'products.id', '=', 'pricings.product_id')
							->select('products.*', 'pricings.value as pricing_value');
				$products = $products->orderBy('pricing_value');
			}elseif($filter_data['sort'] == 'price_desc'){
				$products->join('pricings', 'products.id', '=', 'pricings.product_id')
							->select('products.*', 'pricings.value as pricing_value');
				$products = $products->orderBy('pricing_value','desc');
			}
			$filter_data['sort'] = null;
			
		}
		
        if (!$products) {
            throw new Exception('There Is No Products Available');
        }


       // $user_reviews = User::find(1)->reviews()->first();
		
		if (!empty($filter_data['group'])) {
            $products = $products->whereHas('group',function($q) use($filter_data){  
      	    $q->where('slug',$filter_data['group']);
       		});
        }
		$filter_data['group'] = null;
		
		if (!empty($filter_data['sub_category'])) {
            $filter_data['flutter'] = $filter_data['sub_category'];
        }
		$filter_data['sub_category'] = null;
		
        if (!empty($filter_data)) {
			//return $filter_data;
            $products = $this->applyFilters($products, $filter_data);
	
		
        }

        if (!empty($sort_data)) {
			//return $sort_data;
            $products = $this->applySort($products, $sort_data);
        }
		
        //if ($pagSize > $products->count()) {

         //   if ($products->count() == 0) {
         //       throw new Exception('There Is No Products Available');
         //   }

            //  $page_size = 1;

         //   $products = $products->simplePaginate($pagSize);
        //} else {


        //    $products = $products->paginate($pagSize);
       // }
         $products = $products->get();

 
      //  $products = $products->paginate($pagSize);
        $products =  ProductCollection::make($products)->values()->all();;
        $colors = Color::select('id', 'hex_code')->get();
        $sizes = Size::select('id', 'value')->get();
        $subs = Group::select('id', 'name')->where('type','offer')->get();
        $categories = Category::select('id', 'name')->get();
        return [
            'products' => $products,
            'colors' => $colors,
            'sizes' => $sizes,
            'subs' => $subs,
            'categories' => $categories
        ];
    }
    public function getTypes($type)
    {


        $types= Group::select('id','name','slug','promotion_type','type', 'image_path' )->where('type',$type)->orderBy('created_at', 'desc')->get();
        return $types;
    }
    // getTypes



}