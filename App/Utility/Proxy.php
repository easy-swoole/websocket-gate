<?php


namespace App\Utility;


use App\Beans\Protocol;
use EasySwoole\Component\TableManager;
use EasySwoole\HttpClient\HttpClient;

class Proxy
{
    public static function sendCommand(Protocol $protocol,string $APP_ID,int $tryTimes = 1):?Protocol
    {
        $info = TableManager::getInstance()->get('APPLICATIONS')->get($APP_ID);
        if($info){
            $client = new HttpClient($info['CALLBACK_URL']);
            $client->setTimeout($info['CALLBACK_TIMEOUT']);
            $response = json_decode($client->post($protocol->toArray())->getBody(),true);
            if($response){
                return new Protocol(json_decode($response,true));
            }else{
                if($tryTimes < $info['CALLBACK_RETRY']){
                    return  self::sendCommand($protocol,$APP_ID,$tryTimes + 1);
                }
                return null;
            }
        }else{
            return null;
        }
    }
}