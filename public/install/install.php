<?php
// error_reporting(0);
include "model.php";
include "YxEnv.php";

define('install', true);
define('INSTALL_ROOT', __DIR__);
define('TESTING_TABLE', 'config');

$step = $_GET['step'] ?? 1;

$installDir = "install";
$modelInstall = new installModel();

// Env设置
$yxEnv = new YxEnv();

// 检查是否有安装过
$envFilePath = $modelInstall->getAppRoot() . '/.env';
if ($modelInstall->appIsInstalled() && in_array($step, [1, 2, 3, 4])) {
    die('可能已经安装过本系统了，请删除配置目录下面的install.lock文件再尝试');
}

// 加载Example文件
$yxEnv->load($modelInstall->getAppRoot() . '/.example.env');

// 尝试生成.env
$yxEnv->makeEnv($modelInstall->getAppRoot() . '/.env');

$post = [
    'host' => $_POST['host'] ?? '127.0.0.1',
    'port' => $_POST['port'] ?? '3306',
    'user' => $_POST['user'] ?? 'root',
    'password' => $_POST['password'] ?? '',
    'name' => $_POST['name'] ?? 'tfadmin',
    'admin_user' => $_POST['admin_user'] ?? '',
    'admin_password' => $_POST['admin_password'] ?? '',
    'admin_confirm_password' => $_POST['admin_confirm_password'] ?? '',
    'prefix' => $_POST['prefix'] ?? 'cd_',
    'clear_db' => 'on',
    
    'login_username' => $_POST['login_username'] ?? '',
    'login_password' => $_POST['login_password'] ?? '',
    
    'auth_action' => $_POST['auth_action'] ?? 'login',
    'customer_name' => $_POST['customer_name'] ?? '',
    'customer_type' => $_POST['customer_type'] ?? '1',
    'customer_phone' => $_POST['customer_phone'] ?? '',
    'username' => $_POST['username'] ?? '',
    'password' => $_POST['password'] ?? '',
    'confirm_password' => $_POST['confirm_password'] ?? '',
    
    'customer_domain' => $_SERVER['HTTP_HOST'],
    'app_name' => $_SERVER['HTTP_HOST'],
];
$message = '';

// ============ 第三步提交处理：检查数据库配置 + 创建数据表和管理员账号 ============
if ($step == 4 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $canNext = true;
    if (empty($post['prefix'])) {
        $canNext = false;
        $message = '数据表前缀不能为空';
    } elseif(strpos($post['name'], '-') !== false) {
        $canNext = false;
        $message = '数据库名不能包含字符 -';
    } elseif(strpos($post['user'], '-') !== false) {
        $canNext = false;
        $message = '数据用户名名不能包含字符 -';
    } elseif ($post['admin_user'] == '') {
        $canNext = false;
        $message = '请填写管理员用户名';
    } elseif (empty(trim($post['admin_password']))) {
        $canNext = false;
        $message = '管理员密码不能为空';
    } elseif ($post['admin_password'] != $post['admin_confirm_password']) {
        $canNext = false;
        $message = '两次密码不一致';
    } else {
        // 检查数据库信息并创建数据表
        $result = $modelInstall->checkConfig($post['name'], $post);
        if ($result->result == 'fail') {
            $canNext = false;
            $message = $result->error;
        }

        // 写入.env配置文件
        if ($canNext) {
            $yxEnv->putEnv($envFilePath, $post);
        }
    }

    if (!$canNext) {
        // 如果失败，停留在第三步
        $step = 3;
    }
    // 如果成功，$step 保持为 4，显示登录/注册页面
}

// ============ 第四步提交处理：处理登录/注册API，保存secret ============
if ($step == 5 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $canInstall = true;
    $authAction = $post['auth_action'];

    if ($authAction === 'login') {
        // 验证登录信息
        $loginUsername = $_POST['login_username'] ?? '';
        $loginPassword = $_POST['login_password'] ?? '';
        
        if (empty($loginUsername)) {
            $canInstall = false;
            $message = '请输入账号';
        } elseif (empty($loginPassword)) {
            $canInstall = false;
            $message = '请输入密码';
        } else {
            // 调用登录API
            $apiUrl = 'https://tfadmin.tiefen.net/index/Register/index';
            $loginData = [
                'action' => 'login',
                'username' => $loginUsername,
                'password' => $loginPassword,
                'customer_domain' => $post['customer_domain'],
                'app_name' => $post['app_name']
            ];
            
            $apiResponse = callApi($apiUrl, $loginData);
            
            if ($apiResponse && isset($apiResponse['status']) && $apiResponse['status'] == 200) {
                // 登录成功，保存认证信息
                $modelInstall->saveSecret('appid', $apiResponse['data']['customer_appid']);
                $modelInstall->saveSecret('secrect', $apiResponse['data']['customer_key']);
                $canInstall = true;
            } else {
                $canInstall = false;
                $message = $apiResponse['msg'] ?? '登录失败，请检查账号密码';
            }
        }
    } elseif ($authAction === 'register') {
        // 验证注册信息
        $registerUsername = $_POST['register_username'] ?? '';
        $registerPassword = $_POST['register_password'] ?? '';
        $registerConfirmPassword = $_POST['register_confirm_password'] ?? '';
        
        if (empty($post['customer_name'])) {
            $canInstall = false;
            $message = '请填写会员姓名';
        } elseif (empty($post['customer_phone'])) {
            $canInstall = false;
            $message = '请填写联系方式';
        } elseif (empty($registerUsername)) {
            $canInstall = false;
            $message = '请填写账号';
        } elseif (empty($registerPassword)) {
            $canInstall = false;
            $message = '请设置密码';
        } elseif ($registerPassword !== $registerConfirmPassword) {
            $canInstall = false;
            $message = '两次密码不一致';
        } else {
            // 调用注册API
            $apiUrl = 'https://tfadmin.tiefen.net/index/Register/index';
            $registerData = [
                'action' => 'register',
                'customer_name' => $post['customer_name'],
                'customer_type' => $post['customer_type'],
                'customer_phone' => $post['customer_phone'],
                'username' => $registerUsername,
                'password' => $registerPassword,
                'customer_domain' => $post['customer_domain'],
                'app_name' => $post['app_name']
            ];
            
            $apiResponse = callApi($apiUrl, $registerData);
            
            if ($apiResponse && isset($apiResponse['status']) && $apiResponse['status'] == 200) {
                // 注册成功，保存认证信息
                $modelInstall->saveSecret('appid', $apiResponse['data']['customer_appid']);
                $modelInstall->saveSecret('secrect', $apiResponse['data']['customer_key']);
                $canInstall = true;
            } else {
                $canInstall = false;
                $message = $apiResponse['msg'] ?? '注册失败，请重试';
            }
        }
    }
    
    // 如果登录/注册失败，返回第4步
    if (!$canInstall) {
        $step = 4;
    } else {
        $modelInstall->mkLockFile();
    }
    // 如果成功，$step 保持为 5，显示安装成功页面
}

/**
 * 调用API接口
 */
function callApi($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        return json_decode($response, true);
    }
    
    return null;
}

// 取得安装成功的表（用于第三步创建表后的反馈，但实际第五步直接显示成功）
$successTables = $modelInstall->getSuccessTable();

$nextStep = $step + 1;
include __DIR__ . "/template/main.php";
?>