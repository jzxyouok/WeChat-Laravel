<?php namespace Orz\WeChat;

use Orz\WeChat\Handler\AccessToken;
use Orz\WeChat\Handler\Exception\WeChatException;
use Orz\WeChat\Handler\JsSDK;
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
     * 应用新的配置
     * @param string $key   配置文件中的key
     * @return $this
     */
    public function apply($key)
    {
        $config = app('config')['wechat'][$key];
        if(empty($config['name'])){
            new WeChatException('apply config error,check config',2000);
        }else{
            $this->_config = $config;
        }
        return $this;
    }

    /**
     * 获取access_token
     * @return string
     */
    public function getAccessToken()
    {
        $token = new AccessToken($this->_config['web']);
        return $token->get();
    }


    /**
     * 获取JSSDK注入所需对象
     * @param string $url 接口所需的调用页面URL
     * @param bool|false $debug 是否开启debug模式，默认关闭
     * @return array
     */
    public function getJsSDK($url,$debug = false)
    {
        $js = new JsSDK($this->_config['web']);
        return $js->getJsSDK($url,$debug);
    }

    /**
     * 生成 统一下单 针对app开发
     * @param string $body  微信端订单上显示的产品名称
     * @param string $orderNumber   商户自定义订单号
     * @param int $price    产品价格，单位为分，必须为整形否则会报错
     * @return array  微信官方文档返回数据，已转成数组
     * @throws Handler\Exception\WeChatPayException 包含所有官方文档中提示可能出现的错误
     */
    public function getUnifiedOrderApp($body,$orderNumber,$price)
    {
        $pay = new Pay($this->_config['app']);
        return $pay->unifiedOrder($body,$orderNumber,$price);
    }

    /**
     * 生成 统一下单 针对web开发，公众号开发等等
     * @param string $body  微信端订单上显示的产品名称
     * @param string $orderNumber   商户自定义订单号
     * @param int $price    产品价格，单位为分，必须为整形否则会报错
     * @param string $openid    通过授权获得的请求支付的用户openid
     * @return array  微信官方文档返回数据，已转成数组
     * @throws Handler\Exception\WeChatPayException 包含所有官方文档中提示可能出现的错误
     */
    public function getUnifiedOrderWeb($body,$orderNumber,$price,$openid)
    {
        $pay = new Pay($this->_config['web']);
        return $pay->unifiedOrder($body,$orderNumber,$price,$openid);
    }
}