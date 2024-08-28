<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
return [
    'map_secret_key' => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '地图密钥', // 表单的label标题
        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '', // 表单的默认值
        'tip'   => '请在https://console.tianditu.gov.cn/api/key获取', //表单的帮助提示
    ],
];
