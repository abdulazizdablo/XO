<?php


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userFcmToken;
    protected $titleAr;
    protected $titleEn;
    protected $bodyAr;
    protected $bodyEn;
    protected $type;
    protected $serverKey;
    protected $priority;

    public function __construct(
        string $userFcmToken,
        string $titleAr,
        string $titleEn,
        string $bodyAr,
        string $bodyEn,
        string $type,
        string $serverKey,
        ?string $priority = null
    ) {
        $this->userFcmToken = $userFcmToken;
        $this->titleAr = $titleAr;
        $this->titleEn = $titleEn;
        $this->bodyAr = $bodyAr;
        $this->bodyEn = $bodyEn;
        $this->type = $type;
        $this->serverKey = $serverKey;
        $this->priority = $priority;
    }

    public function handle()
    {
        $reqData['to'] = $this->userFcmToken;
        $reqData['data']['title_ar'] = $this->titleAr;
        $reqData['data']['title_en'] = $this->titleEn;
        $reqData['data']['body_ar'] = $this->bodyAr;
        $reqData['data']['body_en'] = $this->bodyEn;
        $reqData['data']['type'] = $this->type;
        $reqData['data']['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

        if ($this->priority !== null) {
            $reqData['data']['priority'] = $this->priority;
        } else {
            $reqData['data']['priority'] = 'low';
        }
        $reqData['data']['priority'] = 'high';

        if ($this->serverKey === 'delivery_app') {
        	$key = 'key=AAAAvhBn_EM:APA91bEirZCBMXGcHVFOlKN0NkGc0gY6IWHTq5WEtsjOYgoXYquUE-y4DdIi-k-lFBjHTayEzUVCCY0zobfA1pnGq84C0iOD36gASzTMweCmRRdepTuyveqXY6IjISleG7QTrY4xxjt8';	
		}else{
			$key = 'key=AAAAYNDv6iw:APA91bG0KivS-dWnp_xP6UR09RqcBFJFML-3aP80JQyS0YHDKxV9ehUimSr2wUY58a8vE_mSSpMGduU_oGYcCTrBQj2p-leesexU6S9ulUgYqGQBEU0L8_Bg_XuUadx10Yy3S98ijyy7';
		}

        /*$res = */Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $key,
        ])->post('https://fcm.googleapis.com/fcm/send', $reqData);
		
		/*Log::debug('key');
        Log::debug('-----');
        Log::debug($key);
        Log::debug('res');
        Log::debug('-----');
        Log::debug($res);
        Log::debug('-----');
        Log::debug('reqData');
        Log::debug('-----');
        Log::debug(json_encode($reqData));*/
    }
}
