<?php


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FcmToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Exception\RequestException;

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userFcmToken;
    protected $title;
    protected $body;
    protected $type;
    protected $serverKey;
    protected $priority;
    protected $client;
    protected $credentialsPath;
    protected $project_id;

    public function __construct(
        string $userFcmToken,
        string $title,
        string $body,
        string $type,
        string $serverKey,
        ?string $priority = null
    ) {
        $this->userFcmToken = $userFcmToken;
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->serverKey = $serverKey;
        $this->priority = $priority;
        $this->credentialsPath =  config('services.fcm_xo_app.credentialsPath');
        $this->project_id = /*"xo-project-c6723";*/ config('services.fcm_xo_app.project_id');
    }

    public function handle()
    {
        $credentials = new ServiceAccountCredentials('https://www.googleapis.com/auth/firebase.messaging',$this->credentialsPath);
        $authToken = $credentials->fetchAuthToken()['access_token'];
        $url = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";
        try{
            $response = Http::withToken($authToken)->post($url,
            [
                'message'=>['token' => $this->userFcmToken,
                'data' => [
                    'type' => $this->type,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'notification' => [
                    'title' => $this->title ,
                    'body' => $this->body 
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
			Log::debug('User FCM Token: '.$this->userFcmToken);
			Log::debug('-----');
			Log::debug($response);
			Log::debug('-----');

            return $response->getBody()->getContents();
        }catch(RequestException $e){
            return $e->getMessage();
        }
    }

}
