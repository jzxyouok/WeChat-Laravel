<?php namespace Orz\WeChat\Handler\Exception;


/**
 * Class WeChatPayException
 * @package Orz\WeChat\Handler\Exception
 */
class WeChatException extends \Exception
{

    /**
     * @param string $message
     * @param int $code
     */
    public function __construct($message,$code = 0)
    {
        parent::__construct($message,$code);
    }
}