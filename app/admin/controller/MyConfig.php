<?php

namespace app\admin\controller;

class MyConfig extends Admin
{
    private $oldGatewayConfig = null;
    private $newGatewayConfig = null;
    
    /**
     * @description 配置表单
     * @buildcode(true)
     */
    public function index() {
        if (!$this->request->isPost()) {
            return view('index');
        } else {
            $configData = $this->request->post();
            try {
                // 保存旧的网关配置用于恢复
                $this->oldGatewayConfig = [
                    'gateway_module' => config('my.gateway_module', ''),
                    'gateway_controller' => config('my.gateway_controller', ''),
                    'gateway_action' => config('my.gateway_action', ''),
                    'security_gateway_enabled' => config('my.security_gateway_enabled', false)
                ];
                
                // 保存新的网关配置
                $this->newGatewayConfig = [
                    'gateway_module' => $configData['gateway_module'] ?? '',
                    'gateway_controller' => $configData['gateway_controller'] ?? '',
                    'gateway_action' => $configData['gateway_action'] ?? '',
                    'security_gateway_enabled' => $configData['security_gateway_enabled'] ?? false
                ];
                
                // 处理安全入口文件
                $this->processGatewayFiles();
                
                // 处理微信支付证书路径
                if (isset($configData['wechart_pay'])) {
                    $configData['wechart_pay'] = $this->handleWechatPayPaths($configData['wechart_pay']);
                }
                
                // 生成配置文件内容
                $content = $this->generateConfigContent($configData);
                
                // 配置文件路径
                $configPath = app()->getConfigPath() . 'my.php';
                
                // 写入新配置
                $result = file_put_contents($configPath, $content);
                
                if ($result === false) {
                    // 写入失败时恢复网关文件
                    $this->restoreGatewayFiles();
                    throw new \Exception('配置文件写入失败，请检查文件权限');
                }
                
                return json([
                    'status' => 200,
                    'msg' => '配置更新成功'
                ]);
            } catch (\Exception $e) {
                // 异常时恢复网关文件
                $this->restoreGatewayFiles();
                return json([
                    'status' => 500,
                    'msg' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * 处理网关文件
     * @throws \Exception
     */
    private function processGatewayFiles() {
        // 验证新配置
        if ($this->newGatewayConfig['security_gateway_enabled']) {
            $this->validateGatewayConfig();
            
            // 删除旧文件
            $this->deleteOldGatewayFiles();
            
            // 创建新文件
            $this->createNewGatewayFiles();
        }
    }
    
    /**
     * 验证网关配置
     * @throws \Exception
     */
    private function validateGatewayConfig() {
        $newModule = $this->newGatewayConfig['gateway_module'] ?? '';
        $newController = $this->newGatewayConfig['gateway_controller'] ?? '';
        $newAction = $this->newGatewayConfig['gateway_action'] ?? '';
        
        // 验证新配置是否为空
        if (empty($newModule) || empty($newController) || empty($newAction)) {
            throw new \Exception('安全入口配置不完整：模块名、控制器名、方法名不能为空');
        }
        
        // 验证模块名：至少6位，字母数字混合
        if (strlen($newModule) < 6) {
            throw new \Exception('模块名长度至少6位');
        }
        
        // 检查模块名是否包含至少一个字母和至少一个数字
        if (!preg_match('/[a-zA-Z]/', $newModule) || !preg_match('/\d/', $newModule)) {
            throw new \Exception('模块名必须包含字母和数字');
        }
        
        // 验证所有名称不能包含特殊符号（只允许字母、数字、下划线）
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $newModule)) {
            throw new \Exception('模块名只能包含字母、数字和下划线');
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $newController)) {
            throw new \Exception('控制器名只能包含字母、数字和下划线');
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $newAction)) {
            throw new \Exception('方法名只能包含字母、数字和下划线');
        }
        
        // 验证不能以下划线结尾（可以数字结尾）
        if (substr($newModule, -1) === '_') {
            throw new \Exception('模块名不能以下划线结尾');
        }
        
        if (substr($newController, -1) === '_') {
            throw new \Exception('控制器名不能以下划线结尾');
        }
        
        if (substr($newAction, -1) === '_') {
            throw new \Exception('方法名不能以下划线结尾');
        }
        
        // 防止目录遍历攻击
        if (strpos($newModule, '..') !== false || strpos($newController, '..') !== false || strpos($newAction, '..') !== false) {
            throw new \Exception('安全入口配置包含非法字符');
        }
    }
    
    /**
     * 删除旧网关文件
     */
    private function deleteOldGatewayFiles() {
        $oldModule = $this->oldGatewayConfig['gateway_module'] ?? '';
        $oldController = $this->oldGatewayConfig['gateway_controller'] ?? '';
        $oldAction = $this->oldGatewayConfig['gateway_action'] ?? '';
        
        if (!empty($oldModule) && !empty($oldController) && !empty($oldAction)) {
            // 删除视图文件
            $viewFile = $this->getViewFilePath($oldModule, $oldController, $oldAction);
            if (file_exists($viewFile)) {
                @unlink($viewFile);
            }
            
            // 删除控制器文件
            $controllerFile = $this->getControllerFilePath($oldModule, $oldController);
            if (file_exists($controllerFile)) {
                @unlink($controllerFile);
            }
            
            // 清理空目录
            $this->cleanupEmptyDirectories($oldModule, $oldController);
        }
    }
    
    /**
     * 创建新的网关文件
     * @throws \Exception
     */
    private function createNewGatewayFiles() {
        $module = $this->newGatewayConfig['gateway_module'] ?? '';
        $controller = $this->newGatewayConfig['gateway_controller'] ?? '';
        $action = $this->newGatewayConfig['gateway_action'] ?? '';
        
        // 获取目录路径
        $moduleDir = $this->getModuleDirPath($module);
        $controllerDir = $this->getAppRootPath() . $module . '/controller/';
        $viewDir = $this->getAppRootPath() . $module . '/view/' . strtolower($controller) . '/';
        
        // 创建模块目录（如果不存在）
        if (!is_dir($moduleDir)) {
            if (!mkdir($moduleDir, 0755, true)) {
                throw new \Exception('无法创建模块目录：' . $moduleDir);
            }
        }
        
        // 创建控制器目录（如果不存在）
        if (!is_dir($controllerDir)) {
            if (!mkdir($controllerDir, 0755, true)) {
                throw new \Exception('无法创建控制器目录：' . $controllerDir);
            }
        }
        
        // 创建控制器文件
        $controllerFile = $this->getControllerFilePath($module, $controller);
        if (!file_exists($controllerFile)) {
            $controllerContent = $this->generateControllerContent($module, $controller, $action);
            if (file_put_contents($controllerFile, $controllerContent) === false) {
                throw new \Exception('无法创建控制器文件：' . $controllerFile);
            }
        } else {
            // 如果控制器已存在，检查方法是否存在
            $existingContent = file_get_contents($controllerFile);
            $methodName = $action;
            if (strpos($existingContent, "public function {$methodName}") === false) {
                // 在类的结束大括号前添加新方法
                $newMethodContent = $this->generateMethodContent($action);
                $existingContent = preg_replace(
                    '/}\s*$/',
                    "\n    " . $newMethodContent . "\n}",
                    $existingContent
                );
                if (file_put_contents($controllerFile, $existingContent) === false) {
                    throw new \Exception('无法更新控制器文件：' . $controllerFile);
                }
            }
        }
        
        // 创建视图目录（如果不存在）
        if (!is_dir($viewDir)) {
            if (!mkdir($viewDir, 0755, true)) {
                throw new \Exception('无法创建视图目录：' . $viewDir);
            }
        }
        
        // 创建视图文件
        $viewFile = $this->getViewFilePath($module, $controller, $action);
        if (!file_exists($viewFile)) {
            $viewContent = $this->generateViewContent();
            if (file_put_contents($viewFile, $viewContent) === false) {
                throw new \Exception('无法创建视图文件：' . $viewFile);
            }
        }
    }
    
    /**
     * 恢复网关文件（当出现异常时）
     */
    private function restoreGatewayFiles() {
        // 如果新配置已经创建了文件，删除它们
        $newModule = $this->newGatewayConfig['gateway_module'] ?? '';
        $newController = $this->newGatewayConfig['gateway_controller'] ?? '';
        $newAction = $this->newGatewayConfig['gateway_action'] ?? '';
        
        if (!empty($newModule) && !empty($newController) && !empty($newAction)) {
            // 删除新的视图文件
            $newViewFile = $this->getViewFilePath($newModule, $newController, $newAction);
            if (file_exists($newViewFile)) {
                @unlink($newViewFile);
            }
            
            // 删除新的控制器文件
            $newControllerFile = $this->getControllerFilePath($newModule, $newController);
            if (file_exists($newControllerFile)) {
                @unlink($newControllerFile);
            }
            
            // 清理新创建的空目录
            $this->cleanupEmptyDirectories($newModule, $newController);
        }
        
        // 如果旧配置存在且启用了安全入口，重新创建旧文件
        $oldModule = $this->oldGatewayConfig['gateway_module'] ?? '';
        $oldController = $this->oldGatewayConfig['gateway_controller'] ?? '';
        $oldAction = $this->oldGatewayConfig['gateway_action'] ?? '';
        
        if ($this->oldGatewayConfig['security_gateway_enabled'] &&
            !empty($oldModule) && !empty($oldController) && !empty($oldAction)) {
            
            try {
                // 重新创建旧网关文件
                $this->createGatewayFiles($oldModule, $oldController, $oldAction);
            } catch (\Exception $e) {
                // 恢复失败，记录日志但不抛出异常
            }
        }
    }
    
    /**
     * 清理空目录
     * @param string $module
     * @param string $controller
     */
    private function cleanupEmptyDirectories($module, $controller) {
        // 从最内层开始清理
        $dirs = [
            $this->getAppRootPath() . $module . '/view/' . strtolower($controller) . '/',
            $this->getAppRootPath() . $module . '/view/',
            $this->getAppRootPath() . $module . '/controller/',
            $this->getAppRootPath() . $module . '/'
        ];
        
        foreach ($dirs as $dir) {
            $this->removeIfEmpty($dir);
        }
    }
    
    /**
     * 如果是空目录则删除
     * @param string $dir
     */
    private function removeIfEmpty($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = scandir($dir);
        if ($files && count($files) <= 2) {
            @rmdir($dir);
        }
    }
    
    /**
     * 创建网关文件（用于恢复）
     * @param string $module
     * @param string $controller
     * @param string $action
     * @throws \Exception
     */
    private function createGatewayFiles($module, $controller, $action) {
        // 获取目录路径
        $moduleDir = $this->getModuleDirPath($module);
        $controllerDir = $this->getAppRootPath() . $module . '/controller/';
        $viewDir = $this->getAppRootPath() . $module . '/view/' . strtolower($controller) . '/';
        
        // 创建模块目录（如果不存在）
        if (!is_dir($moduleDir)) {
            if (!mkdir($moduleDir, 0755, true)) {
                throw new \Exception('无法创建模块目录：' . $moduleDir);
            }
        }
        
        // 创建控制器目录（如果不存在）
        if (!is_dir($controllerDir)) {
            if (!mkdir($controllerDir, 0755, true)) {
                throw new \Exception('无法创建控制器目录：' . $controllerDir);
            }
        }
        
        // 创建控制器文件
        $controllerFile = $this->getControllerFilePath($module, $controller);
        if (!file_exists($controllerFile)) {
            $controllerContent = $this->generateControllerContent($module, $controller, $action);
            if (file_put_contents($controllerFile, $controllerContent) === false) {
                throw new \Exception('无法创建控制器文件：' . $controllerFile);
            }
        }
        
        // 创建视图目录（如果不存在）
        if (!is_dir($viewDir)) {
            if (!mkdir($viewDir, 0755, true)) {
                throw new \Exception('无法创建视图目录：' . $viewDir);
            }
        }
        
        // 创建视图文件
        $viewFile = $this->getViewFilePath($module, $controller, $action);
        if (!file_exists($viewFile)) {
            $viewContent = $this->generateViewContent();
            if (file_put_contents($viewFile, $viewContent) === false) {
                throw new \Exception('无法创建视图文件：' . $viewFile);
            }
        }
    }
    
    /**
     * @description 修改信息之前查询信息的
     * @buildcode(true)
     */
    function getInfo() {
        $data['status'] = 200;
        $data['data'] = config('my');
        return json($data);
    }
    
    /**
     * 获取应用根目录路径
     * @return string
     */
    private function getAppRootPath() {
        return app()->getRootPath() . 'app/';
    }
    
    /**
     * 获取控制器文件路径
     * @param string $module
     * @param string $controller
     * @return string
     */
    private function getControllerFilePath($module, $controller) {
        return $this->getAppRootPath() . $module . '/controller/' . ucfirst($controller) . '.php';
    }
    
    /**
     * 获取视图文件路径
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return string
     */
    private function getViewFilePath($module, $controller, $action) {
        return $this->getAppRootPath() . $module . '/view/' . strtolower($controller) . '/' . $action . '.html';
    }
    
    /**
     * 获取模块目录路径
     * @param string $module
     * @return string
     */
    private function getModuleDirPath($module) {
        return $this->getAppRootPath() . $module . '/';
    }
    
    /**
     * 生成控制器内容
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return string
     */
    private function generateControllerContent($module, $controller, $action) {
        $controllerName = ucfirst($controller);
        $namespace = 'app\\' . $module . '\\controller';
        $methodContent = $this->generateMethodContent($action);
        
        return <<<PHP
<?php

namespace {$namespace};

use app\BaseController;

class {$controllerName} extends BaseController
{
{$methodContent}
}
PHP;
    }
    
    /**
     * 生成方法内容
     * @param string $action
     * @return string
     */
    private function generateMethodContent($action) {
        return <<<PHP
    public function {$action}()
    {
        session(config("my.auth_session_key"), config("my.auth_session_value"));
        return view('{$action}', ['url' => config("my.redirect_url")]);
    }
PHP;
    }
    
    /**
     * 生成视图内容
     * @return string
     */
    private function generateViewContent() {
        return <<<HTML
<script>
    window.location.href = "{\$url}";
</script>
HTML;
    }
    
    /**
     * 处理微信支付证书路径
     * 当路径前缀与项目根目录相同时，替换为动态路径
     * @param array $wechartPay 微信支付配置数组
     * @return array 处理后的配置数组
     */
    private function handleWechatPayPaths(array $wechartPay): array {
        $rootPath = app()->getRootPath();
        
        // 处理证书路径
        if (!empty($wechartPay['cert_path'])) {
            $wechartPay['cert_path'] = $this->replaceRootPath($wechartPay['cert_path'], $rootPath);
        }
        
        // 处理密钥路径
        if (!empty($wechartPay['key_path'])) {
            $wechartPay['key_path'] = $this->replaceRootPath($wechartPay['key_path'], $rootPath);
        }
        
        return $wechartPay;
    }
    
    /**
     * 替换路径中的根目录为动态获取方式
     * @param string $path     原始路径
     * @param string $rootPath 项目根目录
     * @return string 处理后的路径
     */
    private function replaceRootPath(string $path, string $rootPath): string {
        // 标准化路径分隔符，统一使用/
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedRoot = rtrim(str_replace('\\', '/', $rootPath), '/');
        
        // 检查路径是否以根目录开头
        if (strpos($normalizedPath, $normalizedRoot) === 0) {
            // 提取相对路径部分
            $relativePath = substr($normalizedPath, strlen($normalizedRoot));
            // 清理多余的斜杠
            $relativePath = ltrim($relativePath, '/');
            // 返回动态路径表达式标记
            return '__ROOT_PATH__' . $relativePath;
        }
        
        // 路径不匹配时返回原始路径
        return $path;
    }
    
    /**
     * 生成配置文件内容
     * @param array $data
     * @return string
     */
    private function generateConfigContent(array $data): string {
        // 处理数组类型的nocheck
        $nocheck = isset($data['nocheck']) ? $data['nocheck'] : [];
        if (is_string($nocheck)) {
            $nocheck = explode("\n", $nocheck);
            $nocheck = array_filter(array_map('trim', $nocheck));
        }
        
        // 处理嵌套数组
        $miniProgram = isset($data['mini_program']) ? $data['mini_program'] : [];
        $officialAccounts = isset($data['official_accounts']) ? $data['official_accounts'] : [];
        $wechartPay = isset($data['wechart_pay']) ? $data['wechart_pay'] : [];
        $domain = $this->request->domain();
        
        // 模板内容 - 使用与原文件完全一致的注释格式
        $template = <<<PHP
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 自定义配置
// +----------------------------------------------------------------------
return [
    'upload_subdir' => '{$this->escapeValue($data['upload_subdir'] ?? 'Ym')}', // 文件上传二级目录 标准的日期格式
    'nocheck' => {$this->formatArray($nocheck)}, // 不需要验证权限的url
    'error_log_code' => {$this->escapeValue($data['error_log_code'] ?? 500)}, // 写入日志的状态码
    'password_secrect' => '{$this->escapeValue($data['password_secrect'] ?? 'tfadmin')}', // 密码加密秘钥
    'multiple_login' => {$this->formatBoolean($data['multiple_login'] ?? true)}, // 后台单点登录 true 允许多个账户登录 false 只允许一个账户登录
    'dump_extension' => '{$this->escapeValue($data['dump_extension'] ?? 'xlsx')}', // 默认导出格式
    'verify_status' => {$this->formatBoolean($data['verify_status'] ?? false)}, // 后台登录验证码开关
    'water_img' => '{$this->escapeValue($data['water_img'] ?? '')}', // 水印图片路径
    'check_file_status' => {$this->formatBoolean($data['check_file_status'] ?? true)}, // 上传图片是否检测图片存在
    'show_home_chats' => {$this->formatBoolean($data['show_home_chats'] ?? true)}, // 是否显示首页图表
    'api_upload_auth' => {$this->formatBoolean($data['api_upload_auth'] ?? true)}, // api应用上传是否验证token  true 验证 false不验证 需要重新生成
    
    // 安全入口配置
    'security_gateway_enabled' => {$this->formatBoolean($data['security_gateway_enabled'] ?? false)}, // 安全入口启用状态
    'gateway_module' => '{$this->escapeValue($data['gateway_module'] ?? '')}', // 安全入口模块
    'gateway_controller' => '{$this->escapeValue($data['gateway_controller'] ?? '')}', // 安全入口控制器
    'gateway_action' => '{$this->escapeValue($data['gateway_action'] ?? '')}', // 安全入口操作
    'auth_session_key' => '{$this->escapeValue($data['auth_session_key'] ?? 'login_check')}', // 认证会话键名
    'auth_session_value' => {$this->escapeValue($data['auth_session_value'] ?? 1)}, // 认证会话值
    'redirect_url' => '{$this->escapeValue($data['redirect_url'] ?? $domain . '/admin/login/index')}', // 重定向地址
    
    // 腾讯云短信配置
    'tencent_sms_appid' => '{$this->escapeValue($data['tencent_sms_appid'] ?? '')}', // appiid
    'tencent_sms_appkey' => '{$this->escapeValue($data['tencent_sms_appkey'] ?? '')}', // appkey
    'tencent_sms_tempCode' => '{$this->escapeValue($data['tencent_sms_tempCode'] ?? '')}', // 短信模板id
    'tencent_sms_signname' => '{$this->escapeValue($data['tencent_sms_signname'] ?? 'tfadmin')}', // 短信签名
    
    // 阿里云短信配置
    'ali_sms_accessKeyId' => '{$this->escapeValue($data['ali_sms_accessKeyId'] ?? '')}', // 阿里云短信 keyId
    'ali_sms_accessKeySecret' => '{$this->escapeValue($data['ali_sms_accessKeySecret'] ?? '')}', // 阿里云短信 keysecret
    'ali_sms_signname' => '{$this->escapeValue($data['ali_sms_signname'] ?? 'tfadmin')}', // 签名
    'ali_sms_tempCode' => '{$this->escapeValue($data['ali_sms_tempCode'] ?? '')}', // 短信模板 Code
    
    // oss开启状态 以及配置指定oss
    'oss_status' => {$this->formatBoolean($data['oss_status'] ?? false)}, // true启用  false 不启用
    'oss_upload_type' => '{$this->escapeValue($data['oss_upload_type'] ?? 'server')}', // client 客户端直传  server 服务端传
    'oss_default_type' => '{$this->escapeValue($data['oss_default_type'] ?? 'ali')}', // oss使用类别 ali(阿里),qiniuyun(七牛),tencent(腾讯)
    
    // 阿里云oss配置
    'ali_oss_accessKeyId' => '{$this->escapeValue($data['ali_oss_accessKeyId'] ?? '')}', // 阿里云oss keyId
    'ali_oss_accessKeySecret' => '{$this->escapeValue($data['ali_oss_accessKeySecret'] ?? '')}', // 阿里云oss keysecret
    'ali_oss_endpoint' => '{$this->escapeValue($data['ali_oss_endpoint'] ?? '')}', // 建议填写自己绑定的域名
    'ali_oss_bucket' => '{$this->escapeValue($data['ali_oss_bucket'] ?? 'tfadmin')}', // 阿里云oss存储空间
    
    // 七牛云oss配置
    'qny_oss_accessKey' => '{$this->escapeValue($data['qny_oss_accessKey'] ?? '')}', // access_key
    'qny_oss_secretKey' => '{$this->escapeValue($data['qny_oss_secretKey'] ?? '')}', // secret_key
    'qny_oss_bucket' => '{$this->escapeValue($data['qny_oss_bucket'] ?? 'tfadmin')}', // bucket
    'qny_oss_domain' => '{$this->escapeValue($data['qny_oss_domain'] ?? '')}', // 七牛云访问的域名
    'qny_oss_client_uploadurl' => '{$this->escapeValue($data['qny_oss_client_uploadurl'] ?? 'http://up-z0.qiniup.com')}', // 七牛云客户端直传上传地址 不用动如果提示地址错误 根据提示换就行
    
    // 腾讯云cos配置
    'tencent_oss_secretId' => '{$this->escapeValue($data['tencent_oss_secretId'] ?? '')}', // 腾讯云keyId
    'tencent_oss_secretKey' => '{$this->escapeValue($data['tencent_oss_secretKey'] ?? '')}', // 腾讯云keysecret
    'tencent_oss_bucket' => '{$this->escapeValue($data['tencent_oss_bucket'] ?? '')}', // 腾讯云bucket
    'tencent_oss_region' => '{$this->escapeValue($data['tencent_oss_region'] ?? '')}', // 地区，根据自己的填写
    'tencent_oss_schema' => '{$this->escapeValue($data['tencent_oss_schema'] ?? 'http')}', // 访问前缀 支持http  https
    
    // api tf鉴权配置
    'tf_expire_time' => '{$this->escapeValue($data['tf_expire_time'] ?? '+7 day')}', // second秒 hour小时  minute分钟 day 天
    'tf_secrect' => '{$this->escapeValue($data['tf_secrect'] ?? 'KW11FbeWB3YKi0aGS0TxcHbCakmNeDnAj3DMrjxxnP5rdwxTxYb8irWZGZ5hYY7S')}', // 签名秘钥
    'tf_token' => '{$this->escapeValue($data['tf_token'] ?? 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjozNDM1MDkzNTcxNDM0NjU5ODQsImlzcyI6ImFwaXBvc3QiLCJleHAiOjE3NTM1MjMzMjh9.6hEA6AJugGJgDMjdfZ3AviseG4BCyZVwfG5Sq-d4IEU')}', // apipost鉴权token
    'tf_iss' => '{$this->escapeValue($data['tf_iss'] ?? 'client.tfadmin')}', // 发送端
    'tf_aud' => '{$this->escapeValue($data['tf_aud'] ?? 'server.tfadmin')}', // 接收端
    'tfExpireCode' => {$this->escapeValue($data['tfExpireCode'] ?? 101)}, // tf过期
    'tfErrorCode' => {$this->escapeValue($data['tfErrorCode'] ?? 102)}, // tf无效
    
    // 小程序配置
    'mini_program' => [
        'app_id' => '{$this->escapeValue($miniProgram['app_id'] ?? '')}', // 小程序appid
        'secret' => '{$this->escapeValue($miniProgram['secret'] ?? '')}', // 小程序secret
    ],
    
    // 公众号配置
    'official_accounts' => [
        'app_id' => '{$this->escapeValue($officialAccounts['app_id'] ?? '')}', // 公众号appid
        'secret' => '{$this->escapeValue($officialAccounts['secret'] ?? '')}', // 公众号secret
        'token' => '{$this->escapeValue($officialAccounts['token'] ?? '')}', // token
        'aes_key' => '{$this->escapeValue($officialAccounts['aes_key'] ?? '')}', // EncodingAESKey，兼容与安全模式下请一定要填写
    ],
    
    // 微信支付配置
    'wechart_pay' => [
        'mch_id' => '{$this->escapeValue($wechartPay['mch_id'] ?? '')}', // 商户号
        'key' => '{$this->escapeValue($wechartPay['key'] ?? '')}', // 微信支付32位秘钥
        'cert_path' => {$this->escapePath($wechartPay['cert_path'] ?? app()->getRootPath() . 'extend/utils/wechart/zcerts/apiclient_cert.pem')}, // 证书路径
        'key_path' => {$this->escapePath($wechartPay['key_path'] ?? app()->getRootPath() . 'extend/utils/wechart/zcerts/apiclient_key.pem')}, // 证书路径
    ],
];
PHP;
        
        return $template;
    }
    
    /**
     * 转义字符串值，防止单引号冲突
     * @param mixed $value
     * @return string
     */
    private function escapeValue($value): string {
        if (is_string($value)) {
            return addslashes($value);
        }
        return (string)$value;
    }
    
    /**
     * 处理路径值，特别是包含动态根目录的路径
     * @param mixed $path
     * @return string
     */
    private function escapePath($path): string {
        // 处理标记为根目录路径的情况
        if (is_string($path) && strpos($path, '__ROOT_PATH__') === 0) {
            $relativePath = substr($path, strlen('__ROOT_PATH__'));
            // 清理多余的斜杠
            $relativePath = ltrim($relativePath, '/');
            return "app()->getRootPath() . '" . addslashes($relativePath) . "'";
        }
        
        // 处理默认路径（已经是动态路径的情况）
        if (is_string($path) && strpos($path, 'app()->getRootPath()') !== false) {
            return $path;
        }
        
        // 普通路径处理
        return "'" . addslashes($path) . "'";
    }
    
    /**
     * 格式化布尔值为PHP语法
     * @param mixed $value
     * @return string
     */
    private function formatBoolean($value): string {
        return $value ? 'true' : 'false';
    }
    
    /**
     * 格式化数组为PHP语法
     * @param array $array
     * @return string
     */
    private function formatArray(array $array): string {
        if (empty($array)) {
            return '[]';
        }
        
        $items = [];
        foreach ($array as $item) {
            $items[] = "'" . addslashes($item) . "'";
        }
        
        return "[\n        " . implode(",\n        ", $items) . "\n    ]";
    }
}
