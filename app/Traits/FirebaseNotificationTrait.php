<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FcmToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Exception\RequestException;

trait FirebaseNotificationTrait

{
    protected $client;
    protected $credentialsPath;
    protected $project_id;

    public function __construct() {
        $this->credentialsPath =  config('services.fcm_xo_delivery.credentialsPath');
        $this->project_id =  config('services.fcm_xo_delivery.project_id');
    }
 
   // function send_notification($user_fcm_token, $title_ar, $title_en, $body_ar, $body_en, $type, $server_key, $priority = null)
    function send_notification($user_fcm_token, $title, $body, $type, $server_key, $priority = null, $details = null)
    {
		if($server_key == 'flutter_app'){
			$this->credentialsPath =  config('services.fcm_xo_app.credentialsPath');
			$this->project_id =  config('services.fcm_xo_app.project_id');		
		}
		
		if($server_key == 'dashboard'){
			$this->credentialsPath =  config('services.fcm_xo_dashboard.credentialsPath');
			$this->project_id =  config('services.fcm_xo_dashboard.project_id');		
		}
		Log::debug('user_fcm_token '. $user_fcm_token);
		Log::debug('-----');
		Log::debug('title'. $title);
		Log::debug('-----');
		Log::debug('body'. $body);
		Log::debug('-----');
		Log::debug('server_key'. $server_key);
		Log::debug('-----');
		Log::debug('credentialsPath'. $this->credentialsPath);
		Log::debug('-----');
		Log::debug('project_id'. $this->project_id);
		Log::debug('-----');
		
        $credentials = new ServiceAccountCredentials('https://www.googleapis.com/auth/firebase.messaging',$this->credentialsPath);
        $authToken = $credentials->fetchAuthToken()['access_token'];
        $url = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";
        try{
            $response = Http::withToken($authToken)->post($url,
            [
                'message'=>['token' => $user_fcm_token,
                'data' => [
                    'type' => $type,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
					'details' => $details
                ],
                'notification' => [
                    'title' => $title ,
                    'body' => $body 
                ],
                'android' => [
                    'priority' => 'high'
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10'
                    ],
                    'payload' => [
                        'aps' => [
                            'content-available' => 1,
                            'badge' => 5,
                            'priority' => 'high'
                        ]
                    ]
                ]
            ]]);
			Log::debug('res');
			Log::debug('-----');
			Log::debug($response);
			Log::debug('-----');

            return $response->getBody()->getContents();
        }catch(RequestException $e){
            return $e->getMessage();
        }

        $reqData['to'] = $user_fcm_token;
       /* $reqData['data']['title_ar'] = $title_ar;
        $reqData['data']['title_en'] = $title_en;
        $reqData['data']['body_ar'] = $body_ar;
        $reqData['data']['body_en'] = $body_en;*/
        $reqData['data']['type'] = $type;
        $reqData['data']['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
      //  $reqData['topic'] = 'broadcast';
        $reqData['notification']['title'] = $title;//'broadcast';
        $reqData['notification']['body'] = $body;//'broadcast';

		if($priority!== null){
			$reqData['data']['priority'] = $priority;
		}else{
			$reqData['data']['priority'] = 'low';
		}
        $reqData['data']['priority'] = 'high';
		if($server_key == 'delivery_app'){
		$key = 'key=AAAAvhBn_EM:APA91bEirZCBMXGcHVFOlKN0NkGc0gY6IWHTq5WEtsjOYgoXYquUE-y4DdIi-k-lFBjHTayEzUVCCY0zobfA1pnGq84C0iOD36gASzTMweCmRRdepTuyveqXY6IjISleG7QTrY4xxjt8';	
		}else{
		$key = 'key=AAAAYNDv6iw:APA91bG0KivS-dWnp_xP6UR09RqcBFJFML-3aP80JQyS0YHDKxV9ehUimSr2wUY58a8vE_mSSpMGduU_oGYcCTrBQj2p-leesexU6S9ulUgYqGQBEU0L8_Bg_XuUadx10Yy3S98ijyy7';
		}
        $res = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $key,
        ])->post('https://fcm.googleapis.com/fcm/send', $reqData);

		Log::debug('key');
        Log::debug('-----');
        Log::debug($key);
        Log::debug('res');
        Log::debug('-----');
        Log::debug($res);
		
		if(isset($res['failure'])){
			if($res['failure'] == 1){
				FcmToken::where('fcm_token', $user_fcm_token)->first()->delete();
			}		
		}
		
        Log::debug('-----');
        Log::debug('reqData');
        Log::debug('-----');
        Log::debug(json_encode($reqData));

    }

}
