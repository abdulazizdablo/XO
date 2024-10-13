<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\Section;
use App\Services\SettingService;
use App\Http\Requests\Setting\CreateSettingRequest;
// use App\Traits\CloudinaryTrait;
use App\Traits\PhotoTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Enums\Sections;
use App\Enums\SectionHelper;



class AppSettingController extends Controller
{
    use PhotoTrait;

    public function __construct(protected SettingService $settingService)
    {
    }

	
	
	
	
	
	
	  public function frequentQuestions(Request $request)
  {
    if ($request->key == 'frequent_questions') {
      // Attempt to find the setting with the key 'frequent_questions'
      $setting = Setting::where('key', 'frequent_questions')->first();

      if ($setting) {
        // If the setting exists, update it with the new values

        $new_value = json_decode($request->value, true);



        if ($request->hasFile('faq_photo')) {
          $photo_path = $this->saveImage($request->file('faq_photo'), 'photo', 'faq_photo');
          $updated_value['navBar']['link'] = $photo_path;
        }


        /*  $setting->update([
            'value' => json_encode([
              "en" => [
                "question1" => "asdw",
                "answer1" => "asdw",
                "question2" => "Asdwa",
                "answer2" => "asddw",
                "question3" => "asdw",
                "answer3" => "asdw"
              ],
              "ar" => [
                "question1" => "مرحبا",
                "answer1" => "مرحبا",
                "question2" => "مرحبا بك",
                "answer2" => "مرحبا بك",
                "question3" => "مرحبا",
                "answer3" => "مرحبا"
              ]
            ])
          ]);

          */
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Frequent Questions has been updated successfully',
        ], Response::HTTP_OK);
      } else {



        $new_value = json_decode($request->value, true);


        if ($request->hasFile('how_to_buy_online')) {
          $photo_path = $this->saveImage($request->file('how_to_buy_online'), 'photo', 'faq_photo');
          $new_value['en'][0]['faq_photo'] = $photo_path;
          $new_value['ar'][0]['faq_photo'] = $photo_path;
        }

        if ($request->hasFile('about_my_order')) {
          $photo_path = $this->saveImage($request->file('about_my_order'), 'photo', 'faq_photo');
          $new_value['en'][1]['faq_photo'] = $photo_path;
          $new_value['ar'][1]['faq_photo'] = $photo_path;
        }
        $value = json_encode($new_value, true);
        $setting = new Setting();
        $setting->key = $request->key;
        $setting->value = $value;
        $setting->save();
        //$image_photos = array_merge(array_column($new_value['en']),array_column($new_value['ar']));

        // If the setting does not exist, create a new one
        // $setting = $this->settingService->createSetting($request->all());
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Advertisement Tape has been created successfully',
        ], Response::HTTP_CREATED);
      }
    }
  }

    public function index(Request $request)
    {
        $key = request('key');
        $section = request('section');
		$front = request('front');
		
				  Cache::remember($key, 60 * 300, function () use ($key) {
            return     AppSetting::where('key', $key)->firstOrFail();
        });
		
	 $appSetting = cache($key);
		
		
			$appSetting->value = json_decode($appSetting->value,true);
		//$appSetting->value = json_decode($appSetting->value);
		if(isset($front) && $front!= ''){
		
			

		return response()->success($appSetting,200);
		
		}
		
 // $appSetting =  AppSetting::where('key', $key)->first();
		//$appSetting->value = json_decode($appSetting->value,true);
		//return $appSetting;
		//Cache::forget($key);
		 $value = $appSetting->value;
		
		if($appSetting->key == 'sectionPhotos'){
			
			if(!isset($section) || empty($section)){
			
					return response()->success($value,200);

			
			}
			
		return response()->success($value[$section],200);
		
		
		}
		//$value = json_decode($value,true);
		  if (isset($value['minimum_version'])) {


               // $value =  collect($value);
	//$value = json_decode($value, true); // The second parameter 'true' converts the JSON object into an associative array

    // Now you can work with $data as an array
			
			return response()->success([
				
				'key' => 'versionNumber',
			  'minimum_version'=> $value['minimum_version'],
   'optional_version' => $value['optional_version']
			
			],200
  );

         

                    return response()->success($appSetting->value,  200);
                
		  }
		
		else {
		/*$value = json_decode($value, true); // The second parameter 'true' converts the JSON object into an associative array

    // Now you can work with $data as an array
			
			return response()->success([
			  'minimum_version'=> $value['minimum_version'],
   'optional_version' => $value['optional_version']
			
			],200
  );
			
			*/
			
			if($appSetting->key == 'GiftCardDetails'){
			$appSetting =  AppSetting::where('key','GiftCardDetails')->first();
			$value = json_decode($appSetting->value,true);

		
			
			return response()->success( ['value' => collect($value)->except('balance'),
				   'balance' =>$value['balance']],200 );
			
		}
			}
    /*  Cache::forget($key);
 if (is_array($value)) {


                $value =  collect($value);


                if ($value->has('minimum_version')) {

                    return response()->success($value,  200);
                }*/

     /*   Cache::remember($key, 60 * 300, function () use ($key) {
            return     AppSetting::where('key', $key)->first();
        });
*/

        if (cache($key)) {
            $value = cache($key)->value;

		
            if(isset($section )){
                $value = json_decode($value, true);
                $value = $value[$section];

                return response()->success($value,200);
    
            }
            // If the value is a JSON string, decode it to an array
            if (is_string($value)) {
                $value = json_decode($value, true);
	
            }
            // If the value is an array, flatten it
            if (is_array($value)) {


                $value =  collect($value);


                if ($value->has('minimum_version')) {

                    return response()->success($value,  200);
                }


                foreach ($value as $key => $item) {
                    if (isset($item['link'])) {
                        $value[$key] = $item['link'];
                        /*   } elseif (isset($item['minimum_version'])) {
                $value[$key] = $item['minimum_version'];
                if (isset($item['optional_version'])) {
                    $value[$key +  1] = $item['optional_version'];
                }
            }*/
                    }


                    /*  $value->each(function($item,$key){
                   
               //   $dynamicKey = 'image' . $key+1;
                  
                  $item= $item->flatten();
                   
                   
               });
                
              //  $value = $value->flatten();
            }
            */

                    /*  $value->each(function($item){
                
                
                $item->flatten();
                
            });*/
                    return response()->success($value,  200);
                }

                return response()->error('Setting not found',  404);
            }
        }
    }


	
	public function generalDetailsApp(Request $request){
	
	   $details = AppSetting::whereIn('key',['safeShipping','freeShipping','measurment','compositionAndCare','return_policy'])->get();
		$detailes = $details->map(function($item){
			
		return 
		$item->value = json_decode($item->value,true)
		
		;
		
			
			
			
		});
		

		
	$detailsArray = [
    'safeShipping' => $details->where('key', 'safeShipping')->first()?->value,
    'freeShipping' => $details->where('key', 'freeShipping')->first()?->value,
    'measurment' => $details->where('key', 'measurment')->first()?->value,
    'compositionAndCare' => $details->where('key', 'compositionAndCare')->first()?->value,
    'return_policy' => $details->where('key', 'return_policy')->first()?->value,
];

// Assuming $details is a collection and you want to return an array of decoded JSON values based on certain keys.
// Note: This assumes that each detail has a 'value' attribute containing JSON-encoded data.

		
		return response()->success([
    'safeShipping' => $details->where('key', 'safeShipping')->first()?->value,
    'freeShipping' => $details->where('key', 'freeShipping')->first()?->value,
    'measurment' => $details->where('key', 'measurment')->first()?->value,
    'compositionAndCare' => $details->where('key', 'compositionAndCare')->first()?->value,
    'return_policy' => $details->where('key', 'return_policy')->first()?->value,
] ,200);
	
	}

    public function sectionPhotos(Request $request)
    {


        $validate = Validator::make(
            $request->only('key', 'value', 'men','women','home','kids'),
            [
              'key' => 'required|string|max:40',
              'value' => 'required|json',
              //'section' => 'required|string|in:men,women,kids,home',
              'kids' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
              'women' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
              'home' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
              'men' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ],
      
          );
      
          if ($validate->fails()) {
            return response()->error(
      
              $validate->errors(),
              422
            );
          }

          if ($request->key == 'sectionPhotos') {
            // Attempt to find an existing AppSetting with the key 'sectionPhotos'
            $appSetting = AppSetting::where('key', 'sectionPhotos')->firstOrFail();
        
            // Initialize an array to hold the new values
            $newValues = [];
        
            // Check if each file has been uploaded and process accordingly
            $sections = ['Kids', 'Men', 'Home', 'Women'];
            foreach ($sections as $section) {
                if ($request->hasFile($section)) {
                    $imagePath = $this->saveImage($request->file($section),'photo', $section);
                    if ($imagePath) {
                        // If the image is successfully saved, add it to the new values array
                        $newValues[$section] = $imagePath;
                    }
                }
            }
        
            // If there are new values to update, decode the existing value and merge with new values
            if (!empty($newValues)) {
                if ($appSetting) {
                    // If the AppSetting exists, update it
                    $existingValue = json_decode($appSetting->value, true);
                    $updatedValue = array_merge($existingValue, $newValues);
                    $appSetting->value = json_encode($updatedValue);
                    $appSetting->save();
                } else {
                    // If the AppSetting does not exist, create a new one
                    $appSetting = new AppSetting();
                    $appSetting->key = 'sectionPhotos';
                    $appSetting->value = json_encode($newValues);
                    $appSetting->save();
                }
            }
        
            // Return a success response with the AppSetting
            return response()->success($appSetting, 200);
        }
        
            
        }
    
    public function locationPhotos(CreateSettingRequest $request)
    {
        if ($request->key == 'locationPhotos') {
            $appSetting = AppSetting::where('key', 'locationPhotos')->first();

            if ($appSetting) {
				$decoded_value = json_decode($appSetting->value,true);
				
				if($request->hasFile('image1')){
				$image1 = $this->saveImage($request->file('image1'),'image1','locationPhotos');
					$decoded_value['image1'] = $image1;
				
				}
						if($request->hasFile('image2')){
				$image1 = $this->saveImage($request->file('image2'),'image2','locationPhotos');
						$decoded_value['image2'] = $image1;
				
				}
						if($request->hasFile('image3')){
				$image1 = $this->saveImage($request->file('image3'),'image3','locationPhotos');
					$decoded_value['image3'] = $image1;
				
				}
				
				$new_value = json_encode($decoded_value);
				
				$appSetting->value = $new_value;
				$appSetting->save();
				
               /* $appSetting->update([
                    'value' => json_encode([
                        'image1' => $request->value['image1'],
                        'image2' => $request->value['image2'],
                        'image3' => $request->value['image3']
                    ])
                ]);
*/
                return response()->success($appSetting,  200);
            } else {
				$appSetting = new AppSetting();
					$decoded_value =$request->value;
				if($request->hasFile('image1')){
				$image1 = $this->saveImage($request->file('image1'),'image1','locationPhotos');
					$decoded_value['image1'] = $image1;
				
				}
						if($request->hasFile('image2')){
				$image1 = $this->saveImage($request->file('image2'),'image2','locationPhotos');
						$decoded_value['image2'] = $image2;
				
				}
						if($request->hasFile('image3')){
				$image1 = $this->saveImage($request->file('image3'),'image3','locationPhotos');
					$decoded_value['image2'] = $image2;
				
				}
				$new_value = json_encode($decoded_value);
				$appSetting->key = $request->key;
				$appSetting->value = $new_value;
				$appSetting->save();
				
                //$setting = $this->settingService->createAppSetting($request->validated());
                return response()->success([$appSetting, 'message' => 'Settings for Location Photos have been created successfully'],  200);
            }
        }

        // return response()->error('Invalid key',  400);
    }
	
	
	
	
	

public function giftCardDetails(Request $request){
    if($request->key == 'GiftCardDetails'){
        $appSetting = AppSetting::where('key', 'GiftCardDetails')->first();
	$decoded_value = 	json_decode($request->value,true);
		
		//	$decoded_value = json_decode($request->value,true);

$value	= 	json_decode($appSetting?->value,true)  ;
		    if($appSetting){
			

		$value	['balance']['min'] =  $decoded_value['balance']['min'];
			
					$value['balance']['max'] = $decoded_value['balance']['max'];
				$value['balance']['step'] = $decoded_value['balance']['step'];
				
			
	$value['balance']['price1'] = $decoded_value['balance']['price1'];
		$value['balance']['price2'] = $decoded_value['balance']['price2'];			
			$value['balance']['price3'] = $decoded_value['balance']['price3'];
		
$value['balance']['price4'] = $decoded_value['balance']['price4'];
    
            // Assuming you want to update the value with the new data from the request
			
				if($request->hasFile('banner1')){
				$image1 = $this->saveImage($request->file('banner1'),'banner1','banners');
					$decoded_value['banner1'] = $image1;
				
				}
						if($request->hasFile('banner2')){
				$image1 = $this->saveImage($request->file('banner2'),'banner2','banners');
						$decoded_value['banner2'] = $image1;
				
				}
						if($request->hasFile('banner3')){
				$image1 = $this->saveImage($request->file('banner3'),'banner3','banners');
					$decoded_value['banner3'] = $image1;
				
				}
			
			
		
		
		
			
            $decoded_value = json_encode($decoded_value, true);
				
	

            // Correctly encode the value before saving
            $appSetting->value =   $decoded_value;
            $appSetting->save();
			return response()->success(['appSetting' =>$appSetting,'message' => 'Gift Card Details has been updated successfully'],201);

        } else {
			
				$decoded_value = json_decode($request->value,true);
				
			
            // If the record doesn't exist, you need to create it.
            // However, your current logic tries to update a non-existent record, which won't work.
            // Instead, create a new record with the provided data.
				if($request->hasFile('banner1')){
				$image1 = $this->saveImage($request->file('banner1'),'banner1','banners');
					$decoded_value['banner1'] = $image1;
				
				}
						if($request->hasFile('banner2')){
				$image1 = $this->saveImage($request->file('banner2'),'banner2','banners');
						$decoded_value['banner2'] = $image1;
				
				}
						if($request->hasFile('banner3')){
				$image1 = $this->saveImage($request->file('banner3'),'banner3','banners');
					$decoded_value['banner3'] = $image1;
				
				}
				
			
            $decoded_value = json_encode($decoded_value, true);

            // Correctly encode the value before saving
            $appSetting = new AppSetting;
            $appSetting->key = 'GiftCardDetails';
            $appSetting->value = $decoded_value ;
            $appSetting->save();
			
			return response()->success(['appSetting' => $appSetting,'message' => 'Gift Card Details has been created successfully'],201);
        }
    }
}

	
	
	public function offers(Request $request){
		
	 if ($request->key == 'offers') {
            $appSetting = AppSetting::where('key', 'offers')->first();

            if ($appSetting) {
				$decoded_value = json_decode($appSetting->value,true);
				
				if($request->hasFile('women')){
				$image1 = $this->saveImage($request->file('women'),'women','offers');
					$decoded_value['women'] = $image1;
				
				}
						if($request->hasFile('men')){
				$image1 = $this->saveImage($request->file('men'),'men','offers');
						$decoded_value['men'] = $image1;
				
				}
						if($request->hasFile('kids')){
				$image1 = $this->saveImage($request->file('kids'),'kids','offers');
					$decoded_value['kids'] = $image1;
				
				}
				
						if($request->hasFile('home')){
				$image1 = $this->saveImage($request->file('home'),'home','offers');
					$decoded_value['home'] = $image1;
				
				}
				
				$new_value = json_encode($decoded_value);
				$appSetting->value = $new_value;
				$appSetting->save();
				
               /* $appSetting->update([
                    'value' => json_encode([
                        'image1' => $request->value['image1'],
                        'image2' => $request->value['image2'],
                        'image3' => $request->value['image3']
                    ])
                ]);
*/
                return response()->success($appSetting,  200);
            } else {
				$appSetting = new AppSetting();
				$appSetting->key = 'offers';
					
				if($request->hasFile('women')){
				$image1 = $this->saveImage($request->file('women'),'women','offers');
					$decoded_value['women'] = $image1;
				
				}
						if($request->hasFile('men')){
				$image1 = $this->saveImage($request->file('men'),'men','offers');
						$decoded_value['men'] = $image1;
				
				}
						if($request->hasFile('kids')){
				$image1 = $this->saveImage($request->file('kids'),'kids','offers');
					$decoded_value['kids'] = $image1;
				
				}
				
							if($request->hasFile('home')){
				$image1 = $this->saveImage($request->file('home'),'home','offers');
					$decoded_value['home'] = $image1;
				
				}
				$new_value = json_encode($decoded_value);
				$appSetting->value = $new_value;
				$appSetting->save();
				
                //$setting = $this->settingService->createAppSetting($request->validated());
                return response()->success(['app_setting' => $appSetting, 'message' => 'Settings for Location Photos have been created successfully'],  200);
            }
        }
	
	}
	
	
	
	public function newIn(Request $request){
		
	 if ($request->key == 'newIn') {
            $appSetting = AppSetting::where('key', 'newIn')->first();

            if ($appSetting) {
				$decoded_value = json_decode($appSetting->value,true);
				
				if($request->hasFile('women')){
				$image1 = $this->saveImage($request->file('women'),'women','newIn');
					$decoded_value['women'] = $image1;
				
				}
						if($request->hasFile('men')){
				$image1 = $this->saveImage($request->file('men'),'men','newIn');
						$decoded_value['men'] = $image1;
				
				}
						if($request->hasFile('kids')){
				$image1 = $this->saveImage($request->file('kids'),'kids','newIn');
					$decoded_value['kids'] = $image1;
				
				}
				
						if($request->hasFile('home')){
				$image1 = $this->saveImage($request->file('home'),'home','newIn');
					$decoded_value['home'] = $image1;
				
				}
				
				$new_value = json_encode($decoded_value);
				$appSetting->value = $new_value;
				$appSetting->save();
				
               /* $appSetting->update([
                    'value' => json_encode([
                        'image1' => $request->value['image1'],
                        'image2' => $request->value['image2'],
                        'image3' => $request->value['image3']
                    ])
                ]);
*/
                return response()->success($appSetting,  200);
            } else {
				$appSetting = new AppSetting();
				$appSetting->key = 'newIn';
					
				if($request->hasFile('women')){
				$image1 = $this->saveImage($request->file('women'),'women','newIn');
					$decoded_value['women'] = $image1;
				
				}
						if($request->hasFile('men')){
				$image1 = $this->saveImage($request->file('men'),'men','newIn');
						$decoded_value['men'] = $image1;
				
				}
						if($request->hasFile('kids')){
				$image1 = $this->saveImage($request->file('kids'),'kids','newIn');
					$decoded_value['kids'] = $image1;
				
				}
				
							if($request->hasFile('home')){
				$image1 = $this->saveImage($request->file('home'),'home','newIn');
					$decoded_value['home'] = $image1;
				
				}
				$new_value = json_encode($decoded_value);
				$appSetting->value = $new_value;
				$appSetting->save();
				
                //$setting = $this->settingService->createAppSetting($request->validated());
                return response()->success($appSetting,  200);
            }
        }
	
	}
	
	
	
	public function flashSale(Request $request){
	
	 if ($request->key == 'flashSale') {
            $appSetting = AppSetting::where('key', 'flashSale')->first();

            if ($appSetting) {
				$decoded_value = json_decode($appSetting->value,true);
				
				if($request->hasFile('women')){
				$image1 = $this->saveImage($request->file('women'),'women','flash');
					$decoded_value['women'] = $image1;
				
				}
						if($request->hasFile('men')){
				$image1 = $this->saveImage($request->file('men'),'men','flash');
						$decoded_value['men'] = $image1;
				
				}
						if($request->hasFile('kids')){
				$image1 = $this->saveImage($request->file('kids'),'kids','flash');
					$decoded_value['kids'] = $image1;
				
				}
				
						if($request->hasFile('home')){
				$image1 = $this->saveImage($request->file('home'),'home','flash');
					$decoded_value['home'] = $image1;
				
				}
				
				$new_value = json_encode($decoded_value);
				$appSetting->value = $new_value;
				$appSetting->save();
				
               /* $appSetting->update([
                    'value' => json_encode([
                        'image1' => $request->value['image1'],
                        'image2' => $request->value['image2'],
                        'image3' => $request->value['image3']
                    ])
                ]);
*/
                return response()->success($appSetting,  200);
            } else {
				$appSetting = new AppSetting();
				$appSetting->key = 'flashSale';
					
				if($request->hasFile('women')){
				$image1 = $this->saveImage($request->file('women'),'women','flash');
					$decoded_value['women'] = $image1;
				
				}
						if($request->hasFile('men')){
				$image1 = $this->saveImage($request->file('men'),'men','flash');
						$decoded_value['men'] = $image1;
				
				}
						if($request->hasFile('kids')){
				$image1 = $this->saveImage($request->file('kids'),'kids','flash');
					$decoded_value['kids'] = $image1;
				
				}
				
							if($request->hasFile('home')){
				$image1 = $this->saveImage($request->file('home'),'home','flash');
					$decoded_value['home'] = $image1;
				
				}
				$new_value = json_encode($decoded_value);
				$appSetting->value = $new_value;
				$appSetting->save();
				
                //$setting = $this->settingService->createAppSetting($request->validated());
                return response()->success([$appSetting, 'message' => 'Settings for Location Photos have been created successfully'],  201);
            }
        }
	
	
	}
	
	public function sectionCategories(){
		$app_setting = AppSetting::where('key',	'sectionPhotos')->first();
		$images = json_decode($app_setting->value);
		//return $sections->men;
		$sections = Section::with('categories')->get();
		
		return response()->success($sections,200);
		
	}
		public function homePagePhotos(Request $request){
		
		$section = $request->section;
		$app_setting = AppSetting::where('key',	$section)->firstOrFail();
			
			if(!$app_setting){
			$app_setting =  AppSetting::whereIn('key',	['flashSale','newIn','offerPhotos'])->get();
				
				return response()->success($app_setting,200); 
			}
			
			return response()->success($app_setting,200);
		
		}
		

	
	
    public function offerPhotos(CreateSettingRequest $request)
    {
        if ($request->key == 'offerPhotos') {
            $appSetting = AppSetting::where('key', 'offerPhotos')->first();
            $iterator = 1;
            if ($appSetting) {

                foreach ($appSetting->value as $key => $item) {


                    json_encode(['image' . $iterator => $item['image' . $iterator]]);

                    $iterator++;
                }

                $appSetting->save();


                /*    $appSetting->update([
                    'value' => json_encode([
                        'image1' => $request->value->image1,
                        'image2' => $request->value->image2,
                        'image3' => $request->value->image3,
                         'image4' => $request->value->image4
                    ])
                ]);
*/
                return response()->success($appSetting,  200);
            } else {
                $setting = $this->settingService->createAppSetting($request->validated());
                return response()->success([$setting, 'message' => 'Settings for Ban User Notifications have been created successfully'],  200);
            }
        }
    }
	
	
	public function categoriesSectionPhotos(Request $request){
	

        $validate = Validator::make(
            $request->only('key', 'value', 'men','women','home','kids'),
            [
              'key' => 'required|string|max:40',
              //'value' => 'required|json',
              //'section' => 'required|string|in:men,women,kids,home',
              'kids' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
              'women' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
              'home' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
              'men' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
            ],
      
          );
      
          if ($validate->fails()) {
            return response()->error(
      
              $validate->errors(),
              422
            );
          }

          if ($request->key == 'categoriesSectionPhotos') {
            // Attempt to find an existing AppSetting with the key 'sectionPhotos'
            $appSetting = AppSetting::where('key', 'categoriesSectionPhotos')->first();
        
            // Initialize an array to hold the new values
            $newValues = [];
        
            // Check if each file has been uploaded and process accordingly
            $sections = ['kids', 'men', 'home', 'women'];
            foreach ($sections as $section) {
                if ($request->hasFile($section)) {
                    $imagePath = $this->saveImage($request->file($section),'photo', $section);
                    if ($imagePath) {
                        // If the image is successfully saved, add it to the new values array
                        $newValues[$section] = $imagePath;
                    }
                }
            }
        
            // If there are new values to update, decode the existing value and merge with new values
            if (!empty($newValues)) {
                if ($appSetting) {
                    // If the AppSetting exists, update it
                    $existingValue = json_decode($appSetting->value, true);
                    $updatedValue = array_merge($existingValue, $newValues);
                    $appSetting->value = json_encode($updatedValue);
                    $appSetting->save();
                } else {
                    // If the AppSetting does not exist, create a new one
                    $appSetting = new AppSetting();
                    $appSetting->key = 'categoriesSectionPhotos';
                    $appSetting->value = json_encode($newValues);
                    $appSetting->save();
                }
            }
        
            // Return a success response with the AppSetting
            return response()->success($appSetting, 200);
        }
		
		
		
	
	}
	
	
	public function freeShipping(Request $request){
	
	if($request->key == 'freeShipping'){
	
	$appSetting = AppSetting::where('key',$request->key)->first();
		
		if($appSetting){
		$appSetting->update(['value' => json_encode($request->value)]);
			return response()->success(['app_setting' => $appSetting, 'message' => 'App Setting Updated Successfully'],201);

		
		}
		else {
		$appSetting = new AppSetting();
			$appSetting->key = $request->key;
			$appSetting->value = json_encode($request->value);
			$appSetting->save();
				return response()->success(['app_setting' => $appSetting, 'message' => 'App Setting Created Successfully'],201);

		}
	
	}
	
	
	}
	
	public function measurment(Request $request){
	
	
		
			if($request->key == 'measurment'){
	
	$appSetting = AppSetting::where('key',$request->key)->first();
		
		if($appSetting){
		$appSetting->update(['value' => json_encode($request->value)]);
			return response()->success(['app_setting' => $appSetting, 'message' => 'App Setting Updated Successfully'],201);

		
		}
		else {
		$appSetting = new AppSetting();
			$appSetting->key = $request->key;
			$appSetting->value = json_encode($request->value);
			$appSetting->save();
				return response()->success(['app_setting' => $appSetting, 'message' => 'App Setting Created Successfully'],201);

		}
	
	}
		
	
	}
	
	
	
	
	
	public function compositionAndCare(Request $request){
	
	
		
			if($request->key == 'compositionAndCare'){
	
	$appSetting = AppSetting::where('key',$request->key)->first();
		
		if($appSetting){
		$appSetting->update(['value' => json_encode($request->value)]);
			return response()->success(['app_setting' => $appSetting, 'message' => 'App Setting Updated Successfully'],201);

		
		}
		else {
		$appSetting = new AppSetting();
			$appSetting->key = $request->key;
			$appSetting->value = json_encode($request->value);
			$appSetting->save();
				return response()->success(['app_setting' => $appSetting, 'message' => 'App Setting Created Successfully'],201);

		}
	
	}
		
	
	}
	
	
	
	
	public function safeShipping(Request $request){
	
	if($request->key == 'safeShipping'){
	
	$appSetting = AppSetting::where('key',$request->key)->first();
		
		if($appSetting){
		$appSetting->update(['value' => $request->value]);
				return response()->success(['app_setting' => $appSetting, 'message' => 'App Setting Updated Successfully'],201);

		
		}
		else {
		$appSetting = new AppSetting();
			$appSetting->key = $request->key;
						$appSetting->value = json_encode($request->value);

			$appSetting->save();
		return response()->success(['app_setting' => $appSetting, 'message' => 'App Setting Created Successfully'],201);
		}
	
	}
	
	
	}

	
	


    public function versionNumber(CreateSettingRequest $request)
    {



        if ($request->key == 'versionNumber') {
            $appSetting = AppSetting::where('key', 'versionNumber')->first();

            if ($appSetting) {
                // Decode the existing value to an object
                $appSettingValue = json_decode($appSetting->value,true);
// Check if $value is a JSON string and decode it; otherwise, assume it's already an array
$data = is_string($request->value)? json_decode($request->value, true) :$request->value;

// Now, $data is guaranteed to be an array, and you can proceed with your logic
// Assuming $value could be either an array or a JSON string


// Explicitly setting minimum_version and optional_version to an empty string if not set or empty
$data['minimum_version'] = isset($data['minimum_version']) &&!empty($data['minimum_version'])? $data['minimum_version'] : '';
$data['optional_version'] = isset($data['optional_version']) &&!empty($data['optional_version'])? $data['optional_version'] : '';

// Proceeding with the rest of your logic
if (
    isset($data['minimum_version']) &&
    isset($data['optional_version'])
) {
	
	$appSetting->value = json_encode($data,true);
	$appSetting->save();
	
    return response()->success([
        'key' => 'versionNumber',
        'minimum_version' => $data['minimum_version'],
        'optional_version' => $data['optional_version']
    ], 200);
}

				
			
				
            } else {
                // If the setting does not exist, create a new one
                $setting = $this->settingService->createAppSetting($request->validated());
				
                return response()->success([$setting, 'message' => 'Settings for Ban User Notifications have been created successfully'],  200);
            }
        }
    }

    public function getAppSections(Request $request)
    {
        //$key = $request->key;
		
$section_id = $request->section_id;
		
	//	$section_id = $request->section_id;
		
		$section_name = strtolower(SectionHelper::getIdNameFromSections($section_id));

// Call the static method via the instance (still not necessary but shown for clarity)
//$name = $sectionHelperInstance::getIdName($section_id);
		
		
        $sections =  ['offers', 'newIn', 'flash_sales'];
		
        $set = AppSetting::whereIn('key', $sections)->pluck('value','key');
		
		
$images = $set->map(function ($item,$key) use($section_name) {
	

    // Decode the JSON string into an associative array
    $decodedValue = json_decode($item, true);

	

    // Check if the decoding was successful and if the 'women' key exists
    if (is_array($decodedValue) && isset($decodedValue[$section_name])) {
        // Return a new associative array with the original 'key' and the 'women' value
        return [$key => $decodedValue[$section_name]];
    } 
	
   if (is_array($decodedValue) && isset($decodedValue[$section_name])) {
        // Return a new associative array with the original 'key' and the 'women' value
        return [$key => $decodedValue[$section_name]];
    }
	   if (is_array($decodedValue) && isset($decodedValue[$section_name])) {
        // Return a new associative array with the original 'key' and the 'women' value
        return [$key => $decodedValue[$section_name]];
    }
	   if (is_array($decodedValue) && isset($decodedValue[$section_name])) {
        // Return a new associative array with the original 'key' and the 'women' value
        return [$key => $decodedValue[$section_name]];
    }
	
else {
        // Handle cases where 'women' does not exist or the JSON could not be decoded
        // For demonstration, we'll return null for these cases
        return null;
    }
})->filter()->values(); // Filter out any null values resulting from missing 'women'

		
		$result = $images->flatMap(function ($item) {
    return $item;
})->toArray();
		

        return /*$set->key => json_decode($set->value)->$section*/response()->success($result,200);
    }

    public function app_sections(CreateSettingRequest $request)
    {
        if (in_array($request->key, ['offers', 'new_in', 'flash_sales'])) {
            $setting = AppSetting::where('key', $request->key)->first();
            if ($setting) {
                // Get the current settings value as an array
                $currentSettings = json_decode($setting->value, true);

                // Check if the images are present in the request and save them if they are
                $menImage = isset($request->value['men']) ? $this->saveImage($request->value['men'], 'photo' ,'settings') : $currentSettings['men'] ?? '';
                $womenImage = isset($request->value['women']) ? $this->saveImage($request->value['women'], 'photo' , 'settings') : $currentSettings['women'] ?? '';
                $kidsImage = isset($request->value['kids']) ? $this->saveImage($request->value['kids'], 'photo' , 'settings') : $currentSettings['kids'] ?? '';
                $homeImage = isset($request->value['home']) ? $this->saveImage($request->value['home'], 'photo' , 'settings') : $currentSettings['home'] ?? '';

                // Update the settings with the new or existing images
                $setting->update([
                    'value' => json_encode([
                        'men' => $menImage,
                        'women' => $womenImage,
                        'kids' => $kidsImage,
                        'home' => $homeImage,
                    ])
                ]);

                return response()->success([$setting, 'message' => 'Settings for App section has been updated successfully'], 200);
            }



            $setting = $this->settingService->createAppSection($request->validated());
            return response()->success([$setting, 'message' => 'Settings for App section has been created successfully'], 200);
        }
    }
}
