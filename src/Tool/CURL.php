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
    public function get($url,$return_format = 'json')
    {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 1,
        );
        curl_setopt_array($this->_ch, $options);
        $response = curl_exec($this->_ch);
        return self::formatResponse($response,$return_format);
    }
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
        return self::formatResponse($response,$return_format);
    }
    protected static function formatResponse($data,$format = 'json')
    {
        if( $format == 'json' ) {
            $ret = json_decode($data, true);
        } elseif( $format == 'xml' ) {
//            var_dump($data);
            $ret = @simplexml_load_string($data, null, LIBXML_NOCDATA);
            $ret = json_decode(json_encode($ret), true);
        } else {
            $ret = $data;
        }
        return $ret;
    }
}