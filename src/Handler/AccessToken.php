<?php

namespace Orz\WeChat\Handler;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Orz\WeChat\Handler\Exception\WeChatException;
use Orz\WeChat\Handler\Exception\WeChatOfficialException;
use Orz\WeChat\Tool\CURL;

class AccessToken
{
    /**
     * 文档中的接口地址
     */
    const ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token';

    /**
     * 微信配置数组
     * @var array
     */
    protected $_config;
    /**
     * access_token缓存名称，规则：appid+'accesstoken'
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
        $this->_cache_name = $this->_config['app_id'].'accesstoken';
    }

    /**
     * 获取access_token
     * @return string access_token
     * @throws WeChatException 程序本身异常，配置或缓存未开启
     * @throws WeChatOfficialException 微信官方返回的错误
     */
    public function get()
    {
        if(Cache::has($this->_cache_name)){
            return Cache::get($this->_cache_name);
        }else{
            return $this->getFromURL();
        }
    }

    /**
     * 通过微信接口获得access_token
     * @return string access_token
     * @throws WeChatException 程序本身异常，配置或缓存未开启
     * @throws WeChatOfficialException 微信官方返回的错误
     */
    protected function getFromURL()
    {
        $params = array(
            'grant_type'=>'client_credential',
            'appid'=>$this->_config['app_id'],
            'secret'=>$this->_config['app_secret'],
        );
        $url = self::ACCESS_TOKEN_URL.'?'.http_build_query($params);
        $cURL = new CURL();
        $result = $cURL->get($url);
        if(!is_array($result)){
            throw new WeChatException('AccessToken-get method return format error.',1000);
        }elseif(!empty($result['errcode'])){
            throw new WeChatOfficialException($result['errmsg'],5000);
        }else{
            $access_token = $result['access_token'];
            //缓存token，全局调用，过期时间120分钟(7200秒)
            $expiresAt = Carbon::now()->addMinutes(120);
            Cache::put($this->_cache_name,$access_token,$expiresAt);
            if(Cache::has($this->_cache_name)){
                return $access_token;
            }else{
                throw new WeChatException('AccessToken-get method cache token fail',1001);
            }
        }
    }

}