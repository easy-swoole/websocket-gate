<?php
namespace EasySwoole\EasySwoole;


use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use Swoole\Table;

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
         * 注册onMessage 回调
         */

        $register->set($register::onMessage,function (){

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