<?php namespace Orz\WeChat\Handler\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Orz\WeChat\Tool\DataFormat;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class WeChatPayBackVerifySign
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request,Closure $next)
    {
        if($request->isMethod('post')){ //请求必须为post
            $body = $request->getContent();
            $body = DataFormat::decodeData($body,'xml');
            if(empty($body)){
                Log::warning('illegal request in wechat pay back oauth.');
                return response('error.', 500);
            }else{
                if($body['return_code'] == 'SUCCESS'){  //微信返回码为成功，验证sign，失败不做处理
                    $sign = Cache::get($body['out_trade_no']);

                    //TODO：验证微信是否可以接收
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

                return $next($request);
            }
        }else{
            throw new MethodNotAllowedException(array('post'));
        }
    }
}
