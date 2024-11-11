<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2023 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com> Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace cmf\middleware;

use Closure;
use think\App;
use think\Config;
use think\Cookie;
use think\Lang;
use think\Request;
use think\Response;

/**
 * 多语言加载
 */
class LangDetect
{
    protected $config;

    public function __construct(protected App $app, protected Lang $lang, Config $config)
    {
        $this->config = $lang->getConfig();
    }

    /**
     * 路由初始化（路由规则注册）
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查是否开启多语言功能
        if (empty($this->config['home_multi_lang']) && empty($this->config['admin_multi_lang'])) {
            return $next($request);
        }

        if (empty($this->config['multi_lang_mode'])) {
            $this->config['multi_lang_mode'] = 1;
        }

        $langSet = '';

        switch ($this->config['multi_lang_mode']) {
            case 1: // URL模式
            {
                $pathInfo    = $request->pathinfo();
                $pathInfoExt = $request->ext();
                if (!empty($pathInfoExt)) {
                    $pathInfo = preg_replace("/\.$pathInfoExt$/", '', $pathInfo);
                }
                $pathInfoArr = explode('/', $pathInfo);
                if (!empty($pathInfoArr)) {
                    $mLangSet = $pathInfoArr[0];
                    if (isset($this->config['accept_language'][$mLangSet])) {
                        $mLangSet = $this->config['accept_language'][$mLangSet];
                    }

                    if (in_array($mLangSet, $this->config['allow_lang_list'])) {
                        $langSet = $mLangSet;
                    }
                }

                if (!empty($langSet)) {
                    array_shift($pathInfoArr);
                    $newPathInfo = join('/', $pathInfoArr);
                    if ($pathInfoExt) {
                        $newPathInfo = "$newPathInfo.$pathInfoExt";
                    }

                    $request->setPathinfo($newPathInfo);
                } else {
                    // $langSet = $this->config['default_lang'];
                }

                break;
            }
            case 2: // 域名模式
            {
                $domain    = $request->domain();
                $domainArr = explode('.', $domain);
                $mLangSet  = $domainArr[0];

                if (isset($this->config['lang_domain_list'][$request->host()])) {
                    $mLangSet = $this->config['lang_domain_list'][$request->host()];
                }

                if (in_array($mLangSet, $this->config['allow_lang_list'])) {
                    $langSet = $mLangSet;
                }
                break;
            }
        }

        $this->detect($request, $langSet);

        return $next($request);
    }

    /**
     * 自动侦测设置获取语言选择
     * @access protected
     * @param Request $request
     * @return string
     */
    protected function detect(Request $request, $langSet): string
    {
        // 自动侦测设置获取语言选择
        if (empty($langSet)) {
            if ($request->get($this->config['detect_var'])) {
                // url中设置了语言变量
                $langSet = $request->get($this->config['detect_var']);
            } elseif ($request->header($this->config['header_var'])) {
                // Header中设置了语言变量
                $langSet = $request->header($this->config['header_var']);
            } elseif ($request->cookie($this->config['cookie_var'])) {
                // Cookie中设置了语言变量
                $langSet = $request->cookie($this->config['cookie_var']);
            } elseif ($request->server('HTTP_ACCEPT_LANGUAGE')) {
                // 自动侦测浏览器语言
                $langSet = $request->server('HTTP_ACCEPT_LANGUAGE');
            }

            if (preg_match('/^([a-z\d\-]+)/i', $langSet, $matches)) {
                $langSet = strtolower($matches[1]);
                if (isset($this->config['accept_language'][$langSet])) {
                    $langSet = $this->config['accept_language'][$langSet];
                }
            } else {
                $langSet = $this->lang->getLangSet();
            }
        }

        if (empty($this->config['allow_lang_list']) || in_array($langSet, $this->config['allow_lang_list'])) {
            // 合法的语言
            $this->lang->setLangSet($langSet);
        } else {
            $langSet = $this->lang->getLangSet();
        }

//        if (!empty($langSet)) {
////            $oldHelder                              = $request->header();
////            $oldHelder[$this->config['header_var']] = $langSet;
////            $request->withHeader($oldHelder);
//            $this->saveToCookie($this->app->cookie, $langSet);
//        }

        return $langSet;
    }

    /**
     * 保存当前语言到Cookie
     * @access protected
     * @param Cookie $cookie  Cookie对象
     * @param string $langSet 语言
     * @return void
     */
    protected function saveToCookie(Cookie $cookie, string $langSet): void
    {
        if ($this->config['use_cookie']) {
            $cookie->set($this->config['cookie_var'], $langSet);
        }
    }
}
