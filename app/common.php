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


use think\facade\Db;
use think\facade\Log;
use think\facade\Config;
use think\exception\ValidateException;


error_reporting(0);


/**
 * 随机字符
 * @param int    $length  长度
 * @param string $type    类型
 * @param int    $convert 转换大小写 1大写 0小写
 * @return string
 */
function random($length = 10, $type = 'letter', $convert = 0) {
    $config = array(
        'number' => '1234567890',
        'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );
    
    if (!isset($config[$type])) $type = 'letter';
    $string = $config[$type];
    
    $code = '';
    $strlen = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $string[mt_rand(0, $strlen)];
    }
    if (!empty($convert)) {
        $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
    }
    return $code;
}

/*
 * 生成交易流水号
 * @param char(2) $type
 */
function doOrderSn($type) {
    return date('YmdHis') . $type . substr(microtime(), 2, 3) . sprintf('%02d', rand(0, 99));
}


//后台sql输入框语句过滤
function sql_replace($str) {
    $farr = ["/insert[\s]+|update[\s]+|create[\s]+|alter[\s]+|delete[\s]+|drop[\s]+|load_file|outfile|dump/is"];
    $str = preg_replace($farr, '', $str);
    return $str;
}

//上传文件黑名单过滤
function upload_replace($str) {
    $farr = ["/php|php3|php4|php5|phtml|pht|/is"];
    $str = preg_replace($farr, '', $str);
    return $str;
}

//查询方法过滤
function serach_in($str) {
    $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    $farr = ["/select/i", "/insert/i", "/and/i", "/or/i", "/create/i", "/update/i", "/delete/i", "/alter/i", "/count/i", "/union/i", "/load_file/i", "/outfile/i"];
    $str = preg_replace($farr, '', $str);
    return trim($str);
}

//获取键值对信息
function getItemData($data) {
    $str = in_array(json_encode(array_values($data)), ['[]', '[[]]']) ? '' : json_encode(array_values($data), 320);
    return $str;
}


/*获取应用url前缀*/
function getBaseUrl() {
    $baseAppName = app('http')->getName();
    if (config('app.app_map')) {
        $newapp = array_flip(config('app.app_map'))[$baseAppName];
        if ($newapp) $baseAppName = $newapp;
    }
    
    $basename = '/' . $baseAppName;
    
    if (config('app.domain_bind')) {
        $newapp = array_flip(config('app.domain_bind'))[$baseAppName];
        if ($newapp) $basename = '';
    }
    
    return $basename;
}


//无限极分类转为带有 children的树形list表格结构
if (!function_exists('_generateListTree')) {
    function _generateListTree($data, $pid = 0, $config = []) {
        $tree = [];
        if ($data && is_array($data)) {
            foreach ($data as $v) {
                if ($v[$config[1]] == $pid) {
                    $tree[] = array_merge($v, ['children' => _generateListTree($data, $v[$config[0]], $config)]);
                }
            }
        }
        return $tree;
    }
}

/**
 * 实例化数据库类
 * @param string       $name   操作的数据表名称（不含前缀）
 * @param array|string $config 数据库配置参数
 * @param bool         $force  是否强制重新连接
 * @return \think\db\Query
 */
if (!function_exists('db')) {
    function db($name = '', $connect = '') {
        if (empty($connect)) {
            $connect = config('database.default');
        }
        return Db::connect($connect, false)->name($name);
    }
}


//钩子函数
function hook($hookname, &$data) {
    $path = str_replace('/', '\\', $hookname);
    list($controller, $action) = explode('@', $path);
    $controller = app($controller);
    if (method_exists($controller, $action)) {
        try {
            $response = call_user_func_array([$controller, $action], [&$data]);
        } catch (\Exception $e) {
            throw new ValidateException($e->getMessage());
        }
        
        return $response;
    }
}


function handleSigle($data, $only_param, $menu_id, $data_id) {
    $fileInfo = Db::name('file')
        ->where('filepath', $data)
        ->where('only_param', $only_param)->find();
    if (!empty($fileInfo)) {
        $use_in = json_decode($fileInfo['use_in'], true);
        $use_in[] = ['menu_id' => $menu_id, 'data_id' => $data_id];
        
        Db::name('file')
            ->where('filepath', $data)
            ->where('only_param', $only_param)->update([
                'use_in' => json_encode(array_values($use_in), JSON_UNESCAPED_UNICODE),
                'only_param' => 0
            ]);
    }
}


function handleList($data, $only_param, $menu_id, $data_id) {
    $data = json_decode($data, true);
    if (is_array($data)) {
        foreach ($data as $dataItem) {
            $fileInfo = Db::name('file')
                ->where('filepath', $dataItem['url'])
                ->where('only_param', $only_param)->find();
            if (!empty($fileInfo)) {
                $use_in = json_decode($fileInfo['use_in'], true);
                $use_in[] = ['menu_id' => $menu_id, 'data_id' => $data_id];
                
                Db::name('file')
                    ->where('filepath', $dataItem['url'])
                    ->where('only_param', $only_param)->update([
                        'use_in' => json_encode(array_values($use_in), JSON_UNESCAPED_UNICODE),
                        'only_param' => 0
                    ]);
            }
        }
    }
}


function handleFWB($data, $only_param, $menu_id, $data_id) {
    if (strpos($data, 'src') === false) {
        $data = '<p></p>';
    }
    $dom = new DOMDocument();
    @$dom->loadHTML($data);
    $images = $dom->getElementsByTagName('img');
    
    $urls = [];
    $attributes = ['src', 'data-src', 'data-original']; // 可以检测的属性
    
    foreach ($images as $img) {
        foreach ($attributes as $attr) {
            if ($img->hasAttribute($attr)) {
                $url = $img->getAttribute($attr);
                if (!empty($url) && !in_array($url, $urls)) {
                    $fileInfo = Db::name('file')
                        ->where('filepath', $url)
                        ->where('only_param', $only_param)->find();
                    if (!empty($fileInfo)) {
                        $use_in = json_decode($fileInfo['use_in'], true);
                        
                        $use_in = array_filter($use_in, function ($item) use ($menu_id, $data_id) {
                            return !($item['menu_id'] == $menu_id && $item['data_id'] == $data_id);
                        });
                        
                        $use_in[] = ['menu_id' => $menu_id, 'data_id' => $data_id];
                        
                        Db::name('file')
                            ->where('filepath', $url)
                            ->where('only_param', $only_param)->update([
                                'use_in' => json_encode(array_values($use_in), JSON_UNESCAPED_UNICODE),
                                'only_param' => 0
                            ]);
                    }
                }
            }
        }
    }
}


function handleDelete($only_param, $menu_id, $data_id) {
    $fileList = Db::name('file')->where('only_param', $only_param)->select();
    
    if (!empty($fileList)) {
        foreach ($fileList as $fileListItem) {
            if (!empty($fileListItem['use_in'])) {
                $use_in = json_decode($fileListItem['use_in'], true);
                
                // 删除对应键值对
                $use_in = array_filter($use_in, function ($item) use ($menu_id, $data_id) {
                    return !($item['menu_id'] == $menu_id && $item['data_id'] == $data_id);
                });
                
                if (count($use_in) > 0) {
                    // 清除随机数 和 对应的键值对
                    Db::name('file')->where('only_param', $only_param)
                        ->where('filepath', $fileListItem['filepath'])->update([
                            'use_in' => json_encode(array_values($use_in), JSON_UNESCAPED_UNICODE),
                            'only_param' => 0
                        ]);
                } else {
                    // 删除数据 和 文件
                    Db::name('file')->where('only_param', $only_param)
                        ->where('filepath', $fileListItem['filepath'])->delete();
                    event('DeleteFile', $fileListItem['filepath']);
                }
            } else {
                // 删除数据 和 文件
                Db::name('file')->where('only_param', $only_param)
                    ->where('filepath', $fileListItem['filepath'])->delete();
                event('DeleteFile', $fileListItem['filepath']);
            }
        }
    }
}

function handleDeleteEdit($data, $menu_id, $data_id) {
    $fileInfo = Db::name('file')->where('filepath', $data)->find();
    if (!empty($fileInfo)) {
        if (!empty($fileInfo['use_in'])) {
            $use_in = json_decode($fileInfo['use_in'], true);
            
            // 删除对应键值对
            $use_in = array_filter($use_in, function ($item) use ($menu_id, $data_id) {
                return !($item['menu_id'] == $menu_id && $item['data_id'] == $data_id);
            });
            if (count($use_in) > 0) {
                // 清除随机数 和 对应的键值对
                Db::name('file')->where('filepath', $fileInfo['filepath'])->update([
                    'use_in' => json_encode(array_values($use_in), JSON_UNESCAPED_UNICODE),
                    'only_param' => 0
                ]);
            } else {
                // 删除数据 和 文件
                Db::name('file')->where('filepath', $fileInfo['filepath'])->delete();
                event('DeleteFile', $fileInfo['filepath']);
            }
        } else {
            // 删除数据 和 文件
            Db::name('file')->where('filepath', $fileInfo['filepath'])->delete();
            event('DeleteFile', $fileInfo['filepath']);
        }
        
    }
}

function handleDeleteEditFWB($data, $old_data, $menu_id, $data_id) {
    
    if (strpos($data, 'src') === false) {
        $data = '<p></p>';
    }
    
    if (strpos($old_data, 'src') === false) {
        $old_data = '<p></p>';
    }
    
    $dom = new DOMDocument();
    $dom_old = new DOMDocument();
    @$dom->loadHTML($data);
    @$dom_old->loadHTML($old_data);
    $images = $dom->getElementsByTagName('img');
    $images_old = $dom_old->getElementsByTagName('img');
    
    $urls = [];
    $attributes = ['src', 'data-src', 'data-original']; // 可以检测的属性
    foreach ($images as $img) {
        foreach ($attributes as $attr) {
            if ($img->hasAttribute($attr)) {
                $url = $img->getAttribute($attr);
                if (!empty($url) && !in_array($url, $urls)) {
                    $urls[] = $url;
                    $fileInfo = Db::name('file')
                        ->where('filepath', $url)->find();
                    if (!empty($fileInfo)) {
                        $use_in = json_decode($fileInfo['use_in'], true);
                        
                        $use_in = array_filter($use_in, function ($item) use ($menu_id, $data_id) {
                            return !($item['menu_id'] == $menu_id && $item['data_id'] == $data_id);
                        });
                        
                        $use_in[] = ['menu_id' => $menu_id, 'data_id' => $data_id];
                        
                        Db::name('file')
                            ->where('filepath', $url)->update([
                                'use_in' => json_encode(array_values($use_in), JSON_UNESCAPED_UNICODE),
                                'only_param' => 0
                            ]);
                    }
                }
            }
        }
    }
    
    foreach ($images_old as $img) {
        foreach ($attributes as $attr) {
            if ($img->hasAttribute($attr)) {
                $url = $img->getAttribute($attr);
                if (!empty($url) && !in_array($url, $urls)) {
                    handleDeleteEdit($url, $menu_id, $data_id);
                }
            }
        }
    }
    
}
