<?php
// +----------------------------------------------------------------------
// | wuzhicms [ 五指互联网站内容管理系统 ]
// | Copyright (c) 2014-2015 http://www.wuzhicms.com All rights reserved.
// | Licensed ( http://www.wuzhicms.com/licenses/ )
// | Author: wangcanjia <phpip@qq.com>
// +----------------------------------------------------------------------
defined('IN_WZ') or exit('No direct script access allowed');
/**
 * M/F/V 路由
 */
final class WUZHI_application {
    private $_m;
    private $_f;
    private $_v;
    public function __construct() {
        self::setconfig();
        define('M',$this->_m);
        define('F',$this->_f);
        define('V',$this->_v);
    }
    private function setconfig() {
        $route_config = get_config('route_config','default');
        $this->_m = input('m') ? input('m') : $route_config['m'];
        $this->_f = input('f') ? input('f') : $route_config['f'];
        $this->_v = input('v') ? input('v') : $route_config['v'];
    }
    public function run() {
        $file = $this->load_file();
        if(!defined('IN_ADMIN')) {
            if(CLOSE) {
                $siteconfigs = get_cache('siteconfigs');
                MSG($siteconfigs['close_reason']);
            }
        }
        if (method_exists($file, V)) {
            if (preg_match('/^[_]/i', V)) {
                exit('You are visiting the action is to protect the private action');
            } else {
                call_user_func(array($file, V));
            }
        } elseif(class_exists($GLOBALS['_CLASS_NAME_'],FALSE)) {
            exit('Action:'.V.' not exists.');
        }
    }

    /**
     * 加载文件
     * @param string $filename
     * @param string $app
     * @return obj
     */
    public static function load_file($filename = '', $app = '', $param = '') {
        static $static_file = array();
        if(isset($GLOBALS['_su']) && $GLOBALS['_su']== _SU) {
            $_admin_dir = '/admin';
        } else {
            $_admin_dir = '';
        }
        //判断是否存在类，存在则直接返回
        if (isset($static_file[$filename])) {
            return $static_file[$filename];
        }
        if (empty($filename)) $filename = F;
        if (empty($app)) $app = M;
        $filepath = COREFRAME_ROOT.'app/'.$app.$_admin_dir.'/'.$filename.'.php';
        $name = FALSE;
        if (file_exists($filepath)) {
            //$name = 'WUZHI_'.$filename;
            $name = $filename;
            if (class_exists($name, FALSE) === FALSE) {
                require_once($filepath);
            }
        }
        //如果存在扩展类，则初始化扩展类
        if (file_exists(COREFRAME_ROOT.'app/'.$app.$_admin_dir.'/EXT_'.$filename.'.php')) {
            $name = 'EXT_'.$filename;
            if (class_exists($name, FALSE) === FALSE) {
                require_once(COREFRAME_ROOT.'app/'.$app.$_admin_dir.'/EXT_'.$filename.'.php');
            }
        }
        $GLOBALS['_CLASS_NAME_'] = '';
        if ($name === FALSE) {
            $full_dir = '';
            if(OPEN_DEBUG) $full_dir = COREFRAME_ROOT.'app/'.$app.$_admin_dir.'/';
            echo 'Unable to locate the specified filename: '.$full_dir.$filename.'.php';
            exit();
        }
        if (class_exists($name, FALSE) === FALSE) {
            return TRUE;
        }
        $GLOBALS['_CLASS_NAME_'] = $name;
        $static_file[$filename] = isset($param) ? new $name($param) : new $name();
        return $static_file[$filename];
    }
}