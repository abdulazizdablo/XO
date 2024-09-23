<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\AppSetting;
use InvalidArgumentException;
use App\Traits\PhotoTrait;
use App\Traits\TranslateFields;

class SettingService
{
    use PhotoTrait, TranslateFields;

    public function updateNotifications($key, $ar, $en)
    {
        $key = Setting::where('key', $key)->first();
        $value = json_encode([
            'ar' => [
                'title' => $ar['title'],
                'body' => $ar['body']
            ],
            'en' => [
                'title' => $en['title'],
                'body' => $en['body']
            ]
        ]);

        if (!$value) {
            throw new InvalidArgumentException('There Is No Setting Available');
        }

        $key->update(
            [
                'value' => $value,
            ]
        );
        return $key;
    }



    public function updatelinks($key, $phone, $helpdesk, $facebook, $instagram, $twitter, $whatsapp, $email, $google_play, $app_store)
    {
        $key = Setting::where('key', $key)->first();
        $value = json_encode(['phone' => $phone, 'helpdesk' => $helpdesk, 'facebook' => $facebook, 'instagram' => $instagram, 'twitter' => $twitter, 'whatsapp' => $whatsapp, 'email' => $email, 'google_play' => $google_play, 'app_store' => $app_store]);

        if ($key != null) {

            $key->update(
                [
                    'value' => $value,
                ]
            );
            return response($key, 200);
        } else {
            // return response()->json(['error' => ' this id does not exit to modify '], 404);

            Setting::create([

                'key' => $key,

                'value' => $value

            ]);
        }
    }

    public function getSetting($key)
    {
        $key = Setting::where('key', $key)->first();

        if ($key != null) {
            $key->value = json_decode($key->value);
            return $key;
        } else {

            throw new InvalidArgumentException('There Is No Settings');
        }
    }

  /*  public function getSetting($key)
    {
        $setting = Setting::where('key', $key)->first();
    
        if ($setting != null) {
            // Check if the value is an array
            if (is_array($setting->value)) {
                return $setting;
            }
            // If the value is not an array, it might be a JSON string
            else {
                // Decode the JSON string
                $setting->value = json_decode($setting->value, true);
                return $setting;
            }
        } else {
            // Throw an exception if no setting is found
            throw new InvalidArgumentException('There is no setting with the given key.');
        }
    }
*/
    public function createSetting(array $setting_data)
    {



        $json_data = json_encode($setting_data['value']);

        $setting = Setting::create([

            'key' => $setting_data['key'],

            'value' => $json_data

        ]);


        return $setting;
    }


    public function createAppSetting(array $setting_data)
    {

        $json_data = json_encode($setting_data['value']);

        $setting = AppSetting::create([

            'key' => $setting_data['key'],

            'value' => $json_data

        ]);


        return $setting;
    }



    public function delete(string $key_setting)
    {

        $setting = Setting::where('key', $key_setting)->first();

        $setting->delete();
    }

    public function updateNotifaction(Request $request)
    {

        $key = $request->key;
        $title = $request->title;
        $body = $request->body;


        $this->settingService->updateNotifications($key, $title, $body);

        return response()->success(['message' => 'Setting has been updated successfully'], 200);
    }
}
