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
    'upload_subdir' => 'Ym', // 文件上传二级目录 标准的日期格式
    'nocheck' => [
        '/admin/Login/verify.html'
        , '/admin/Login/index.html'
        , '/admin/Login/logout.html'
        , '/admin/Base/getMenu.html'
        , '/admin/Index/index.html'
        , '/admin/Upload/upload.html'
        , '/admin/Upload/createFile.html'
        , '/admin/Login/aliOssCallBack.html'
    ], // 不需要验证权限的url
    'error_log_code' => 500, // 写入日志的状态码
    'password_secrect' => 'tfadmin', // 密码加密秘钥
    'multiple_login' => true, // 后台单点登录 true 允许多个账户登录 false 只允许一个账户登录
    'dump_extension' => 'xlsx', // 默认导出格式
    'verify_status' => false, // 后台登录验证码开关
    'water_img' => '', // 水印图片路径
    'check_file_status' => true, // 上传图片是否检测图片存在
    'show_home_chats' => true, // 是否显示首页图表
    'api_upload_auth' => true, // api应用上传是否验证token  true 验证 false不验证 需要重新生成
    
    // 安全入口配置
    'security_gateway_enabled' => false, // 安全入口启用状态
    'gateway_module' => '', // 安全入口模块
    'gateway_controller' => '', // 安全入口控制器
    'gateway_action' => '', // 安全入口操作
    'auth_session_key' => 'login_check', // 认证会话键名
    'auth_session_value' => 1, // 认证会话值
    'redirect_url' => app()->request->domain() . '/admin/login/index', // 重定向地址
    
    // 腾讯云短信配置
    'tencent_sms_appid' => '', // appiid
    'tencent_sms_appkey' => '', // appkey
    'tencent_sms_tempCode' => '', // 短信模板id
    'tencent_sms_signname' => 'tfadmin', // 短信签名
    
    // 阿里云短信配置
    'ali_sms_accessKeyId' => '', // 阿里云短信 keyId
    'ali_sms_accessKeySecret' => '', // 阿里云短信 keysecret
    'ali_sms_signname' => 'tfadmin', // 签名
    'ali_sms_tempCode' => '', // 短信模板 Code
    
    // oss开启状态 以及配置指定oss
    'oss_status' => false, // true启用  false 不启用
    'oss_upload_type' => 'server', // client 客户端直传  server 服务端传
    'oss_default_type' => 'ali', // oss使用类别 ali(阿里),qiniuyun(七牛),tencent(腾讯)
    
    // 阿里云oss配置
    'ali_oss_accessKeyId' => '', // 阿里云oss keyId
    'ali_oss_accessKeySecret' => '', // 阿里云oss keysecret
    'ali_oss_endpoint' => '', // 建议填写自己绑定的域名
    'ali_oss_bucket' => 'tfadmin', // 阿里云oss存储空间
    
    // 七牛云oss配置
    'qny_oss_accessKey' => '', // access_key
    'qny_oss_secretKey' => '', // secret_key
    'qny_oss_bucket' => 'tfadmin', // bucket
    'qny_oss_domain' => '', // 七牛云访问的域名
    'qny_oss_client_uploadurl' => 'http://up-z0.qiniup.com', // 七牛云客户端直传上传地址 不用动如果提示地址错误 根据提示换就行
    
    // 腾讯云cos配置
    'tencent_oss_secretId' => '', // 腾讯云keyId
    'tencent_oss_secretKey' => '', // 腾讯云keysecret
    'tencent_oss_bucket' => '', // 腾讯云bucket
    'tencent_oss_region' => '', // 地区，根据自己的填写
    'tencent_oss_schema' => 'http', // 访问前缀 支持http  https
    
    // api tf鉴权配置
    'tf_expire_time' => '+7 day', // second秒 hour小时  minute分钟 day 天
    'tf_secrect' => 'KW11FbeWB3YKi0aGS0TxcHbCakmNeDnAj3DMrjxxnP5rdwxTxYb8irWZGZ5hYY7S', // 签名秘钥
    'tf_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjozNDM1MDkzNTcxNDM0NjU5ODQsImlzcyI6ImFwaXBvc3QiLCJleHAiOjE3NTM1MjMzMjh9.6hEA6AJugGJgDMjdfZ3AviseG4BCyZVwfG5Sq-d4IEU', // apipost鉴权token
    'tf_iss' => 'client.tfadmin', // 发送端
    'tf_aud' => 'server.tfadmin', // 接收端
    'tfExpireCode' => 101, // tf过期
    'tfErrorCode' => 102, // tf无效
    
    // 小程序配置
    'mini_program' => [
        'app_id' => '', // 小程序appid
        'secret' => '', // 小程序secret
    ],
    
    // 公众号配置
    'official_accounts' => [
        'app_id' => '', // 公众号appid
        'secret' => '', // 公众号secret
        'token' => '', // token
        'aes_key' => '', // EncodingAESKey，兼容与安全模式下请一定要填写
    ],
    
    // 微信支付配置
    'wechart_pay' => [
        'mch_id' => '', // 商户号
        'key' => '', // 微信支付32位秘钥
        'cert_path' => app()->getRootPath() . 'extend/utils/wechart/zcerts/apiclient_cert.pem', // 证书路径
        'key_path' => app()->getRootPath() . 'extend/utils/wechart/zcerts/apiclient_key.pem', // 证书路径
    ],
];
