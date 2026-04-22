<?php

namespace app\admin\controller;
use think\exception\FuncNotFoundException;
use think\exception\ValidateException;
use app\BaseController;
use think\facade\Db;


class Admin extends BaseController
{


	protected function initialize(){
		$controller = $this->request->controller();
		$action = $this->request->action();
		$app = app('http')->getName();

		$admin = session('admin');
        $userid = session('admin_sign') == data_auth_sign($admin) ? $admin['user_id'] : 0;
        
        if(!$userid && ($app <> 'admin' || $controller <> 'Login')){
			echo '<script type="text/javascript">top.parent.frames.location.href="'.url('admin/Login/index').'";</script>';exit();
        }
        
        $url =  "/{$app}/{$controller}/{$action}.html";
        if(session('admin.role_id') <> 1
            && !in_array($url,config('my.nocheck'))
            && !in_array($action,[
                'getExtends',
                'getInfo',
                'getFieldList',
                'addSchedule',
                'deleteSchedule',
                'getSchedules',
                'getInitData'
                ,'setAdminIndexStatus',
                'watermark',
                'loginEX',
                'preview',
            ])){
            if(!in_array($url,session('admin.access'))){
                throw new ValidateException ('你没操作权限');
            }
        }

		event('DoLog',session('admin.username'));	//写入操作日志

		$list = Db::name('base_config')->cache(true,60)->select()->column('data','name');
		config($list,'base_config');
		//验证唯一登录
		$this->checkSingleSession();
	}


	//返回当前应用的菜单列表
    protected function getBaseMenus()
    {
        $appname = app('http')->getName();
        $field = 'menu_id,pid,title,controller_name,status,icon,sortid,url';
        // 提醒 - 2025-08-29
        $field .= ",prompt,table_name,prompt_session,open_mode";

        $list = db("menu")->field($field)->where(['status' => 1, 'app_id' => 1])->order('sortid asc')->select()->toArray();
        if ($list) {
            foreach ($list as $key => $val) {
                $menus[$key]['pid'] = $val['pid'];
                $menus[$key]['menu_id'] = $val['menu_id'];
                $menus[$key]['title'] = $val['title'];
                $menus[$key]['sortid'] = $val['sortid'];
                $menus[$key]['icon'] = $val['icon'] ? $val['icon'] : 'el-icon-menu';
                $menus[$key]['url'] = $this->getUrl($val, $appname);
                $menus[$key]['access'] = $val['url'] ? $val['url'] : $appname . '/' . $val['controller_name'];
                $menus[$key]['open_mode'] = intval($val['open_mode'] ?? 1) ?: 1;


                // 提醒 - 2025-08-29
                $show = false;
                $tj = [
                    1 => '>',     // 大于
                    2 => '<',     // 小于
                    3 => '=',     // 等于
                    4 => '>=',    // 大于等于
                    5 => '<=',    // 小于等于
                    6 => 'like',  // 包含
                    7 => 'not like', // 不包含
                    8 => '<>',    // 不等于
                    // 9和10不需要值，因为是判断空或非空
                ];

                // 查条件字段
                if ($val['prompt'] == 1) {
                    $where = [];
                    $whereRawList = [];
                    $prompt_query = Db::name($val['table_name']);
                    // 方法
                    $menu_actions = Db::name('action')
                        ->where('menu_id', $val['menu_id'])
                        ->whereIn('type', [53, 54])
                        ->select()
                        ->toArray();
                    // 字段
                    $menu_fileds = Db::name('field')
                        ->where('menu_id', $val['menu_id'])
                        ->where('tx_config', '<>', '')
                        ->whereNotNull('tx_config')
                        ->select()
                        ->toArray();
                    // session值
                    if ($val['prompt_session']) {
                        $where[$val['prompt_session']] = session('admin')[$val['prompt_session']];
                    }

                    // 查方法
                    if (!empty($menu_actions)) {
                        // 方法名
                        $action_names = array_column($menu_actions, 'action_name');
                        // url处理
                        $action_urls = array_map(function ($action_name) use ($val) {
                            return "/admin/{$val['controller_name']}/{$action_name}.html";
                        }, $action_names);

                        // 循环处理看全部 看同字段
                        foreach ($menu_actions as $menu_action) {
                            if ($menu_action['type'] == 53) {
                                $role_id = session('admin.role_id');
                                $access = session('admin.access');
                                $admin_value = session('admin')[$menu_action['sql']];
                                $field = $menu_action['fields'];
                                $whereRaw = "";
                                $action_name = $menu_action['action_name'];
                                // 关联session的字段
                                $session_fields = Db::name('field')
                                    ->where('menu_id', $val['menu_id'])
                                    ->where('type', 30)
                                    ->select()
                                    ->toArray();

                                // 优先处理session字段
                                if ($session_fields && count($session_fields) > 1) {
                                    foreach ($session_fields as $session_field) {
                                        if ($field != $session_field['field']) {
                                            if (!in_array($role_id, [1]) && empty(array_intersect($access, $action_urls))) {
                                                $where[$session_field['field']] = session('admin')[$session_field['field']];
                                            }
                                            if (!in_array($role_id, [1]) && !empty(array_intersect($access, ["/admin/{$val['controller_name']}/{$action_name}.html"]))) {
                                                $whereRaw .= " {$session_field['field']} = '" . session('admin')[$session_field['field']] . "' or ";
                                            }
                                        }
                                    }
                                }

                                // 处理同字段
                                if (!in_array($role_id, [1]) && !empty(array_intersect($access, ["/admin/{$val['controller_name']}/{$action_name}.html"]))) {
                                    $whereRaw .= " $field = '$admin_value' or FIND_IN_SET('$admin_value', $field)";
                                }
                                if (!in_array($role_id, [1]) && empty(array_intersect($access, $action_urls))) {
                                    $where[$field] = $admin_value;
                                }

                                $whereRawList[] = $whereRaw;
                            }
                        }


                    }

                    $whereRawList = array_filter($whereRawList);
                    if (!empty($whereRawList)) {
                        if (count($whereRawList) > 0) {
                            $whereRawList = implode(' and ', $whereRawList);
                        } else {
                            $whereRawList = implode('', $whereRawList);
                        }
                        $prompt_query->whereRaw($whereRawList);
                    }

                    // 多循环
                    foreach ($menu_fileds as $menu_filed) {
                        $menu_tx_config = $menu_filed['tx_config'] ? json_decode($menu_filed['tx_config'], true) : [];
                        
                        if (!empty($menu_tx_config)) {
                            // [{"tx_tiaojian":3,"tx_zhi":"\"匹配交往中\"","tx_color":"#6967ce"}]
                            foreach ($menu_tx_config as $tx_config) {
                                $condition = $tx_config['tx_tiaojian'];
                                $value = $tx_config['tx_zhi'];


                                // 处理 {:session('key')} 格式，统一转换为 session('key')
                                // 解析 {:session('key')} 并替换为 $_SESSION 值
                                $value = preg_replace_callback(
                                    '/\{:session\(([\'"])(.*?)\1\)\}/i',
                                    function ($matches) {
                                        // 假设 session 数据存储在 $_SESSION 或 thinkphp 的 session() 函数
                                        $sessionKey = $matches[2]; // 例如 'admin.yg_xingming'
                                        $sessionValue = session($sessionKey); // 获取实际 session 值
                                        return $sessionValue;
                                    },
                                    $value
                                );

                                if (empty($condition)) {
                                    continue;
                                }
                                // 处理不同的条件类型
                                if ($condition == 9) { // 为空
                                    $show = $prompt_query
                                            ->where($menu_filed['field'], '=', '')
                                            ->orWhereNull($menu_filed['field'])
                                            ->where($where)
                                            ->count() > 0;
                                } elseif ($condition == 10) { // 不为空
                                    $show = $prompt_query
                                            ->where($menu_filed['field'], '<>', '')
                                            ->whereNotNull($menu_filed['field'])
                                            ->where($where)
                                            ->count() > 0;
                                } else {
                                    // 其他条件使用映射的关系运算符
                                    $operator = $tj[$condition] ?? '=';
                                    if ($operator == 'like' || $operator == 'not like') {
                                        $value = str_replace('"', '',$value);
//                                        $value = "%{$value}%";
                                    }

                                    if (in_array($operator, ['like', 'not like'])) {
                                        if ($operator == 'like') {
                                            $operator = "find_in_set";
                                        } else {
                                            $operator = "{$menu_filed['field']} IS NULL OR not find_in_set";
                                        }
                                        $show = $prompt_query
                                                ->whereRaw("{$operator}(?, {$menu_filed['field']})", [$value])
                                                ->where($where)
                                                ->count() > 0;
                                    } else {
                                        $show = $prompt_query
                                                ->where($menu_filed['field'], $operator, $value)
                                                ->where($where)
                                                ->count() > 0;
                                    }
                                }
                            }
                            if ($show) break;
                        }
                    }
                }
                $menus[$key]['prompt'] = $show ? 1 : 0;

            }
            return _generateListTree($menus, 0, ['menu_id', 'pid']);
        }
    }

	//获取url
	private function getUrl($val,$appname){
		if($val['url']){
			if(strpos($val['url'], '://')){
				$url = $val['url'];
			}else{
				$url = (string)url(ltrim(str_replace('.html','',$val['url']),'/'));
			}
			$appname = app('http')->getName();
			$mapping = '';
			foreach(config('app.app_map') as $k=>$v){
				if($v == $appname){
					$mapping = $k;
				}
			}
			if(!empty($mapping)){
				$url = str_replace($appname,$mapping,$url);
			}
		}else{
			$url = (string)url(getBaseUrl().'/'.str_replace('/','.',$val['controller_name']).'/index');
		}
		return $url;
	}


	//验证器 并且抛出异常
	protected function validate($data,$validate){
		try{
			validate($validate)->scene($this->request->action())->check($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}
		return true;
	}

	//格式化sql字段查询 转化为 key=>val 结构
	protected function query($sql,$connect='mysql'){
		preg_match_all('/select(.*)from/iUs',$sql,$all);
		if(!empty($all[1][0])){
			$sqlvalue = explode(',',trim($all[1][0]));
		}
		if(strpos($sql,'tkey') !== false){
			$sqlvalue[1] = 'tkey';
		}

		if(strpos($sql,'tval') !== false){
			$sqlvalue[0] = 'tval';
		}
		$sql = str_replace('pre_',config('database.connections.'.$connect.'.prefix'),$sql);
		$list = Db::connect($connect)->query($sql);
		$array = [];
		foreach($list as $k=>$v){
			$array[$k]['key'] = $v[trim($sqlvalue[1])];
			$array[$k]['val'] = $v[$sqlvalue[0]];
			if($sqlvalue[2]){
				$array[$k]['pid'] = $v[trim($sqlvalue[2])];
			}
		}
		return $array;
	}



	public function __call($method, $args){
        throw new FuncNotFoundException('方法不存在',$method);
    }

    function getRealClientIP() {
        $ip = '';

        // 检查是否使用了代理
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // 可能是多个IP地址列表，取第一个
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        // 验证IP地址格式
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
    	// 唯一登录检查每次
    public function checkSingleSession() {
        //if (!isset($_SESSION['admin.user_id'])) return;
        $dlbh = Db::name('base_config')->column('data','name');
        
        // dump($dlbh);
        // exit;
        
        
        if($dlbh['denglubaohu'] == 1) {
            
            $sessionuser_id = session('admin.user_id');
    
            if (!empty($sessionuser_id)){
                $storedToken = Db::name('admin_user')
                ->field('session_token')
                ->where('user_id', $sessionuser_id)
                ->value('session_token');
            
                if (session('user_token') !== $storedToken) {
                    // token不匹配，说明其他地方登录了
                    session('admin', null);
                    session('user_token', null);
            		session('admin_sign', null);
                    return json(['status' => 200]);
                }
                
            }else{
                    session('admin', null);
                    session('user_token', null);
            		session('admin_sign', null);
                    return json(['status' => 200]);
                
            }
            
        }
        

        

    }


}
