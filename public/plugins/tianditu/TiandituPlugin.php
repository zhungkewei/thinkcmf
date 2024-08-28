<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\tianditu;

use cmf\lib\Plugin;

class TiandituPlugin extends Plugin
{
    public $info = [
        'name'        => 'Tianditu',
        'title'       => '天地图',
        'description' => '天地图',
        'status'      => 1,
        'author'      => 'ThinkCMF',
        'version'     => '1.0.0'
    ];

    public $hasAdmin = 0;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    public function adminDialogMapView()
    {
        $config = $this->getConfig();
        $this->assign('config', $config);
        return $this->fetch('widget');
    }

}
