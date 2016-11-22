<?php namespace Orz\WeChat\Handler;


use Orz\WeChat\Handler\Exception\WeChatPayException;
use Orz\WeChat\Tool\CURL;

class Pay
{
    const PREPAY_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    protected $_config;

    /**
     * Pay constructor.
     * @param $_config
     */
    public function __construct($_config)
    {
        $this->_config = $_config;
    }

    public function unifiedOrder($body,$orderNumber,$price,$openid = '')
    {
        $trade_type = strtoupper($this->_config['trade_type']);
        $order = array(
            'appid'=>$this->_config['app_id'],
            'mch_id'=>$this->_config['mch_id'],
            'device_info'=>'WEB',
            'nonce_str'=>str_random(32),
            'body'=>$body,
            'out_trade_no'=>$orderNumber,
            'total_fee'=>$price,
            'spbill_create_ip'=>$_SERVER['REMOTE_ADDR'],
            'notify_url'=>$this->_config['notify_url'],
            'trade_type'=>$trade_type,
        );
        if($trade_type == 'JSAPI'){
            $order['openid'] = $openid;
        }
        $order['sign'] = self::sign($order,$this->_config['mch_secret']);
        $xml = self::array2xml($order);
        $curl = new CURL();
        $result = $curl->post(self::PREPAY_URL,$xml,'xml');
        if($result['return_code'] == 'FAIL'){
            throw new WeChatPayException($result['return_msg'],1000);
        }else{
            return $result;
        }
    }
    protected static function sign($data,$mch_secrest,$algo = 'md5')
    {
        ksort($data);
        $list = array();
        foreach( $data as $key => $val ) {
            if( $val == '' )
                continue;
            $list[] = $key . '=' . $val;
        }
        $str = implode('&', $list) . '&key=' . $mch_secrest;

        if( $algo == 'md5' )
            return strtoupper(md5($str));
        else
            return strtoupper(sha1($str));
    }
    protected static function array2xml($array)
    {
        $xml = "<xml>\n";
        foreach( $array as $k => $v ) {
            $xml .= "\t<$k><![CDATA[$v]]></$k>\n";
        }
        $xml .= '</xml>';

        return $xml;
    }
}