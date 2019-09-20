<?php


namespace App\HttpController\Api;


use App\Beans\Protocol;

class Callback extends Base
{
    /*
     *
     */
    function index()
    {
        $response = new Protocol();
        $proto = new Protocol($this->request()->getRequestParam());
        switch ($proto->getOpCode()){
            case Protocol::OP_CONNECT:{
                $response->setData('hello , welcome to easyswoole');
                break;
            }
        }
        $response->makeSignature('123456');
        $this->response()->write($response->__toString());
    }
}