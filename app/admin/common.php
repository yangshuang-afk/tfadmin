<?php
// +----------------------------------------------------------------------
// | 应用公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------


use think\helper\Str;

error_reporting(0);


//根据键名获取键值
function getItemVal($val, $item_config) {
    if (!is_null($val)) {
        $str = '';
        foreach (explode(',', $val) as $v) {
            foreach (json_decode($item_config, true) as $m) {
                if ($v == $m['val']) {
                    $str .= $m['key'] . ',';
                }
            }
        }
        return rtrim($str, ',');
    }
}

//根据键值获取键名
function getValByKey($val, $item_config) {
    if ($val) {
        $str = '';
        foreach (explode(',', $val) as $v) {
            foreach (json_decode($item_config, true) as $m) {
                if ($v == $m['key']) {
                    $str .= $m['val'] . ',';
                }
            }
        }
        return rtrim($str, ',');
    }
}

//无限极分类转为带有 children的树形select结构
function _generateSelectTree($data, $pid = 0) {
    $tree = [];
    if ($data && is_array($data)) {
        foreach ($data as $v) {
            if ($v['pid'] == $pid) {
                $tree[] = [
                    'key' => $v['key'],
                    'val' => $v['val'],
                    'children' => _generateSelectTree($data, $v['val']),
                ];
            }
        }
    }
    return $tree;
}


function deldir($dir) {
    if (is_dir($dir)) {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }
        
        closedir($dh);
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}

/*写入
* @param  string  $type 1 为生成控制器
*/
function filePutContents($content, $filepath, $type) {
    if (in_array($type, [1, 3])) {
        // 1. 读取旧文件
        $str = file_get_contents($filepath);
        
        // 2. 在提取前，先删除旧文件中的ES配置
        $str = removeEsFromContent($str);
        
        // 3. 提取需要保留的内容（此时已无ES配置）
        $parten = '/\s\/\*+start\*+\/(.*)\/\*+end\*+\//iUs';
        preg_match_all($parten, $str, $all);
        if ($all[0]) {
            foreach ($all[0] as $key => $val) {
                $ext_content .= $val . "\n\n";
            }
        }
        
        // 4. 合并新旧内容（新内容已在API返回时清理过ES）
        $content .= $ext_content . "\n\n";
        if ($type == 1) {
            $content .= "}\n\n";
        }
    }
    
    ob_start();
    echo $content;
    $_cache = ob_get_contents();
    ob_end_clean();
    
    if ($_cache) {
        $File = new \think\template\driver\File();
        $File->write($filepath, $_cache);
    }
}

/**
 * 删除内容中的ES配置
 */
function removeEsFromContent($str) {
    // 根据实际格式：制表符 + 注释
    $patterns = [
        // 第一个ES属性
        '/\t\/\*\*[\s\S]*?Elasticsearch服务实例[\s\S]*?\@buildcode\(true\)[\s\S]*?\$esService = null;\s*\n/',
        
        // 第二个ES属性
        '/\t\/\*\*[\s\S]*?ES索引名称[\s\S]*?\@buildcode\(true\)[\s\S]*?\$esIndex = \'[^\']*\';[\s\S]*?\n/',
        
        // 第三个ES属性
        '/\t\/\*\*[\s\S]*?需要ES处理的LIKE查询字段[\s\S]*?\@buildcode\(true\)[\s\S]*?\$esLikeFields = \[[^\]]*\];[\s\S]*?\n/'
    ];
    
    foreach ($patterns as $pattern) {
        $str = preg_replace($pattern, '', $str);
    }
    
    // 清理多余空行
    $str = preg_replace('/\n\n\n+/', "\n\n", $str);
    
    return $str;
}


function data_auth_sign($data) {
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data);
    $code = http_build_query($data);
    $sign = sha1($code);
    return $sign;
}


function killword($str, $start, $length, $charset = "utf-8", $suffix = true) {
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}

function killhtml($str, $length = 0) {
    if (is_array($str)) {
        foreach ($str as $k => $v) $data[$k] = killhtml($v, $length);
        return $data;
    }
    
    if (!empty($length)) {
        $estr = htmlspecialchars(preg_replace('/(&[a-zA-Z]{2,5};)|(\s)/', '', strip_tags(str_replace('[CHPAGE]', '', $str))));
        if ($length < 0) return $estr;
        return killword($estr, 0, $length);
    }
    return htmlspecialchars(trim(strip_tags($str)));
}

function getListUrl($newslist) {
    if (!empty($newslist['jumpurl'])) {
        $url = $newslist['jumpurl'];
    } else {
        $info = db('content')->alias('a')->join('catagory b', 'a.class_id=b.class_id')->where(['a.content_id' => $newslist['content_id']])->field('a.content_id,b.filepath')->find();
        $url = $info['filepath'] . '/' . $info['content_id'] . '.html';
    }
    return $url;
}


function U($classid) {
    $info = db('catagory')->where('class_id', $classid)->find();
    $filepath = $info['filepath'] == '/' ? '' : '/' . trim($info['filepath'], '/');
    $filename = $info['filename'] == 'index.html' ? '' : $info['filename'];
    $url = $filepath . '/' . $filename;
    return $url;
}


// ====================== //

//多级控制器 获取控制其名称
function getControllerName($controller_name) {
    if ($controller_name && strpos($controller_name, '/') > 0) {
        $controller_name = explode('/', $controller_name)[1];
    }
    return $controller_name;
}

//多级控制器 获取use名称
function getUseName($controller_name) {
    if ($controller_name && strpos($controller_name, '/') > 0) {
        $controller_name = str_replace('/', '\\', $controller_name);
    }
    return $controller_name;
}

//多级控制器 获取db命名空间
function getDbName($controller_name) {
    if ($controller_name && strpos($controller_name, '/') > 0) {
        $controller_name = '\\' . explode('/', $controller_name)[0];
    } else {
        $controller_name = '';
    }
    return $controller_name;
}

//多级控制器获取视图名称
function getViewName($controller_name) {
    if ($controller_name && strpos($controller_name, '/') > 0) {
        $arr = explode('/', $controller_name);
        $controller_name = ucfirst($arr[0]) . '/' . Str::snake($arr[1]);
    } else {
        $controller_name = Str::snake($controller_name);
    }
    return $controller_name;
}

// ====================== //


/**
 * @param     $length
 * @param int $type
 * @return string
 * @desc      随机字符串
 * @author    JiaWei
 * @email     975162853@qq.com
 * @date      2025/8/1
 * @time      15:29
 */
function create_number($length = 6, int $type = 1) {
    $chars = [
        1 => '0123456789',
        2 => 'abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        3 => '0123456789abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        4 => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|',
        5 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|',
        6 => '0123456789abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_',
    ];
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[$type][mt_rand(0, strlen($chars[$type]) - 1)];
    }
    return $str;
}


