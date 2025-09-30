<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class EcashPaymentService
{
    private $base_url;
    private $redirect_url;
    private $gift_redirect_url;
    private $callback_url;
    private $gift_callback_url;
    private $replace_callback_url;
    // private $headers;
    private $request_client;
    private $currency;
    private $terminal_key;
    private $merchant_id;
    private $merchant_secret;
    /** 
     *EcashPaymentService constructor.
     *@param Client $request_client
     */
    public function __construct(Client $request_client)
    {
        $this->request_client = $request_client;

        $this->base_url = config('app.ecash_base_url');
        $this->terminal_key = config('app.ecash_terminal_key');
        $this->merchant_id = config('app.ecash_merchant_id');
        $this->merchant_secret = config('app.ecash_merchant_secret');
        $this->redirect_url = urlencode("https://xo-textile.sy/cart/shipping/payment-success");
        $this->gift_redirect_url = urlencode("https://xo-textile.sy/my-profile/giftcards?payment-coupon=success");
        //$this->redirect_url = urlencode(str_replace('/public', '', url(config('app.ecash_redirect_url'))));
        $this->callback_url = urlencode(url(config('app.ecash_callback_url')));
        $this->gift_callback_url = urlencode(url(config('app.ecash_gift_callback_url')));
        $this->replace_callback_url = urlencode(url(config('app.ecash_replace_callback_url')));
        $this->currency = config('app.ecash_currency');
        // $this->headers = [
        //     'Content-Type' => 'application/json',
        //     'authorization' => "Bearer " . config("app.ecash_token")
        // ];
    }
    /** 
     *@param $uri
     *@param $method
     *@param array $body
     *@return false |mixed
     *@throws GuzzleHttp\Exception\GuzzleException
     */
    public function buildRequest($uri, $method, $data = [])
    {


        // $request = new Request($method, $full_url, $this->headers);
        // if (!$data)
        //     return false;

        // $response = $this->request_client->send($request, [
        //     'json' => $data
        // ]);
        // if ($response->getStatusCode() != 200) {

        //     return false;
        // }

        // $response = json_decode($response->getBody(), true);

        // return $response;
    }
    /** 
     *@param $value
     *@return mixed
     */

    //to delete pay function
    public function pay()
    {
        $full_url = "https://checkout.ecash-pay.co/Checkout/Card/GB97ST/IXZM1E/744F03E5FE93EC8C7302DAD09E823701/SYP/1000.00/";
        return $response = new Request('GET', $full_url);
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

    public function replaceOrderPayment($data)
    {
        $checkout_type = "Card"; // "QR"
        $amount = $data['amount'];
        $order_ref = $data['order_ref'];
        $verification_code = strtoupper(md5($this->merchant_id . $this->merchant_secret . $amount . $order_ref));
        $lang = $data['lang'];
        return [
            'exchange' => $data['exchange'],
            'amount' =>  $data['amount'],
            'message' => 'You will be redirected to eCash',
            'order_id' => $data['order_id'],
            'phone' => $data['phone'],
            'invoice_number' => $data['invoice_number'],
            'url' => $this->base_url . "Checkout" . "/" . $checkout_type . "/" . $this->terminal_key . "/" . $this->merchant_id . "/" . $verification_code . "/" . $this->currency . "/" . $amount . "/" . $lang . "/" . $order_ref . "/" . $this->redirect_url . "/" . $this->replace_callback_url
        ];
        //return   [$full_url = $this->base_url . "Checkout" . "/" . $checkout_type . "/" . $this->terminal_key . "/" . $this->merchant_id . "/" . $verification_code . "/" . $this->currency . "/" . $amount . "/" . $lang . "/" . $order_ref . "/" . $this->redirect_url . "/" . $this->replace_callback_url];
    }

    public function chargeGiftCard($data)
    {
        $checkout_type = "Card"; // "QR"
        $amount = $data['amount'];
        $order_ref = $data['ref'];
        $verification_code = strtoupper(md5($this->merchant_id . $this->merchant_secret . $amount . $order_ref));
        $lang = $data['lang'];

        return   $full_url = $this->base_url . "Checkout" . "/" . $checkout_type . "/" . $this->terminal_key . "/" . $this->merchant_id . "/" . $verification_code . "/" . $this->currency . "/" . $amount . "/" . $lang . "/" . $order_ref . "/" . $this->gift_redirect_url . "/" . $this->gift_callback_url;
    }


    public function getPaymentStatus($data)
    {
        return $response = $this->buildRequest("getPaymentStatus", 'POST', $data);
    }
}
