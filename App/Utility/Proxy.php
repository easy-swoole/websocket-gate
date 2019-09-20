<?php


namespace App\Utility;


use App\Beans\Protocol;
use EasySwoole\HttpClient\HttpClient;

class Proxy
{
    public static function sendCommand(Protocol $protocol,string $url,float $timeout = 3.0):?Protocol
    {
        $client = new HttpClient($url);
        $client->setTimeout($timeout);
        $response = json_decode($client->post($protocol->toArray())->getBody(),true);
        if($response){
            return new Protocol(json_decode($response,true));
        }else{
            return null;
        }
    }
}