<?php

// +----------------------------------------------------------------------
// |Author: 翼小菜
// +----------------------------------------------------------------------
// |Description: 验证码
// +----------------------------------------------------------------------
// |更多功能可联系QQ314688769
// +----------------------------------------------------------------------
namespace api\admin\controller;
use cmf\controller\RestBaseController;
use think\captcha\Captcha;

class CaptchaController extends RestBaseController
{
    public function index(Captcha $captcha)
    {
        $config  = [
            // 验证码字体大小(px)
            'fontSize' => 12,
            // 验证码图片高度
            'imageH'   => 38,
            // 验证码图片宽度
            'imageW'   => 120,
            // 验证码位数
            'length'   => 4,
            // 背景颜色
            'bg'       => [255, 255, 255],
        ];
        $request = request();

        $fontSize = $request->param('font_size', 12, 'intval');
        if ($fontSize > 8 && $fontSize < 100) {
            $config['fontSize'] = $fontSize;
        }

        $imageH = $request->param('height', '');
        if ($imageH != '' && $imageH < 100) {
            $config['imageH'] = intval($imageH);
        }

        $imageW = $request->param('width', '');
        if ($imageW != '' && $imageW < 200) {
            $config['imageW'] = intval($imageW);
        }

        $length = $request->param('length', 4, 'intval');
        if ($length > 2 && $length <= 100) {
            $config['length'] = $length;
        }

        $bg = $request->param('bg', '');

        if (!empty($bg)) {
            $bg = explode(',', $bg);
            array_walk($bg, 'intval');
            if (count($bg) > 2 && $bg[0] < 256 && $bg[1] < 256 && $bg[2] < 256) {
                $config['bg'] = $bg;
            }
        }

        $id = $request->param('id', 0, 'intval');
        if ($id > 5 || empty($id)) {
            $id                   = '';
            $config['captcha_id'] = $id;
        }

        $response = hook_one('captcha_image', $config);
        if (empty($response)) {
            config($config, 'captcha');
            $response = $captcha->create(null);
        }
        @ob_clean();// 清除输出缓存
        // 直接调用此/api/admin/captcha
        return $response;
    }
}
