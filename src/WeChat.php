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

    /**
     * 生成 统一下单
     * @param string $body  微信端订单上显示的产品名称
     * @param string $orderNumber   商户自定义订单号
     * @param int $price    产品价格，单位为分，必须为整形否则会报错
     * @param string $openid    如果为公众平台支付，则必须传此参数
     * @return array  微信官方文档返回数据，已转成数组
     * @throws Handler\Exception\WeChatPayException 包含所有官方文档中提示可能出现的错误
     */
    public function getUnifiedOrder($body,$orderNumber,$price,$openid = '')
    {
        $pay = new Pay($this->_config);
        return $pay->unifiedOrder($body,$orderNumber,$price,$openid);
    }
}