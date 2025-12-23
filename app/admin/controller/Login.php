<?php
namespace app\admin\controller;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Cache;

class Login extends Admin{
	
	
	
	//用户登录 
    public function index(){
		if(!$this->request->isPost()) {
            // 开启安全入口
            if (config("my.security_gateway_enabled")) {
                // 验证参数
                if (session(config("my.auth_session_key")) == config("my.auth_session_value")) {
                    return view('index');
                } else {
                    return view('index_no');
                }
            } else {
                return view('index');
            }
		}else{
			$data = $this->request->post();
			
			$errCount = 5; //账号密码错误次数
			$timeOut = 1800; //有效期
			
			$errorKey = $this->request->url() . 'login_error_count_' . $data['username'];
			if(Cache::get($errorKey) >= $errCount && time() - Cache::get($errorKey . '_time') < $timeOut) {
				throw new ValidateException('账号已被锁定，请半小时后再尝试登录');
			}
			
			if(empty($data['key']) || empty($data['verify'])){
				// throw new ValidateException("验证码或者验证id不能为空");
			}
			if(empty($data['username']) || empty($data['password'])){
				throw new ValidateException("用户名或者密码不能为空");
			}
			
			if(config('my.verify_status') && false === captcha_check($data['key'],$data['verify'])) {
				throw new ValidateException("验证码错误");
			}
			
			$where['a.user'] = trim($data['username']);
			$where['a.pwd']  = md5(trim($data['password']).config('my.password_secrect'));
			
			$info = Db::name('admin_user')->alias('a')->join('role b', 'a.role_id in(b.role_id)')->field('a.user_id,a.name,a.user as username,a.status,a.role_id as user_role_ids,b.role_id,b.name as role_name,b.status as role_status,b.access')->where($where)->find();
			
			if(!$info){
				Cache::inc($errorKey);
				Cache::set($errorKey . '_time', time(),$timeOut);
				if(Cache::get($errorKey) == $errCount){
					throw new ValidateException('账号已被锁定，请半小时后再尝试登录');
				}else{
					throw new ValidateException(sprintf("用户名或者密码错误，您还可以登录%d次",$errCount-Cache::get($errorKey)));
				}
			}
			if(!($info['status']) || !($info['role_status'])){
				throw new ValidateException("该账户被禁用");
			}

			
			$info['access'] = explode(',',$info['access']);
			
			// 生成唯一登录token
            $token = md5(uniqid(rand(), true));
            
            // 唯一登录存储到数据库
            Db::name('admin_user')
            ->where('user_id', $info['user_id'])
            ->update(['session_token' => $token]);
            
            
			session('user_token', $token);
			session('admin', $info);
			session('admin_sign', data_auth_sign($info));
			
			Cache::set($errorKey, null);
			Cache::set($errorKey . '_time', null);
			
			event('LoginLog',$data);	//写入登录日志
			
			return json(['status'=>200]);
		}
    }

	//验证码
	public function verify(){
		$data['data'] = captcha();
		$data['verify_status'] = config('my.verify_status',true);	//验证码开关
		$data['status'] = 200;
	    return json($data);
	}
	

	//退出
    public function logout(){
        session('admin', null);
		session('admin_sign', null);
	    return json(['status'=>200]);
    }
	

	//重新登录获取新权限
    public function loginEX() {
        // 1. 检查当前会话是否有效
        if (empty(session('admin.user_id'))) {
            throw new ValidateException("不符合重新登录状态");
        }
    
        // 2. 查询用户信息
        $where['a.user_id'] = session('admin.user_id');
        $info = Db::name('admin_user')
            ->alias('a')
            ->join('role b', 'a.role_id in(b.role_id)')
            ->field('a.user_id,a.name,a.user as username,a.status,a.role_id as user_role_ids,b.role_id,b.name as role_name,b.status as role_status,b.access')
            ->where($where)
            ->find();
    
        // 3. 验证用户是否存在且有效
        if (!$info) {
            throw new ValidateException("用户不存在");
        }
        if (!$info['status'] || !$info['role_status']) {
            throw new ValidateException("该账户被禁用");
        }
    
        // 4. 清理旧 Session 和缓存
        session('admin', null);
        session('admin_sign', null);
        
        // 如果有登录错误计数缓存，清理它（需正确定义 $errorKey）
        $errorKey = 'login_error_count_' . $info['username']; // 示例：基于用户名生成缓存键
        Cache::delete($errorKey);
        Cache::delete($errorKey . '_time');
    
        
        // 6. 重新建立 Session
        $info['access'] = explode(',', $info['access']);
        session('admin', $info);
        session('admin_sign', data_auth_sign($info));
    
        return json(['status' => 200]);
    }
	
	
	//阿里云oss上传异步回调返回上传路径，放到这是因为这个地址必须外部能直接访问到
	function aliOssCallBack(){
		$body = file_get_contents('php://input');
		header("Content-Type: application/json");
		$url = $this->getendpoint(config('my.ali_oss_endpoint')).'/'.str_replace('%2F','/',$body);
        return json(['code'=>1,'data'=>$url]);
	}
	
	
	//获取阿里云oss客户端上传地址
	private function getendpoint($str){
		if(strpos(config('my.ali_oss_endpoint'),'aliyuncs.com') !== false){
			if(strpos($str,'https') !== false){
				$point = 'https://'.config('my.ali_oss_bucket').'.'.substr($str,8);
			}else{
				$point = 'http://'.config('my.ali_oss_bucket').'.'.substr($str,7);
			}	
		}else{
			$point = config('my.ali_oss_endpoint');
		}
		return $point;
	}

	

}
