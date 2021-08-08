<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: kane <chengjin005@163.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\controller\HomeBaseController;
use cmf\lib\Storage;
use cmf\lib\Upload;
use think\exception\HttpResponseException;
use think\Response;

/**
 * 百度编辑器文件上传处理控制器
 * Class Ueditor
 * @package app\asset\controller
 */
class UeditorController extends HomeBaseController
{

    private $stateMap = [ //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE"           => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED"        => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED"   => "文件类型不允许",
        "ERROR_CREATE_DIR"         => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE"  => "目录没有写权限",
        "ERROR_FILE_MOVE"          => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND"     => "找不到上传文件",
        "ERROR_WRITE_CONTENT"      => "写入文件内容错误",
        "ERROR_UNKNOWN"            => "未知错误",
        "ERROR_DEAD_LINK"          => "链接不可用",
        "ERROR_HTTP_LINK"          => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE"   => "链接contentType不正确"
    ];

    /**
     * 初始化
     */
    public function initialize()
    {
        $adminId = cmf_get_current_admin_id();
        $userId  = cmf_get_current_user_id();
        if (empty($adminId) && empty($userId)) {
            $this->error("非法上传！");
        }
    }

    /**
     * 处理上传处理
     */
    public function upload()
    {
//        error_reporting(E_ERROR);
//        header("Content-Type: text/html; charset=utf-8");

        $action = $this->request->param('action');

        switch ($action) {

            case 'config':
                $result = $this->ueditorConfig();
                break;

            /* 上传图片 */
            case 'uploadimage':
                $result = $this->ueditorUpload("image");
                break;
            /* 上传涂鸦 */
            case 'uploadscrawl':
                $result = $this->ueditorUpload("image");
                break;
            /* 上传视频 */
            case 'uploadvideo':
                $result = $this->ueditorUpload("video");
                break;
            /* 上传文件 */
            case 'uploadfile':
                $result = $this->ueditorUpload("file");
                break;

            /* 列出图片 */
            case 'listimage':
                $result = "";
                break;
            /* 列出文件 */
            case 'listfile':
                $result = "";
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = $this->_get_remote_image();
                break;

            default:
                $result = json_encode(['state' => '请求地址出错']);
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"]) && false) {//TODO 跨域上传
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode([
                    'state' => 'callback参数不合法'
                ]);
            }
        } else {
            $response = Response::create(json_decode($result,true),'json');
            throw new HttpResponseException($response);
        }
    }


    /**
     * 获取远程图片
     */
    private function _get_remote_image()
    {
        
        $source = $this->request->param('source/a');
        
        
        $item              = [
            "state"    => "",
            "url"      => "",
            "size"     => "",
            "title"    => "",
            "original" => "",
            "source"   => ""
        ];
        $date              = date("Ymd");
        $uploadSetting     = cmf_get_upload_setting();
        $uploadMaxFileSize = $uploadSetting['file_types']["image"]['upload_max_filesize'];
        $uploadMaxFileSize = empty($uploadMaxFileSize) ? 2048 : $uploadMaxFileSize;//默认2M
        $allowedExits      = explode(',', $uploadSetting['file_types']["image"]["extensions"]);
        $strSavePath       = ROOT_PATH . 'public' . DS . 'upload' . DS . "ueditor" . DS . $date . DS;
        //远程抓取图片配置
        $config = [
            "savePath"   => $strSavePath,            //保存路径
            "allowFiles" => $allowedExits,// [".gif", ".png", ".jpg", ".jpeg", ".bmp"], //文件允许格式
            "maxSize"    => $uploadMaxFileSize                    //文件大小限制，单位KB
        ];
        
        $storageSetting = cmf_get_cmf_settings('storage');
        
        
        $list = [];
        foreach ($source as $imgUrl) {
            $return_img           = $item;
            $return_img['source'] = $imgUrl;
            $imgUrl               = htmlspecialchars($imgUrl);
            $imgUrl               = str_replace("&amp;", "&", $imgUrl);
            //http开头验证
            if (strpos($imgUrl, "http") !== 0) {
                $return_img['state'] = $this->stateMap['ERROR_HTTP_LINK'];
                array_push($list, $return_img);
                continue;
            }
            
            //获取请求头
            // is_sae()
            
            if (!cmf_is_sae()) {//SAE下无效
                $heads = get_headers($imgUrl);
                
                //死链检测
                if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
                    $return_img['state'] = $this->stateMap['ERROR_DEAD_LINK'];
                    array_push($list, $return_img);
                    continue;
                }
            }
            
            //格式验证(扩展名验证和Content-Type验证)
            ///判断是否是从微信浏览器获取的图片
            $regx = '"https://mmbiz.qpic.cn/mmbiz_\S*(wx_co=1|wx_lazy=1)"';
            preg_match_all($regx, $imgUrl, $result);
            $wechatUrl = '';
            $fileType  = del_dot(del_as_str(strtolower(strrchr($imgUrl, '.'))));
            if ($result[0]) {
                $wechatUrl = $result[0][0];
                preg_match_all("(mmbiz_jpg|mmbiz_png|mmbiz_gif|mmbiz_svg)", $wechatUrl, $re);
                $fType = str_replace("mmbiz_", "", $re[0][0]);
                if ($fType) {
                    $fileType = $fType;
                }
            }
            
            if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
                $return_img['state'] = $this->stateMap['ERROR_HTTP_CONTENTTYPE'];
                array_push($list, $return_img);
                continue;
            }
            
            //打开输出缓冲区并获取远程图片
            ob_start();
            if ($wechatUrl) {
                //$img = (save_wechat_pics(str_replace('"','',$wechatUrl),$fileType));
                $img = file_get_contents($wechatUrl);
            } else {
                
                
                $context = stream_context_create(
                    [
                        'http' => [
                            'follow_location' => false // don't follow redirects
                        ]
                    ]
                );
                //请确保php.ini中的fopen wrappers已经激活
                readfile($imgUrl, false, $context);
                $img = ob_get_contents();
            }
            ob_end_clean();
            
            
            //大小验证
            $uriSize   = strlen($img); //得到图片大小
            $allowSize = 1024 * $config['maxSize'];
            if ($uriSize > $allowSize) {
                $return_img['state'] = $this->stateMap['ERROR_SIZE_EXCEED'];
                array_push($list, $return_img);
                continue;
            }
            $savePath = $config['savePath'];
            
            if ($wechatUrl) {
                $file = md5($imgUrl) . "." . $fileType;
                
            } else {
                $file = uniqid() . del_as_str(strrchr($imgUrl, '.')) ?: strrchr($imgUrl, '.');
            }
            
            $tmpName = $savePath . $file;
            
            
            //创建保存位置
            if (!file_exists($savePath)) {
                mkdir("$savePath", 0777, true);
            }
            
            $file_write_result = cmf_file_write($tmpName, $img);
            
            if ($file_write_result) {
                if ($storageSetting['type'] != 'Local') {
                    
                    $storage             = new Storage();
                    $url                 = $storage->upload($file, $tmpName);
                    $return_img['state'] = 'SUCCESS';
                    $return_img['url']   = $url['url'];
                    array_push($list, $return_img);
                } else {
                    
                    $file = $strSavePath . $file;
                    
                    $return_img['state'] = 'SUCCESS';
                    $return_img['url']   = $file;
                    array_push($list, $return_img);
                }
            } else {
                $return_img['state'] = $this->stateMap['ERROR_WRITE_CONTENT'];
            }
            array_push($list, $return_img);
        }
        
        return json_encode([
            'state' => count($list) ? 'SUCCESS' : 'ERROR',
            'list'  => $list
        ],JSON_UNESCAPED_SLASHES);
    }

    /**
     * 文件上传
     * @param string $fileType 文件类型
     * @return string
     */
    private function ueditorUpload($fileType = 'image')
    {
        $uploader = new Upload();
        $uploader->setFileType($fileType);
        $uploader->setFormName('upfile');
        $result = $uploader->upload();

        if ($result === false) {
            return json_encode([
                'state' => $uploader->getError()
            ]);
        } else {
            return json_encode([
                'state'    => 'SUCCESS',
                'url'      => $result['url'],
                'title'    => $result['name'],
                'original' => $result['name']
            ]);
        }

    }

    /**
     * 获取百度编辑器配置
     */
    private function ueditorConfig()
    {
        $config_text    = preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(WEB_ROOT . "static/js/ueditor/config.json"));
        $config         = json_decode($config_text, true);
        $upload_setting = cmf_get_upload_setting();

        $config['imageMaxSize']    = $upload_setting['file_types']['image']['upload_max_filesize'] * 1024;
        $config['imageAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(",", $upload_setting['file_types']['image']['extensions']));
        $config['scrawlMaxSize']   = $upload_setting['file_types']['image']['upload_max_filesize'] * 1024;
//
        $config['catcherMaxSize']    = $upload_setting['file_types']['image']['upload_max_filesize'] * 1024;
        $config['catcherAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(",", $upload_setting['file_types']['image']['extensions']));

        $config['videoMaxSize']    = $upload_setting['file_types']['video']['upload_max_filesize'] * 1024;
        $config['videoAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(",", $upload_setting['file_types']['video']['extensions']));

        $config['fileMaxSize']    = $upload_setting['file_types']['file']['upload_max_filesize'] * 1024;
        $config['fileAllowFiles'] = array_map([$this, 'ueditorExtension'], explode(",", $upload_setting['file_types']['file']['extensions']));

        return json_encode($config);
    }

    /**
     * 格式化后缀
     * @param $str
     * @return string
     */
    private function ueditorExtension($str)
    {
        return "." . trim($str, '.');
    }

}
