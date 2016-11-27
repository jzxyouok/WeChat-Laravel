<?php namespace Orz\WeChat\Handler\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Orz\WeChat\Handler\Exception\WeChatException;
use Orz\WeChat\Handler\Pay;
use Orz\WeChat\Tool\DataFormat;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class WeChatPayBackVerifySign
{
    public function handle($request,Closure $next)
    {
        if($request->isMethod('post')){ //请求必须为post
            $body = $request->getContent();
            $body = DataFormat::decodeData($body,'xml');
            if(empty($body)){
                throw new WeChatException('WeChat pay callback data is empty',2003);
            }else{
                if($body['return_code'] == 'SUCCESS'){  //微信返回码为成功，验证sign，失败不做处理
                    $data = Cache::store('file')->get($body['out_trade_no']);
                    $data = json_decode($data,TRUE);

                    //TODO：验证微信是否可以接收
                    if(empty($data)){
                        $result_arr = array(
                            'return_code'=>'FAIL'
                        );
                        echo DataFormat::array2xml($result_arr);
                    }else{
                        $sign = Pay::sign($data,app('config')['wechat.default']['app']);
                        if($sign == $body['sign']){
                            $result_arr = array(
                                'return_code'=>'SUCCESS'
                            );
                            echo DataFormat::array2xml($result_arr);
                        }else{
                            $result_arr = array(
                                'return_code'=>'FAIL'
                            );
                            echo DataFormat::array2xml($result_arr);
                        }
                    }
                }

                return $next($request);
            }
        }else{
            throw new MethodNotAllowedException(array('post'));
        }
    }
}
