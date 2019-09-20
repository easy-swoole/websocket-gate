<?php


namespace App\Beans;


use EasySwoole\Spl\SplBean;

class Signature extends SplBean
{
    protected $signature;
    protected $signatureTime;

    public function __construct(array $data = null)
    {
        parent::__construct($data, true);
    }

    protected function initialize(): void
    {
        if(empty($this->signatureTime)){
            $this->signatureTime = time();
        }
    }

    function makeSignature(string $key):string
    {
        $data = $this->toArray();
        unset($data['signature']);
        ksort($data);
        $this->signature = md5(implode($data).$this->signatureTime.$key);
        return $this->signature;
    }

    function checkSignature(string $key,$ttl = 5):bool
    {
        if(time() - $this->signatureTime > $ttl){
            return false;
        }
        $data = $this->toArray();
        unset($data['signature']);
        ksort($data);
        return $this->signature === md5(implode($data).$this->signatureTime.$key);
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return mixed
     */
    public function getSignatureTime()
    {
        return $this->signatureTime;
    }

}