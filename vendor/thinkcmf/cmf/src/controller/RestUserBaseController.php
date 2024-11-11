<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

class RestUserBaseController extends RestBaseController
{

    public function initialize()
    {
        hook('home_init');
        if (empty($this->user)) {
            $this->error(['code' => 10001, 'msg' => lang('登录已失效！')]);
        }

    }

}
