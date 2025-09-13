<?php
namespace listen;
use think\facade\Db;

class DoLog{
    
    //写入操作日志
    public function handle($user){
        // if(in_array(request()->action(),[
        //     'add',
        //     'update',
        //     'delete',
        //     'createApplication',
        //     'updateApplication',
        //     'secrect',
        //     'buildApplication',
        //     'deleteApplication',
        //     'createMenu',
        //     'updateMenu',
        //     'updateMenuExt',
        //     'deleteMenu',
        //     'copyMenu',
        //     'createField',
        //     'batchCreateField',
        //     'alterTableField',
        //     'updateField',
        //     'updateFieldExt',
        //     'copyField',
        //     'deleteField',
        //     'getPostField',
        //     'createAction',
        //     'quckCreateAction',
        //     'updateAction',
        //     'updateActionExt',
        //     'deleteAction',
        //     'create',
        //     'createCode',
        //     'createByTable',
        //     'updateActionRemark',
        //     'saveRemarkVersion',
        //     'updateFullRemark',
        // ])){
        // 	$content = request()->except(['s', '_pjax']);
        // 	if ($content) {
        // 		foreach ($content as $k => $v) {
        // 			if (is_string($v) && strlen($v) > 200 || stripos($k, 'password') !== false) {
        // 				unset($content[$k]);
        // 			}
        // 		}
        // 	}
        //
        // 	$data['application_name'] = app('http')->getName();
        // 	$data['username'] = $user;
        // 	$data['url'] = request()->url(true);
        // 	$data['ip'] = request()->ip();
        // 	$data['useragent'] = request()->server('HTTP_USER_AGENT');
        // 	$data['content'] = json_encode($content,JSON_UNESCAPED_UNICODE);
        // 	$data['create_time'] = time();
        // 	$data['type'] = 2;
        //
        // 	Db::name('log')->insert($data);
        // }
        //
        //
        // $content = request()->except(['s', '_pjax']);
        // if ($content) {
        //     foreach ($content as $k => $v) {
        //         if (is_string($v) && strlen($v) > 200 || stripos($k, 'password') !== false) {
        //             unset($content[$k]);
        //         }
        //     }
        // }
        
        
        $content = request()->except(['s', '_pjax']);
        if ($content) {
            foreach ($content as $k => $v) {
                if (is_string($v) && strlen($v) > 200 || stripos($k, 'password') !== false) {
                    unset($content[$k]);
                }
            }
        }
        $data['application_name'] = app('http')->getName();
        $data['username'] = $user;
        $data['url'] = request()->url(true);
        $data['ip'] = request()->ip();
        $data['useragent'] = request()->server('HTTP_USER_AGENT');
        $data['content'] = json_encode($content,JSON_UNESCAPED_UNICODE);
        $data['create_time'] = time();
        $data['type'] = 2;
        
        Db::name('log')->insert($data);
    }
}
