<?php
namespace EasySwoole\EasySwoole;


use App\Beans\Protocol;
use App\Utility\Proxy;
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
            'CALLBACK_URL'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>128
            ],
            'CALLBACK_TIMEOUT'=>[
                'type'=>Table::TYPE_FLOAT,
                'size'=>4
            ],
            'CALLBACK_RETRY'=>[
                'type'=>Table::TYPE_INT,
                'size'=>1
            ],
            'MAX_CONNECTION'=>[
                'type'=>Table::TYPE_INT,
                'size'=>8
            ],
            "CONNECTION_NUM"=>[
                'type'=>Table::TYPE_INT,
                'size'=>8
            ]
        ]);
        $apps = Config::getInstance()->getConf('APPLICATIONS');
        $maxConnection = 0;
        foreach ($apps as $app){
            TableManager::getInstance()->get('APPLICATIONS')->set($app['APP_ID'],$app);
            TableManager::getInstance()->get('APPLICATIONS')->set($app['APP_ID'],[
                'CONNECTION_NUM'=>0
            ]);
            $maxConnection += $app['MAX_CONNECTION'];
        }

        TableManager::getInstance()->add('FD_LIST',[
            'APP_ID'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>16
            ],
            'UID'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>16
            ],
        ],$maxConnection);

        TableManager::getInstance()->add('UID_LIST',[
            'FD'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>16
            ]
        ],$maxConnection);

        /*
         * 注册onMessage onHandShake onClose 回调
         */
        $register->set($register::onMessage,function (Server $server, Frame $frame){
            $info = TableManager::getInstance()->get('FD_LIST')->get($frame->fd);
            /*
             * 推送消息回去应用callback url
             */
        });

        $register->set($register::onClose,function (Server $server, int $fd, int $reactorId){
            $info = TableManager::getInstance()->get('FD_LIST')->get($fd);
            if($info){
                TableManager::getInstance()->get('FD_LIST')->del($fd);
                TableManager::getInstance()->get('UID_LIST')->del($info['APP_ID'].$info['UID']);
                //推送对应的消息给应用回调URL
            }
        });

        $register->set($register::onHandShake,function (\Swoole\Http\Request $request,\Swoole\Http\Response $response){
            $msg = null;
            /*
             * 应用鉴权
             */
            if(!isset($request->get['APP_ID'])){
                $response->status(400);
                $response->end('APP_ID is require');
                return;
            }
            $appConfig = TableManager::getInstance()->get('APPLICATIONS')->get($request->get['APP_ID']);
            if(!$appConfig){
                $response->status(400);
                $response->end('APP_ID not exist');
                return;
            }
            $data = $request->get;
            unset($data['APP_ID']);
            $protocol = new Protocol([
                'opCode'=>Protocol::OP_CONNECT,
                "APP_ID"=>$request->get['APP_ID'],
                "data"=>$data,
                "fd"=>$request->fd
            ]);
            $protocol->makeSignature($appConfig['APP_SECRET']);
            $apiResponse = Proxy::sendCommand($protocol,$appConfig['APP_ID']);
            if($apiResponse == null){
                $response->status(502);
                return;
            }
            $msg = $apiResponse->getData();
            if($apiResponse->getStatus() === Protocol::STATUS_ERROR) {
                $response->status(400);
                $response->end($msg);
                return;
            }
            /*
             * 以下信息为RFC规定握手流程
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
            /*
             * FD映射标记
             */
            /*
             * 可能握手的时候就回复了信息
             */
            if(!empty($msg)){
                ServerManager::getInstance()->getSwooleServer()->push($request->fd,$apiResponse->getData());
            }
        });
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {

    }
}