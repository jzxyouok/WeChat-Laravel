<?php namespace Orz\WeChat;

use Orz\WeChat\Handler\Pay;

class WeChat
{
    protected $_config;

    /**
     * WeChat constructor.
     * @param $_config
     */
    public function __construct($_config)
    {
        $this->_config = $_config;
    }
    public function getUnifiedOrder()
    {
        $pay = new Pay($this->_config);
        return $pay->unifiedOrder('test','123456',1);
    }
    public function test()
    {
        dd($this->_config);
    }

}