<?php namespace Orz\WeChat\Facade;

use Illuminate\Support\Facades\Facade;

class WeChat extends Facade
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
