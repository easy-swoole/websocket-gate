<?php


namespace App\HttpController\Api;


use EasySwoole\Http\AbstractInterface\Controller;

class Base extends Controller
{

    function index()
    {
        $this->actionNotFound('index');
    }
}