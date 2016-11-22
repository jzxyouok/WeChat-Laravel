<?php namespace Orz\WeChat;

use Illuminate\Support\ServiceProvider;

class WeChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $config = __DIR__.'/config/wechat.php';
        $this->mergeConfigFrom($config,'wechat');

        $this->app->bind('wechat',function(){
            return new WeChat(app('config')['wechat.xinxianjupin_app']);
        });
    }
}
