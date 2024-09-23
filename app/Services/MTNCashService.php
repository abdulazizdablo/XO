<?php

namespace App\Http\Services;

use App\Http\Requests\MTNPayment\PaymentConfirmationRequest;
use App\Http\Requests\MTNPayment\PaymentInitiateRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use App\Models\Transaction;
use Exception;
use App\Http\Requests\MTNPayment\CreateInvoiceRequest;
use Illuminate\Http\Request;

class MTNCashService 
{

    protected $private_key;

    public function __construct()
    {
        $this->private_key =  file_get_contents(env('PRIVATE_KEY_PATH'));
    }




    public function confirmPayment(array $payment_data)
    {
        try {

            $code = base64_encode(hash('sha256', $request->validated('code'), true));
            $json_body_parameter = [
                'Phone' => $payment_data['Phone'],
                'Guid' => $request->header('X-Guid') ?? '',
                'Invoice' => $request->header('X-Invoice') ?? '',
                'OperationNumber' => $request->header('X-OperationNumber') ?? '',
                'Code' => $code

            ];


            $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $privateKeyResource = openssl_pkey_get_private($this->private_key);





            openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

            $encryptedString = base64_encode($signature);


            $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/payment_phone/confirm";
            // Create a new Guzzle client
            $client = new Client([
                'verify' => true, // Disable SSL verification
            ]);

            $response = $client->post($full_url, [
                'headers' => [
                    'Request-Name' => 'pos_web/payment_phone/initiate',
                    'X-Signature' => $encryptedString,
                    'Subject' => '9001000000048983',
                    'Accept-Language' => 'en'
                ],
                'json' => [
                    'Phone' =>  $request->validated('Phone'),
                    'Guid' =>  $request->header('X-Guid') ?? '',
                    'Invoice' => $request->header('X-Invoice'),
                    'OperationNumber' => $request->header('X-OperationNumber') ?? '',
                    'Code' => $code
                ],
                'stream' => false,

                'debug' => true
            ]);


            // Check the response status and return the response body
            if ($response->getStatusCode() == 200) {



                $transaction = Transaction::where('transactionUUID',  $request->header('X-Guid'))->first();

                if (!$transaction) {

                    throw new Exception('Something Went Wrong', 404);
                }
                $transaction->update('status', 'completed');


                return json_decode($response->getBody(), true);
            }
        } catch (\Exception $e) {

            return response()->error('Something Went Wrong', 400);
        }
    }



    public function activate(Request $request)
    {



        $private_key =     env('PRIVATE_KEY_PATH');
        $private_key = file_get_contents($private_key);
        $publicKey = env('PUBLIC_KEY_PATH');

        $publicKey = file_get_contents($publicKey);
        $publicKey = trim(str_replace(["\n", "-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----"], "", $publicKey));



        $jsonBody =  [
            'Secret' => "18263555",
            'Serial' => "9001000000048983",
            'Key' =>   $publicKey
        ];

        $jsonString = json_encode($jsonBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $hashedString = hash('sha256',  $jsonString, true);

        // Encrypt the hashed string using the private key
        openssl_private_encrypt($hashedString, $signature, $private_key);

        // Base64 encode the encrypted data
        $encryptedString = base64_encode($signature);


        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);
        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/invoice/create";
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/pos/activate',
                'X-Signature' => $encryptedString,
                'Subject' => "9001000000048983",
                'Accept-Language' => 'en'
            ],
            'json' => [
                'Secret' => "18263555",
                'Serial' => "9001000000048983",
                'Key' =>   $publicKey
            ],
            'stream' => false,
            'debug' => true
        ]);



        return $response->getBody();
    }

    public function initiatePayment(array $payment_data)
    {

        $guid = Str::random(5);

        $json_body_parameter = [
            'Phone' => $payment_data['Phone'],
            'Invoice' =>   $request->header('X-Invoice') ?? '',
            'Guid' => $guid
        ];


        $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyResource = openssl_pkey_get_private($this->private_key);



       

        openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        //$signature = hash('sha256',  $signature);
        // Encrypt the hashed string using the private key
        //openssl_private_encrypt($hashedString, $signature, $private_key);

        // Base64 encode the encrypted data
        $encryptedString = base64_encode($signature);

        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/payment_phone/initiate";

        // Create a new Guzzle client
        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);

        // Make the HTTP POST request using Guzzle's Client
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/payment_phone/initiate',
                'X-Signature' => $encryptedString,
                'Subject' => '9001000000048983',
                'Accept-Language' => 'en'
            ],
            'json' => [
                'Phone' =>  $payment_data['Phone'],
                'Invoice' => $request->header('X-Invoice'),
                'Guid' => $guid
            ],
            'stream' => false,

            'debug' => true
        ]);


        // Check the response status and return the response body
        if ($response->getStatusCode() == 200) {

            $transaction = Transaction::create([
                'transactionUUID' => $guid,
                'user_id' => auth()->user()->id,
                'amount' => $request->Amount,
                'status' => 'pending',
                'operation_type' => $request->operation_type
            ]);

            return json_decode($response->getBody(), true);
        } else {
            // Handle the error as needed
            return response()->json(['error' => 'Failed to create invoice'], 500);
        }
    }

    public function createInvoice(array $payment_data)
    {


        $amount = isset($request->validated('Amount')) ? $request->validated('Amount') * 100 : ($request->header('X-Amount') * 100);


        $invoice_number = rand(10000, 99999);


        $private_key = file_get_contents(env('PRIVATE_KEY_PATH'));


        // Prepare the JSON body
        $json_body_parameter = [
            'Amount' => $amount,
            'Invoice' =>  $invoice_number,
            'TTL' => 15
        ];


        $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyResource = openssl_pkey_get_private($private_key);



        // Hash the JSON body
        //$jsonString = str_replace(["\n", "\r", "\t", " "], '', $json_body_parameter);

        openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        //$signature = hash('sha256',  $signature);
        // Encrypt the hashed string using the private key
        //openssl_private_encrypt($hashedString, $signature, $private_key);

        // Base64 encode the encrypted data
        $encryptedString = base64_encode($signature);

        // Define the full URL
        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/invoice/create";

        // Create a new Guzzle client
        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);

        // Make the HTTP POST request using Guzzle's Client
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/invoice/create',
                'X-Signature' => $encryptedString,
                'Subject' => '9001000000048983',
                'Accept-Language' => 'en'
            ],
            'json' => [
                'Amount' =>  $amount,
                'Invoice' =>  $invoice_number,
                'TTL' => 15
            ],
            'stream' => false,

            'debug' => true
        ]);


        // Check the response status and return the response body
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        } else {
            // Handle the error as needed
            return response()->json(['error' => 'Failed to create invoice'], 500);
        }
    }


    public function authenticateMerchant()
    {
        try {


            /*   $credintals = ['inputObj' => [
            'userName' => 'shafeech@xo',
            'password' => ' Mtn@123456789',
            'merchantGSM' => '963912345678'
        ]];/*
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',


        ])->post('https://services.mtnsyr.com:2021/authenticateMerchant');


        return $response->json();*/

            /* ['inputObj' => [
            'userName' => 'shafeech@xo',
            'password' => ' Mtn@123456789',
            'merchantGSM' => '1963912345678'
        ]];


        $full_url  = 'https://services.mtnsyr.com:2021/authenticateMerchant';



        $response = Http::withHeaders([
              'Content-Type' => 'application/json',


        ])->post($full_url,$credintals);


	
	*/
            //$ch = curl_init("https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken");
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            //$data = curl_exec($ch);
            //echo($data);
            //echo 'Curl error: ' . curl_error($ch);

            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_URL, 
            // "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken");
            // curl_setopt($ch, CURLOPT_HEADER, 0);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE); 
            // if(curl_exec($ch) === false)
            // {
            // echo 'Curl error: ' . curl_error($ch);
            // }
            // else
            // {
            // echo 'Operation completed without any errors';
            // }
            // curl_close($ch);

            // }
            //$full_url = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken";
            //  return $response = Http::post( $full_url);
            // }



            $full_url  = 'https://services.mtnsyr.com:2021/authenticateMerchant';

            $client = new Client([
                'verify' => true, // Disable SSL verification
            ]);

            $response = $client->post($full_url, [
                'json' => ['inputObj' => [
                    'userName' => 'shafeech@xo',
                    'password' => ' Mtn@123456789',
                    'merchantGSM' => '963912345678'
                ]], 'stream' => false
            ]);

            // Get the response body
            $response = json_decode($response->getBody(), true);

            // You can then return or use $responseBody as needed
            return $response;

            //  echo $response;

            // You can then return or use $responseBody as needed
            //return $response;


        } catch (\Exception $e) {

            return $e;
        }
    }
}
