<?php namespace Orz\WeChat\Handler;


use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Orz\WeChat\Handler\Exception\WeChatException;
use Orz\WeChat\Handler\Exception\WeChatOfficialException;
use Orz\WeChat\Tool\CURL;
use Orz\WeChat\Tool\DataFormat;


/**
 * Class Pay
 * @package Orz\WeChat\Handler
 */
class Pay
{

    /**
     *  微信文档中定义的 统一下单 接口地址
     */
    const PREPAY_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    /**
     * 微信配置文件
     * @var
     */
    protected $_config;

    /**
     * Pay constructor.
     * @param $_config
     */
    public function __construct($_config)
    {
        $this->_config = $_config;
    }

    /**
     * 生成 统一下单 订单，并将签名缓存，便于回调验证签名
     * @param string    $body  订单描述
     * @param string    $orderNumber   订单号
     * @param int   $price  价格
     * @param string    $openid 如果是公众号支付则必须有此值
     * @return array 微信官方返回数据，已转换成数组
     * @throws WeChatException  程序本身异常，配置或缓存未开启
     * @throws WeChatOfficialException  微信官方返回的错误
     */
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

        $xml = DataFormat::array2xml($order);
        $curl = new CURL();
        $result = $curl->post(self::PREPAY_URL,$xml,'xml');
        if(!is_array($result)){
            throw new WeChatException('Pay-unifiedOrder method return format error.',2000);
        }elseif($result['return_code'] == 'FAIL'){
            throw new WeChatOfficialException($result['return_msg'],5000);
        }else{
            //缓存签名，用于之后验证微信回调，过期时间15分钟
            $expiresAt = Carbon::now()->addMinutes(15);
            Cache::put($orderNumber,$order['sign'],$expiresAt);
            if(Cache::has($orderNumber)){
                return $result;
            }else{
                throw new WeChatException('Pay-unifiedOrder method cache sign fail.',2001);
            }
        }
    }

    /**
     * 支付第二步：APP端调起支付接口所需注入的对象
     * @param array $unifiedOrder 由预支付订单返回的微信官方数据数组
     * @return array
     */
    public function InjectObjectApp($unifiedOrder)
    {
        $data = array(
            'appid'=>$unifiedOrder['appid'],
            'partnerid'=>$unifiedOrder['mch_id'],
            'prepayid'=>$unifiedOrder['prepay_id'],
            'package'=>'Sign=WXPay',
            'noncestr'=>$unifiedOrder['nonce_str'],
            'timestamp'=>time(),
        );
        $data['sign'] = self::sign($data,$this->_config['mch_secret']);
        return $data;
    }

    /**
     * 支付第二步：WEB端调起支付接口所需注入的对象
     * @param array $unifiedOrder 由预支付订单返回的微信官方数据数组
     * @return array
     */
    public function InjectObjectWeb($unifiedOrder)
    {
        $data = array(
            'appId'=>$unifiedOrder['appid'],
            'signType'=>'MD5',
            'package'=>'prepay_id='.$unifiedOrder['prepay_id'],
            'nonceStr'=>$unifiedOrder['nonce_str'],
            'timeStamp'=>time(),
        );
        $data['paySign'] = self::sign($data,$this->_config['mch_secret']);
        return $data;
    }

    /**
     * 签名方法
     * @param array $data   订单数组
     * @param string $mch_secrest   商户key
     * @param string $algo  加密方式
     * @return string
     */
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
}