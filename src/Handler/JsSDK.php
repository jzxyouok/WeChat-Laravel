<?php
namespace Orz\WeChat\Handler;


use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Orz\WeChat\Handler\Exception\WeChatException;
use Orz\WeChat\Handler\Exception\WeChatOfficialException;
use Orz\WeChat\Tool\CURL;

class JsSDK
{
    /**
     * 文档中的接口地址
     */
    const API_TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    /**
     * 微信配置数组
     * @var array
     */
    protected $_config;
    /**
     * access_token缓存名称，规则：appid+'jsapiticket'
     * @var string
     */
    protected $_cache_name;

    /**
     * AccessToken constructor.
     * @param $_config
     */
    public function __construct($_config)
    {
        $this->_config = $_config;
        $this->_cache_name = $this->_config['app_id'].'jsapiticket';
    }

    /**
     * 获取JSSDK
     * @param string $url 调用页面URL
     * @param bool|false $debug 是否开启debug模式
     * @return array
     */
    public function getJsSDK($url,$debug = false)
    {
        $noncestr = str_random(32);
        $jsapi_ticket = $this->getTicket();
        $timestamp = time();
        $sign_data = array(
            'noncestr'=>$noncestr,
            'jsapi_ticket'=>$jsapi_ticket,
            'timestamp'=>$timestamp,
            'url'=>$url,
        );
        $sign = $this->sign($sign_data);
        return array(
            'debug'=>$debug,
            'appId'=>$this->_config['app_id'],
            'timestamp'=>$timestamp,
            'nonceStr'=>$noncestr,
            'signature'=>$sign,
            'jsApiList'=>array(
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'onMenuShareQZone',
                'startRecord',
                'stopRecord',
                'onVoiceRecordEnd',
                'playVoice',
                'pauseVoice',
                'stopVoice',
                'onVoicePlayEnd',
                'uploadVoice',
                'downloadVoice',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage',
                'translateVoice',
                'getNetworkType',
                'openLocation',
                'getLocation',
                'hideOptionMenu',
                'showOptionMenu',
                'hideMenuItems',
                'showMenuItems',
                'hideAllNonBaseMenuItem',
                'showAllNonBaseMenuItem',
                'closeWindow',
                'scanQRCode',
                'chooseWXPay',
                'openProductSpecificView',
                'addCard',
                'chooseCard',
                'openCard',
            ),
        );
    }

    /**
     * 获取jsapi_ticket，如无必要，勿直接调用
     * @return string
     * @throws WeChatException 程序本身异常，配置或缓存未开启
     * @throws WeChatOfficialException 微信官方返回的错误
     */
    public function getTicket()
    {
        if(Cache::has($this->_cache_name)){
            return Cache::get($this->_cache_name);
        }else{
            return $this->getTicketFromURL();
        }
    }

    /**
     * 通过微信接口地址获取jsapi_ticket
     * @return string
     * @throws WeChatException 程序本身异常，配置或缓存未开启
     * @throws WeChatOfficialException 微信官方返回的错误
     */
    protected function getTicketFromURL()
    {
        $params = array(
            'access_token'=>$this->getAccessToken(),
            'type'=>'jsapi',
        );
        $url = self::API_TICKET_URL.'?'.http_build_query($params);
        $cURL = new CURL();
        $result = $cURL->get($url);
        if(!is_array($result)){
            throw new WeChatException('JsSDK-getTicketFromURL method return format error.',3000);
        }elseif(!empty($result['errcode'])){
            throw new WeChatOfficialException($result['errmsg'],5000);
        }else{
            $ticket = $result['ticket'];
            //缓存ticket，全局调用，过期时间120分钟(7200秒)
            $expiresAt = Carbon::now()->addMinutes(120);
            Cache::put($this->_cache_name,$ticket,$expiresAt);
            if(Cache::has($this->_cache_name)){
                return $ticket;
            }else{
                throw new WeChatException('JsSDK-getTicketFromURL method cache ticket fail',3001);
            }
        }

    }

    /**
     * 工具方法，调用AccessToken类获取token
     * @return string
     */
    protected function getAccessToken()
    {
        $token = new AccessToken($this->_config);
        return $token->get();
    }

    /**
     * 签名方法
     * @param array $data 签名数组
     * @return string
     */
    protected static function sign($data)
    {
        ksort($data);
        $str = '';
        foreach($data as $key=>$value)
        {
            $str .= $key.'='.$value.'&';
        }
        $str = substr($str,0,count($str)-2);
        return sha1($str);
    }
}