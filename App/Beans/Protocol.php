<?php


namespace App\Beans;


class Protocol extends Signature
{
    const OP_CONNECT = 1;
    const OP_DISCONNECT = 2;
    const OP_MSG = 3;
    const OP_BIND_UID = 4;

    const STATUS_OK = 0;
    const STATUS_ERROR = -1;

    protected $opCode;
    protected $status = self::STATUS_OK;
    protected $data;
    protected $fd;

    /**
     * @return mixed
     */
    public function getOpCode()
    {
        return $this->opCode;
    }

    /**
     * @param mixed $opCode
     */
    public function setOpCode($opCode): void
    {
        $this->opCode = $opCode;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getFd()
    {
        return $this->fd;
    }

    /**
     * @param mixed $fd
     */
    public function setFd($fd): void
    {
        $this->fd = $fd;
    }
}