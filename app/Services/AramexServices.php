<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Config;

class AramexServices
{
    private $base_url;
    private $headers;
    private $request_client;
/**
*AramexServices constructor.
*@param Client $request_client
*/
public function __construct (Client $request_client)
{
    $this->request_client = $request_client;

    $this->base_url = config('app.aramex_base_url');

    $this->headers = [
    'Content-Type' => 'application/json',
    'Accept' => 'application/json'
    ];
}
/**
*@param $uri
*@param $method
*@param array $body
*@return false |mixed
*@throws GuzzleHttp\Exception\GuzzleException
*/
public function buildRequest($uri,$endpoint,$method,$data=[]){
    $full_url=$this->base_url.$endpoint.$uri;
    $request = new Request ($method, $full_url, $this->headers);
    if(!$data)
        return false;

    $response=$this->request_client->send($request, [
        'json' => $data
    ]);
    if ($response->getStatusCode() != 200) {

        return false;

    }
    $response = json_decode($response->getBody(),true);

    return $response;
}
/**
*@param $value
*@return mixed
*/
    public function fetchCities($endpoint,$data){
        return $response = $this->buildRequest("FetchCities",$endpoint,'POST',$data);
        }
    public function createPickup($endpoint,$data){
        return $response = $this->buildRequest("CreatePickup",$endpoint,'POST',$data);
        }    
    public function createShipments($endpoint,$data){
        return $response = $this->buildRequest("CreateShipments",$endpoint,'POST',$data);
        }
    public function trackShipments($endpoint,$data){
        return $response = $this->buildRequest("TrackShipments",$endpoint,'POST',$data);
        }
    public function cancelPickup($endpoint,$data){
        return $response = $this->buildRequest("CancelPickup",$endpoint,'POST',$data);
        }


}
