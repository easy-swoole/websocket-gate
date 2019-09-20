<?php


namespace App\Beans;


class Protocol extends Signature
{
    const OP_CONNECT = 1;
    const OP_DISCONNECT = 2;
    const OP_MSG = 3;
    const OP_BIND_UID = 4;
}