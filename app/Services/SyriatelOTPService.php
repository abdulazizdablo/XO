<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class syriatelOTPService
{
    private $base_url;
    private $user_name;
    private $pass;

    public function __construct( )
    {
        $this->base_url = env('syriatel_otp_base_url');
        $this->user_name = env('syriatel_otp_user_name');
        $this->pass = env('syriatel_otp_password');
    }

   
    public function sendSMS($msg,$phone)
    {

        //$full_url = "https://bms.syriatel.sy/API/Add_Admin_By_Agent.aspx?agent_user_name=XO&agent_pass=P@1234567&first_name=xo&last_name=textile&user_name=xo_textile&password=xo123456";
		
		//$full_url= "https://bms.syriatel.sy/API/Add_Normal_By_Admin.aspx?admin_user_name=XO&admin_pass=P@1234567&first_name=xo&last_name=textile&user_name=xo_textile&mobile=963933096270";
		
		//$full_url = "https://bms.syriatel.sy/API/SendSMS.aspx?job_name=marksJob&user_name=XO&password=P@1234567&msg=TestMSG&sender=xo&to=963993572688";
//$full_url = "https://bms.syriatel.sy/API/CheckJobStatus.aspx?user_name=XO&password=P@1234567&job_id=266943412";
        //return $response = Http::post( $full_url);
		
		// $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, 
        // "https://bms.syriatel.sy/API/SendSMS.aspx?job_name=XO&user_name=XO&password=P@1234567&msg=Hello_Shafik&sender=xo&to=963933096270");
        // //"https://bms.syriatel.sy/API/CheckJobStatus.aspx?user_name=XO&password=P@1234567&job_id=266946392");
        // curl_setopt($ch, CURLOPT_HEADER, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        // if(curl_exec($ch) === false)
        // {
        // echo 'Curl error: ' . curl_error($ch);
        // }
        // else
        // {
        // ///echo 'Operation completed without any errors';
        // }
        // curl_close($ch);
    
        // }

        $job_name="XO";
        $sender = "xo";
        $full_url = $this->base_url . "SendSMS.aspx?job_name".$job_name."&user_name".$this->user_name."&password=".$this->pass."&msg=".$msg."&sender=".$sender."&to=963".$phone;
        //$full_url = "https://bms.syriatel.sy/API/Add_Admin_By_Agent.aspx?agent_user_name=XO&agent_pass=P@1234567&first_name=xo&last_name=textile&user_name=xo_textile&password=xo123456";
        return $response = Http::post( $full_url);
    }
    
    public function sendPayment($data)
    {
        $checkout_type = "Card"; // "QR"
        $amount = $data['amount'];
        $order_ref = $data['order_ref'];
        $verification_code = strtoupper(md5($this->merchant_id . $this->merchant_secret . $amount . $order_ref));
        $lang = $data['lang'];

        return   $full_url = $this->base_url . "Checkout" . "/" . $checkout_type . "/" . $this->terminal_key . "/" . $this->merchant_id . "/" . $verification_code . "/" . $this->currency . "/" . $amount . "/" . $lang . "/" . $order_ref . "/" . $this->redirect_url . "/" . $this->callback_url;
    }

    public function getPaymentStatus($data)
    {
        return $response = $this->buildRequest("getPaymentStatus", 'POST', $data);
    }
}
