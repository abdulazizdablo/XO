<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Setting\CreateSettingRequest;
use App\Http\Requests\Setting\PhotosSettingRequest;
//use App\Traits\CloudinaryTrait;
use App\Traits\PhotoTrait;
use Illuminate\Support\Facades\Cache;
use App\Models\Poligon;
use App\Enums\Roles;

class SettingController extends Controller
{
  //use CloudinaryTrait;
  use PhotoTrait;	
  public function __construct(
    protected SettingService $settingService
  ) {
  }


  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\Setting  $Setting
   * @return \Illuminate\Http\Response
   */
  public function show()
  {
  }
  public function getSetting()
  {
    try {
      $setting_key = request('key');

      Cache::remember($setting_key, 60 * 300, function () use ($setting_key) {
        return $this->settingService->getSetting($setting_key);
      });

      return response()->success(
        cache($setting_key),
        Response::HTTP_OK
      );
    } catch (InvalidArgumentException $e) {
      return response()->error(
        $e->getMessage(),
        Response::HTTP_NOT_FOUND
      );
    }
  }

  /*  public function categories(CreateSettingRequest $request)
    {
        if ($request->key == 'categories') {
     ]);
        
        
    }
            
            $setting = $this->settingService->createSetting($request->validated());
            return response()->success([$setting, 'message' => 'Settings for Categories has been created successfully'], 200);
        }
    }
*/



  public function getAllSetting(Request $request)
  {
    //$photos_images_keys = ['locationPhotos','kidsPhotos','menPhotos','womenPhotos','homePhotos','homePagePhotos']

    //$lang = $request->lang;
/*$settings = Setting::all('key', 'value');
	  $settings->each(function($item){
	  $item->value = json_decode($item->value,true);
	  
	  	  $item->value = json_encode($item->value,true);

	  
	  });
	  return $settings;*/
	   //return Setting::all('key', 'value');
	  

 /*  Cache::remember('settings', 60 * 60, function () {
     return $settings =  Setting::all('key', 'value');
	

		
		});
		
		//return $settings;
      // return $settings = Setting::getSettingsByLanguage($lang);


    return cache('settings');

	  */
	//  Cache::forget('settings');
	  
	  return Setting::all('key','value');
    /* $settings = Setting::query()
      ->when($lang == 'ar', function ($query) {
        return $query->select('key', 'value->ar as value');
      }, function ($query) {
        return $query->select('key', 'value->en as value');
      })
      ->get();

    // If both 'value->ar' and 'value->en' are null, return the whole value
    $settings->transform(function ($setting) {
      if (is_null($setting->value->ar) || is_null($setting->value->en)) {
        $setting->value = json_decode($setting->value, true);
      }
      return $setting;
    });

    return $settings;
    */
  }
	
	
	
	public function addNonReplacableCatgories(Request $request){
		
	   if ($request->key == 'addNonReplacableCatgories') {
		   
	
      $setting = Setting::where('key', 'addNonReplacableCatgories')->first();
      if ($setting) {
		  
		  
		  
		  
       // Assuming $setting is an object with an update method and $request is an instance of a class or array containing request data.
		  $setting_updated = [];
		  
		  foreach($request->value as $key => $value){
		  $setting_updated[$key] = $value;
		  
		  
		  }
		  
$setting->update([
    'value' => json_encode([
      
            $setting_updated
    ])
]);


        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Advertisement Tape has been updated successfully',
        ], Response::HTTP_OK);
      }

      $setting = $this->settingService->createSetting($request->all());
      return response()->json([
        'data' => $setting,
        'message' => 'Settings for Advertisement Tape has been created successfully',
      ], Response::HTTP_CREATED);
    }
	
	}
	
		
		
		
		
	


  public function typesOfProblems(CreateSettingRequest $request)
  {
    if ($request->key == 'type_of_problems') {
      $setting = Setting::where('key', 'type_of_problems')->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([
            $request->validated('value')
          ])
        ]);
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Types of Problems has been updated successfully',
        ], Response::HTTP_OK);
      }

      $setting = $this->settingService->createSetting($request->validated());
      return response()->json([
        'data' => $setting,
        'message' => 'Settings for Types of Problems has been created successfully',
      ], Response::HTTP_CREATED);
    }
  }

public function aboutUs(Request $request)
{
    $validate = Validator::make(
        $request->only('key', 'value', 'aboutUs'),
        [
            'key' => 'required|string|max:40',
            'value' => 'required|json',
            'aboutUs' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]
    );

    if ($validate->fails()) {
        return response()->json([
            'errors' => $validate->errors(),
        ], 422);
    }

    if ($request->key == 'aboutUs') {
        $setting = Setting::where('key', 'aboutUs')->first();
        if ($setting) {
            if ($request->hasFile('aboutUs')) {
                $image = $this->saveImage($request->file('aboutUs'),rand(000000,999999), 'aboutUs');
                if ($image) {
					
				
                    $value = json_decode($request->input('value'), true);
                    $setting->update([
                        'value' => json_encode([
                            'ar' => $value['value']['ar'],
                            'en' => $value['value']['en'],
                            'image' => $image
                        ])
                    ]);
                    return response()->json([
                        'data' => $setting,
                        'message' => 'Settings for About Us has been updated successfully',
                    ], Response::HTTP_OK);
                }
            } else {
                $value = json_decode($request->input('value'), true);
                $setting->update([
                    'value' => json_encode([
                        'ar' => $value['value']['ar'],
                        'en' => $value['value']['en'],
                        'image' => $value['value']['image'] ?? null
                    ])
                ]);
                return response()->json([
                    'data' => $setting,
                    'message' => 'Settings for About Us has been updated successfully',
                ], Response::HTTP_OK);
            }
        } else {
            $setting = $this->settingService->createSetting($request->validated());
            if ($setting) {
                return response()->json([
                    'data' => $setting,
                    'message' => 'Settings for About Us has been created successfully',
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'message' => 'Failed to create setting',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}

        


  public function loginNotifactions(Request $request)
  {
    if ($request->key == 'loginNotification') {
      $setting = Setting::where('key', 'loginNotification')->first();
      if ($setting) {
        $setting = $this->settingService->updateNotifications($request->key, $request->value['title'], $request->value['body']);

        return $setting;
      }

      $setting = $this->settingService->createSetting($request->all());
      return response()->success([$setting, 'message' => 'Settings for Login Notifactions has been created successfully'], 200);
    }
  }



  /*public function typesOFProblems(Request $request){

    if ($request->key == 'typesOFProblems') {
      $setting = Setting::where('key', 'typesOFProblems')->first();
      if ($setting) {
        $this->settingService->updateNotifications($request->key, $request->value['title'], $request->value['body']);

        return $setting;
      }

      $setting = $this->settingService->createSetting($request->all());
      return response()->success([$setting, 'message' => 'Settings for Login Notifactions has been created successfully'], 200);
    }

  }
  */

  public function banUserNotifactions(Request $request)
  {
    if ($request->key == 'BanUserNotification') {

      $setting = Setting::where('key', 'BanUserNotification')->first();
      if ($setting) {
        $setting = $this->settingService->updateNotifications($request->key, $request->value['ar'], $request->value['en']);

        return $setting;
      }



      $setting = $this->settingService->createSetting($request->all());
      return response()->success([$setting, 'message' => 'Settings for Ban User Notifactions has been created successfully'], 200);
    }
  }
  public function links(CreateSettingRequest $request)
  {
    if ($request->key == 'links') {


      $setting = Setting::where('key', 'links')->first();
      if ($setting) {
        $setting = $this->settingService->updatelinks($request->key, $request->value['phone'], $request->value['helpdesk'], $request->value['facebook'], $request->value['instagram'], $request->value['twitter'], $request->value['whatsapp'], $request->value['email'], $request->value['google_play'], $request->value['app_store']);

        return $setting;
      }


      $setting = $this->settingService->createSetting($request->validated());
      return response()->success([$setting, 'message' => 'Settings for Links has been created successfully'], 200);
    }
  }

  public function fees(CreateSettingRequest $request)
  {
    if ($request->key == 'fees') {
      $setting = Setting::where('key', 'fees')->first();
      if ($setting) {
		  
		 
       /* $setting->update([
          'value' => json_encode([


            'en' => [

              'shipping_fee' => $request->value['en']['shipping_fee'],
              'free_shipping' => $request->value['en']['free_shipping']

            ],


            'ar' => [

              'shipping_fee' => $request->value['ar']['shipping_fee'],
              'free_shipping' => $request->value['ar']['free_shipping']


            ]




          ])
        ]);
		  */
 $setting->update(['value' => $request->value]);
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Fees has been updated successfully',
        ], Response::HTTP_OK);
      }

      $setting = $this->settingService->createSetting($request->validated());
      return response()->json([
        'data' => $setting,
        'message' => 'Settings for Fees has been created successfully',
      ], Response::HTTP_CREATED);
    }
  }

  public function returnPolicy(CreateSettingRequest $request)
  {
    if ($request->key == 'return_policy') {
      $setting = Setting::where('key', 'return_policy')->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([

            'en' => [

              'title' => $request->value['en']['title'],
              'days' => $request->value['en']['days']

            ],

            'ar' => [

              'title' => $request->value['ar']['title'],
              'days' => $request->value['ar']['days']



            ]

          ])
        ]);
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Return Policy has been updated successfully',
        ], Response::HTTP_OK);
      }

      $setting = $this->settingService->createSetting($request->validated());
      return response()->json([
        'data' => $setting,
        'message' => 'Settings for Return Policy has been created successfully',
      ], Response::HTTP_CREATED);
    }
  }
  public function advertismentTape(CreateSettingRequest $request)
  {
    if ($request->key == 'Advertisement_tape') {
      $setting = Setting::where('key', 'Advertisement_tape')->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([
            'en' => [
              'sentence1' => $request->value['en']['sentence1'],
              'sentence2' => $request->value['en']['sentence2'],
              'sentence3' => $request->value['en']['sentence3']
            ],
            'ar' => [
              'sentence1' => $request->value['ar']['sentence1'],
              'sentence2' => $request->value['ar']['sentence2'],
              'sentence3' => $request->value['ar']['sentence3']
            ]
          ])
        ]);

        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Advertisement Tape has been updated successfully',
        ], Response::HTTP_OK);
      }

      $setting = $this->settingService->createSetting($request->validated());
      return response()->json([
        'data' => $setting,
        'message' => 'Settings for Advertisement Tape has been created successfully',
      ], Response::HTTP_CREATED);
    }
  }


  public function privacyPolicy(CreateSettingRequest $request)
  {
    if ($request->key == 'privacy_policy') {



      $setting = Setting::where('key', 'privacy_policy')->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([
            'en' => $request->value['en'],
            'ar' => $request->value['ar']
          ])
        ]);
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Privacy Policy has been updated successfully',
        ], Response::HTTP_OK);
      }


      $setting = $this->settingService->createSetting($request->validated());
      return response()->success([$setting, 'message' => 'Settings for Privacy Policy has been created successfully'], 200);
    }
  }


  public function photos(CreateSettingRequest $request)
  {
    if ($request->key == 'photos') {


      $setting = Setting::where('key', 'photos')->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([
            'en' => $request->value['en'],
            'ar' => $request->value['ar']
          ])
        ]);
        return $setting;
      }



      $setting = $this->settingService->createSetting($request->validated());
      return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
    }
  }

  public function getAppSections(Request $request)
  {
    $key = $request->key;
    $section = $request->section_name;
    $set = Setting::where('key', $key)->first();
    return json_decode($set->value)->$section;
  }


  public function app_sections(CreateSettingRequest $request)
  {
    if (in_array($request->key, ['offers', 'new_in', 'flash_sales'])) {

      $setting = Setting::where('key', $request->key)->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([
            'men_image' => $request->value['men_image'],
            'women_image' => $request->value['women_image'],
            'kids_image' => $request->value['kids_image'],
            'home_image' => $request->value['home_image'],
          ])
        ]);
        return $setting;
      }



      $setting = $this->settingService->createSetting($request->validated());
      return response()->success([$setting, 'message' => 'Settings for App section has been created successfully'], 200);
    }
  }

  public function homePagePhotos(PhotosSettingRequest $request)
  {


    if ($request->key == 'homePagePhotos') {


      $setting = Setting::where('key', 'homePagePhotos')->first();

      if ($setting) {
        $updated_value = $this->updateHomePageSettingValue($request, $setting);
        $setting->value = $updated_value;
        $setting->save();
      } else {
        $setting = new Setting();
        $setting->key = $request->key;
        $setting->value = $this->createHomePageSettingValue($request);
        $setting->save();
      }

      return response()->success([$setting, 'message' => 'Settings for App section has been created successfully'], 200);
    }
  }

	
	public function createHomePageSettingValue($request){
	  $keyName = $request['key'];
    $existing_value = $request[$keyName] ?? '{}'; // Default to an empty JSON object if not provided
    $updated_value = json_decode($existing_value, true);

    if (isset($request['men'])) {
      $photo_path = $this->saveImage($request['men'], 'photo', 'men');
      $updated_value['men']['link'] = $photo_path;
    }

    if (isset($request['women'])) {
      $photo_path = $this->saveImage($request['women'], 'photo', 'women');
      $updated_value['women']['link'] = $photo_path;
    }

    if (isset($request['kids'])) {
      $photo_path = $this->saveImage($request['kids'], 'photo', 'kids');
      $updated_value['kids']['link'] = $photo_path;
    }

    if (isset($request['home'])) {
      $photo_path = $this->saveImage($request['home'], 'photo', 'home');
      $updated_value['home']['link'] = $photo_path;
    }

    return json_encode($updated_value);
	
	}
	
	
	public function updateHomePageSettingValue($request){
	  $keyName = $request['key'];
    $existing_value = $request[$keyName];  // Default to an empty JSON object if not provided
    $updated_value = json_decode($existing_value, true);

    if (isset($request['men'])) {
      $photo_path = $this->saveImage($request['men'], 'photo', 'men');
      $updated_value['men']['link'] = $photo_path;
    }

    if (isset($request['women'])) {
      $photo_path = $this->saveImage($request['women'], 'photo', 'women');
      $updated_value['women']['link'] = $photo_path;
    }

    if (isset($request['kids'])) {
      $photo_path = $this->saveImage($request['kids'], 'photo', 'kids');
      $updated_value['kids']['link'] = $photo_path;
    }

    if (isset($request['home'])) {
      $photo_path = $this->saveImage($request['home'], 'photo', 'home');
      $updated_value['home']['link'] = $photo_path;
    }

    return json_encode($updated_value);
	
	
	}

  public function userComplaints(CreateSettingRequest $request)
  {


    if ($request->key == 'userComplaints') {


      $setting = Setting::where('key', 'userComplaints')->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([



            'ar' => [

              'complaint1' => $request->value['ar']['complaint1'],
              'complaint2' => $request->value['ar']['complaint2'],
              'complaint3' => $request->value['ar']['complaint3'],
            ],

            'en' => [

              'complaint1' => $request->value['en']['complaint1'],
              'complaint2' => $request->value['en']['complaint2'],
              'complaint3' => $request->value['en']['complaint3'],

            ]


          ])
        ]);
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for User Complaints has been updated successfully',
        ], Response::HTTP_OK);
      }

      $setting = $this->settingService->createSetting($request->validated());
      return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
    }
  }




  public function navBarPhotos(CreateSettingRequest $request)
  {
    if ($request->key == 'navBarPhotos') {


      $setting = Setting::where('key', 'navBarPhotos')->first();
      if ($setting) {
        $setting->update(['value' => json_encode(['men' => $request->value->men, 'women' => $request->value->women, 'kids' => $request->value->kids, 'home' => $request->value->home])]);
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Privacy Policy has been updated successfully',
        ], Response::HTTP_OK);
      }



      $setting = $this->settingService->createSetting($request->validated());
      return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
    }
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

  public function terms(CreateSettingRequest $request)
  {
    if ($request->key == 'terms') {
      $setting = Setting::where('key', 'terms')->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([
            'en' => $request->value['en'],
            'ar' => $request->value['ar']
          ])
        ]);
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for Terms has been updated successfully',
        ], Response::HTTP_OK);
      }

      $setting = $this->settingService->createSetting($request->validated());
      return response()->json([
        'data' => $setting,
        'message' => 'Settings for Terms has been created successfully',
      ], Response::HTTP_CREATED);
    }
  }

	
	public function terms_en(CreateSettingRequest $request)
	  {
		if ($request->key == 'terms_en') {
		  $setting = Setting::where('key', 'terms_en')->first();
		  if ($setting) {
			$setting->update([
			  'value' => json_encode($request->validated['value'])
			]);
			return response()->json([
			  'data' => $setting,
			  'message' => 'Settings for Terms has been updated successfully',
			], Response::HTTP_OK);
		  }

		  $setting = $this->settingService->createSetting($request->validated());
		  return response()->json([
			'data' => $setting,
			'message' => 'Settings for Terms has been created successfully',
		  ], Response::HTTP_CREATED);
		}
	  }

  public function changeVisibilty(Request $request)
  {

    $change_visiblity = $request->change_visiblity;

    $setting_to_change = Setting::findOrFail($request->setting_id);

    if ($setting_to_change->is_visiable == $change_visiblity) {


      return;
    } else {

      $setting_to_change->update(['is_visible' => $change_visiblity]);
    };

    return response()->success([$setting_to_change, 'message' => 'Setting has been visible successfully'], 200);
  }



  public function menPhotos(PhotosSettingRequest $request)
  {


    if ($request->key == 'menPhotos') {


      $setting = Setting::where('key', 'menPhotos')->first();

      if ($setting) {
        $updated_value = $this->updateSettingValue($request->validated(), $setting);
        $setting->value = $updated_value;
        $setting->save();
      } else {
        $setting = new Setting();
        $setting->key = $request->key;
        $setting->value = $this->createSettingValue($request->validated());
        $setting->save();
      }

      return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
    }
  }



  public function womenPhotos(PhotosSettingRequest $request)
  {


    if ($request->key == 'womenPhotos') {


      $setting = Setting::where('key', 'womenPhotos')->first();

      if ($setting) {
        $updated_value = $this->updateSettingValue($request->validated(), $setting);
        $setting->value = $updated_value;
        $setting->save();
      } else {
        $setting = new Setting();
        $setting->key = $request->key;
        $setting->value = $this->createSettingValue($request->validated());
        $setting->save();
      }

      return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
    }
  }




  public function kidsPhotos(PhotosSettingRequest $request)
  {


    if ($request->key == 'kidsPhotos') {


      $setting = Setting::where('key', 'kidsPhotos')->first();

      if ($setting) {
        $updated_value = $this->updateSettingValue($request->validated(), $setting);
        $setting->value = $updated_value;
        $setting->save();
      } else {
        $setting = new Setting();
        $setting->key = $request->key;
        $setting->value = $this->createSettingValue($request->validated());
        $setting->save();
      }

      return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
    }
  }




  public function homePhotos(PhotosSettingRequest $request)
  {


    if ($request->key == 'homePhotos') {


      $setting = Setting::where('key', 'homePhotos')->first();

      if ($setting) {
        $updated_value = $this->updateSettingValue($request->validated(), $setting);
        $setting->value = $updated_value;
        $setting->save();
      } else {
        $setting = new Setting();
        $setting->key = $request->key;
        $setting->value = $this->createSettingValue($request->validated());
        $setting->save();
      }

      return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
    }
  }




  public function couponDetails(Request $request)
  {


    $validate = Validator::make(
      $request->only('key', 'couponDetails', 'banner'),
      [
          'key' => 'required|string|max:40',
          'couponDetails' => 'required|json',
          'banner' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
      ]
  );

  if ($validate->fails()) {
      return response()->json([
          'errors' => $validate->errors(),
      ], 422);
  }



    if ($request->key == 'couponDetails') {


      $setting = Setting::where('key', 'couponDetails')->first();
      if ($setting) {


        if ($request->hasFile('banner')) {


          $banner = $this->saveImage($request->file('banner'),'banners', 'banner');
          $coupon_details_value = json_decode($request->couponDetails, true);


          $setting->update([
            'value' => json_encode([
              'ar' => [
                'code' => $coupon_details_value['ar']['code'],
                'text' => $coupon_details_value['ar']['text'],
                'banner' => $banner
              ],
              'en' => [
                'code' => $coupon_details_value['en']['code'],
                'text' => $coupon_details_value['en']['text'],
                'banner' => $banner
              ]
            ])
          ]);

          return response()->json([
            'data' => $setting,
            'message' => 'Settings for Coupon Details has been updated successfully',
          ], Response::HTTP_OK);
        } else {
          $coupon_details_value = json_decode($request->couponDetails, true);
          $setting->update([
            'value' => json_encode([
              'en' => [
                'code' => $coupon_details_value['en']['code'],
                'text' => $coupon_details_value['en']['text'],
                'banner' => $coupon_details_value['en']['banner']
              ],
              'ar' => [
                'code' => $coupon_details_value['ar']['code'],
                'text' => $coupon_details_value['ar']['text'],
                'banner' => $coupon_details_value['ar']['banner']
              ]
            ])
          ]);

          return response()->json([
            'data' => $setting,
            'message' => 'Settings for Coupon Details has been updated successfully',
          ], Response::HTTP_OK);
        }
      } else {

        $banner = $this->saveImage($request->file('banner'),'banners', 'banner');
        $setting = new Setting();

        $setting->key = $request->key;


        $updated_value = json_decode($request->couponDetails, true);

        $updated_value['banner'] = $banner;

        $setting->value = json_encode($updated_value);
        $setting->save();

        // $setting = $this->settingService->createSetting($request->validated());
        return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
      }
    }
  }
  public function flashSale(CreateSettingRequest $request)
  {



    /* if ($request->key == 'flashSale') {


     
     $setting = Setting::where('key', 'flashSale')->first();
      if ($setting) {
        $setting->update(['value' => json_encode(['men' => $request->value['men'], 'women' => $request->value['women'], 'kids' => $request->value['kids'], 'home' => $request->value['home']])]);
        return $setting;
      } else {


   $flash_sales_products = Product::group()-> 
                $query->where('promotion_type', 'flash_sales');
            ) ->whereHas('discount', function ($query) {
                        $query->where('end_date', '<=', now());


$value = json_encode(['men' => ['group_name' => ] ])

        //$setting = $this->settingService->createSetting($request->validated());
        
        $setting = Setting::create([
            
            'key' => $request->key,
            
    
            
            
            
            
            ]);
        
        
        
        return response()->success([$setting, 'message' => 'Settings for Photos has been created successfully'], 200);
      }
    }
    */
  }



  public function policySecurity(CreateSettingRequest $request)
  {



    if ($request->key == 'policySecurity') {
      $setting = Setting::where('key', 'policySecurity')->first();
      if ($setting) {
        $setting->update([
          'value' => json_encode([
            'en' => [
              'refund_policy' => $request->value['en']['refund_policy'],
              'secure_payment' => $request->value['en']['secure_payment'],
              'your_info_is_safe' => $request->value['en']['your_info_is_safe']
            ],
            'ar' => [
              'refund_policy' => $request->value['ar']['refund_policy'],
              'secure_payment' => $request->value['ar']['secure_payment'],
              'your_info_is_safe' => $request->value['ar']['your_info_is_safe']
            ]
          ])
        ]);
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for PolicySecurity has been updated successfully',
        ], Response::HTTP_OK);
      } else {
        $setting = $this->settingService->createSetting($request->validated());
        return response()->json([
          'data' => $setting,
          'message' => 'Settings for PolicySecurity has been created successfully',
        ], Response::HTTP_CREATED);
      }
    }
  }






  private function updateSettingValue($request, $setting)
  {


    $keyName = $request['key'];
    $existing_value = $request[$keyName];  // Default to an empty JSON object if not provided
    $updated_value = json_decode($existing_value, true);

    if (isset($request['navBar'])) {
      $photo_path = $this->saveImage($request['navBar'], 'photo', 'men');
      $updated_value['navBar']['link'] = $photo_path;
    }

    if (isset($request['banner_header'])) {
      $photo_path = $this->saveImage($request['banner_header'], 'photo', 'women');
      $updated_value['banner_header']['link'] = $photo_path;
    }

    if (isset($request['banner_middle_page'])) {
      $photo_path = $this->saveImage($request['banner_middle_page'], 'photo', 'home');
      $updated_value['banner_middle_page']['link'] = $photo_path;
    }

    if (isset($request['banner_bottom_page'])) {
      $photo_path = $this->saveImage($request['banner_bottom_page'], 'photo', 'kids');
      $updated_value['banner_bottom_page']['link'] = $photo_path;
    }

    return json_encode($updated_value);
  }

  private function createSettingValue($request)
  {
    $keyName = $request['key'];
    $existing_value = $request[$keyName] ?? '{}'; // Default to an empty JSON object if not provided
    $updated_value = json_decode($existing_value, true);

    if (isset($request['navBar'])) {
      $photo_path = $this->saveImage($request['navBar'], 'photo', 'men');
      $updated_value['navBar']['link'] = $photo_path;
    }

    if (isset($request['banner_header'])) {
      $photo_path = $this->saveImage($request['banner_header'], 'photo', 'women');
      $updated_value['banner_header']['link'] = $photo_path;
    }

    if (isset($request['banner_middle_page'])) {
      $photo_path = $this->saveImage($request['banner_middle_page'], 'photo', 'home');
      $updated_value['banner_middle_page']['link'] = $photo_path;
    }

    if (isset($request['banner_middle_page'])) {
      $photo_path = $this->saveImage($request['banner_middle_page'], 'photo', 'kids');
      $updated_value['banner_middle_page']['link'] = $photo_path;
    }

    return json_encode($updated_value);
  }

  public function typeOfProblems()
  {
    $key = Setting::where('key', 'type_of_problems')->first();

    if ($key != null) {
      $keys = json_decode($key->value)[0]->en;
      $categoryNames = [];

      // Iterate over the 'en' part to extract the category names
      foreach ($keys as $categoryName => $categoryDetails) {
        // Add the category name to the array
        $categoryNames[] = $categoryName;
      }
      return $categoryNames;
    }
  }


public function shippingNotesP(Request $request)
{
    if ($request->key == 'shippingNotes') {
        $setting = Setting::where('key', 'shippingNotes')->first();
        if ($setting) {
            $setting->update([
                'value' => json_encode([
                    'en' => [
                        'paragraph' => $request->value['en']['paragraph'],
                    ],
                    'ar' => [
                        'paragraph' => $request->value['ar']['paragraph'],
                    ]
                ])
            ]);

            return response()->json([
                'data' => $setting,
                'message' => 'Settings for Advertisement Tape has been updated successfully',
            ], Response::HTTP_OK);
        }

        $setting = $this->settingService->createSetting($request->all());
        return response()->json([
            'data' => $setting,
            'message' => 'Settings for Advertisement Tape has been created successfully',
        ], Response::HTTP_CREATED);
    }
}
	public function storePoligon(Request $request){
		        
		$employee = auth('api-employees')->user();
		if (!$employee){
			return response()->json('Unauthorized',403);
		}
		if ($employee->hasRole(Roles::MAIN_ADMIN)) {
			$coordinates = $request->coordinates;
			Poligon::create([
				'coordinates' => $coordinates
			]);
			return response()->success(['message' => 'Poligon has been created successfully'], 200);
		}else{
			return response()->json(['error' => 'Something went wrong'], 400);
		}
	}
	
	public function getPoligons(){
		
		$coordinates = Poligon::all();
		return response()->json(['data' => $coordinates]);
		
	}





  public function delete(Request $request)
  {
    $this->settingService->delete($request->key);


    return response()->success(['message' => 'Setting ' . $request->key . 'has been deleted successfully'], 200);
  }
}
