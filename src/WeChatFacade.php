<?php namespace Orz\WeChat;

use Illuminate\Support\Facades\Facade;

class WeChatFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wechat';
    }
}
