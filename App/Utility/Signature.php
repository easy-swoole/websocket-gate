<?php


namespace App\Utility;


class Signature
{
    public static function make(array $data,string $secret)
    {
        return md5(implode($data).$secret);
    }
}