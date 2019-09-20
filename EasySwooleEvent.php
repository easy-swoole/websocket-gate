<?php
namespace EasySwoole\EasySwoole;


use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use Swoole\Table;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        /*
         * 初始化应用信息存储的Table
         */
        TableManager::getInstance()->add('APPLICATIONS',[
            'APP_ID'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>16
            ],
            'APP_SECRET'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>32
            ],
            'CALLBACK'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>128
            ],
        ]);
        $apps = Config::getInstance()->getConf('APPLICATIONS');
        foreach ($apps as $app){
            TableManager::getInstance()->get('APPLICATIONS')->set($app['APP_ID'],$app);
        }

        /*
         * 注册onMessage onHandShake 回调
         */

        $register->set($register::onMessage,function (Server $server, Frame $frame){

        });

        $register->set($register::onHandShake,function (\Swoole\Http\Request $request,\Swoole\Http\Response $response){
            /*
             * 应用鉴权
             */

            /*
             * 一下信息为RFC规定握手流程
             */
            if(!isset($request->header['sec-websocket-key'])){
                $response->end('sec-websocket-key error');
                return ;
            }
            $secWebSocketKey = $request->header['sec-websocket-key'];
            $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
            if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
                $response->end('sec-websocket-key error');
                return ;
            }
            $key = base64_encode(sha1(
                $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                true
            ));
            $headers = [
                'Upgrade' => 'websocket',
                'Connection' => 'Upgrade',
                'Sec-WebSocket-Accept' => $key,
                'Sec-WebSocket-Version' => '13',
            ];
            if (isset($request->header['sec-websocket-protocol'])) {
                $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
            }
            foreach ($headers as $key => $val) {
                $response->header($key, $val);
            }
            $response->status(101);
            $response->end();
        });
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}