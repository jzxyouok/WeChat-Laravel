<?php namespace Orz\WeChat\Tool;

class CURL
{
    protected $_ch;

    /**
     * CURL constructor.
     */
    public function __construct()
    {
        $this->_ch = curl_init();
    }

    /**
     * cURL get方法
     * @param string    $url
     * @param string $return_format 返回数据格式，json、xml、raw，除raw外均解析成为数组返回
     * @return mixed|\SimpleXMLElement
     */
    public function get($url,$return_format = 'json')
    {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 1,
        );
        curl_setopt_array($this->_ch, $options);
        $response = curl_exec($this->_ch);
        return DataFormat::decodeData($response,$return_format);
    }

    /**
     * cURL post方法
     * @param $url
     * @param $params
     * @param string $return_format
     * @return mixed|\SimpleXMLElement
     */
    public function post($url,$params,$return_format = 'json')
    {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 1,
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $params,
        );
        curl_setopt_array($this->_ch, $options);
        $response = curl_exec($this->_ch);
        return DataFormat::decodeData($response,$return_format);
    }
}