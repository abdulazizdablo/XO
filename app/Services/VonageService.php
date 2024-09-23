<?php

namespace App\Services;

use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\Verify2\Request\SMSRequest;
use Vonage\Verify2\Request\WhatsAppRequest;

class VonageService
{
    protected $vonage;

    public function __construct()
    {
        $basic = new Basic(config('app.NEXMO_API_KEY'), config('app.NEXMO_API_SECRET'));
        $this->vonage = new Client($basic);
    }
    public function startVerification($phoneNumber, $brand = 'Xo Textile')
    {
        // Verify via WhatsApp
        // $WhatsAppRequest = new WhatsAppRequest($phoneNumber,  $brand);
        // $verification=      $this->vonage->verify2()->startVerification($WhatsAppRequest);

        // Verify via SMS
        $SMSRequest = new SMSRequest($phoneNumber,  $brand);
        $verification=      $this->vonage->verify2()->startVerification($SMSRequest);

        return $verification;
    }

    public function checkVerification($requestId, $code)
    {
        return $this->vonage->verify2()->check($requestId, $code);
    }
}