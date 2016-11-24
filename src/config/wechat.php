<?php

return [
    /*
     * 可以自定义其他的配置，配置结构与key值不要改变，改变后可调用apply方法使用新的配置
     * */
    'default'=>[
        'name'=>'', //别称，当使用非默认配置时会验证这个值
        'web'=>[
            'app_id'=>'',
            'app_secret'=>'',
            'mch_id'=>'',
            'mch_secret'=>'',
            'trade_type'=>'JSAPI',
            'notify_url'=>'',
        ],
        'app'=>[
            'app_id'=>'',
            'app_secret'=>'',
            'mch_id'=>'',
            'mch_secret'=>'',
            'trade_type'=>'',
            'notify_url'=>'',
        ],
    ]
];
