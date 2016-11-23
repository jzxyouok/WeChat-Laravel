<?php

namespace Orz\WeChat\Tool;


class DataFormat
{

    /**
     * 格式化数据
     * @param mixed $data   需格式化的数据
     * @param string $format    数据的格式
     * @return mixed|\SimpleXMLElement  不一定返回期望的数据，如解码后数据为空则会返回raw数据
     */
    public static function decodeData($data,$format = 'json')
    {
        if( $format == 'json' ) {
            $ret = json_decode($data, true);
        } elseif( $format == 'xml' ) {
            $ret = @simplexml_load_string($data, null, LIBXML_NOCDATA);
            $ret = json_decode(json_encode($ret), true);
        } else {
            $ret = $data;
        }
        if(empty($ret)){
            return $data;
        }else{
            return $ret;
        }
    }

    /**
     * 数组转xml
     * @param $array
     * @return string
     */
    public static function array2xml($array)
    {
        $xml = "<xml>\n";
        foreach( $array as $k => $v ) {
            $xml .= "\t<$k><![CDATA[$v]]></$k>\n";
        }
        $xml .= '</xml>';

        return $xml;
    }
}