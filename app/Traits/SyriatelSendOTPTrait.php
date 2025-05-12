<?php

namespace App\Traits;

trait SyriatelSendOTPTrait

{
    private $syriatel_otp_username;
    private $syriatel_otp_password;
    private $syriatel_otp_templatecode;
    private $syriatel_send_template_url;

    public function initializeSyriatelSendOTP() {
        $this->syriatel_otp_username = config('app.syriatel_otp_username');
        $this->syriatel_otp_password = config('app.syriatel_otp_password');
        $this->syriatel_otp_templatecode = config('app.syriatel_otp_templatecode');
        $this->syriatel_send_template_url = config('app.syriatel_send_template_url');
    }

    function send_otp($verify_code, $phone)
    {
        ob_start();
        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_URL,
            /*"https://bms.syriatel.sy/API/SendTemplateSMS.aspx?user_name=" */
            $this->syriatel_otp_templatecode . '?user_name=' . $this->syriatel_otp_username . "&password=" . $this->syriatel_otp_password . "&template_code=" . $this->syriatel_otp_templatecode . "&param_list=" . $verify_code . "&sender=XO&to=" . $phone
        );
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if (curl_exec($ch) === false) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        ob_end_clean();
    }
}
