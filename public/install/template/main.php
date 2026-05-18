<?php !defined('install') && exit(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThinkPHP开源低代码平台安装</title>
    <link rel="stylesheet" type="text/css" href="https://www.layuicdn.com/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="./css/mounted.css"/>
    <style>
        /* 登录注册样式 */
        .auth-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #e8e8e8;
        }
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            cursor: pointer;
            font-size: 16px;
            color: #666;
            transition: all 0.3s;
        }
        .auth-tab.active {
            color: #1890ff;
            border-bottom: 2px solid #1890ff;
            margin-bottom: -2px;
        }
        .auth-form {
            display: none;
        }
        .auth-form.show {
            display: block;
        }
        .form-box-item {
            margin-bottom: 16px;
        }
        .form-desc {
            width: 100px;
            margin-bottom: 6px;
            color: #333;
            font-size: 14px;
        }
        .form-box-item input, .form-box-item select {
            width: 100%;
            height: 38px;
            padding: 0 12px;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-box-item input:focus, .form-box-item select:focus {
            border-color: #1890ff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(24,144,255,0.2);
        }
        .auth-tip {
            color: #999;
            font-size: 12px;
            margin-top: 4px;
        }
        .login-info {
            background: #e6f7ff;
            border: 1px solid #91d5ff;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 16px;
            color: #0050b3;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="mounted" id="mounted">
    <div class="mounted-box">
        <form method="post" action="#" name="main_form" id="main_form">
            <div class="mounted-title">安装步骤</div>
            <div class="mounted-container" id="tab">
                <ul class="mounted-nav" id="nav">
                    <li <?php if ($step == "1") { ?>class="active"<?php } ?>>许可协议</li>
                    <li <?php if ($step == "2") { ?>class="active"<?php } ?>>环境监测</li>
                    <li <?php if ($step == "3") { ?>class="active"<?php } ?>>参数配置</li>
                    <li <?php if ($step == "4") { ?>class="active"<?php } ?>>账号登录/注册</li>
                    <li <?php if ($step == "5") { ?>class="active"<?php } ?>>安装</li>
                </ul>

                <!-- 第一步：阅读许可 -->
                <?php if ($step == '1') { ?>
                    <div class="mounted-content-item show">
                        <div class="content-header">
                            ThinkPHP开源低代码平台授权协议
                        </div>
                        <div class="content">
                            <h2>版权所有(c)2005-<?=date('Y')?>，TFadmin团队保留所有权利。</h2>
                            <p class="mt16">
                                感谢你信任并选择ThinkPHP开源低代码平台，免费使用，快速构建企业应用。基于ThinkPHP框架，支持应用开发与销售，连接开发者与需求方的生态平台。</p>
                            <h3 class="mt16">MIT 开源许可证</h3>
                            <p class="mt6">
                                特此授予任何人获得本软件及相关文档文件（以下简称"软件"）副本的权利，允许其无限制地处理本软件，包括但不限于使用、复制、修改、合并、发布、分发、再许可和/或出售软件副本的权利，同时允许向获得软件的人员授予相同权利，唯需遵守以下条件：
                            </p>
                            <p class="mt6">
                                本软件按"原样"提供，不附带任何形式的明示或暗示担保，包括但不限于对适销性、特定用途适用性和非侵权性的担保。在任何情况下，作者或版权持有人均不对因使用本软件或无法使用本软件而产生的任何索赔、损害或其他责任承担责任，无论此类责任是基于合同、侵权行为或其他法律理论，即使已被告知可能发生此类损害。
                            </p>
                            <h3 class="mt16">特别限制条款：</h3>
                            <p class="mt6">
                                尽管本软件以高度开放的方式授权，但明确禁止对软件进行任何形式的反向工程、反编译或反汇编操作，无论通过何种技术手段实现。此限制旨在保护软件的核心架构设计及知识产权完整性。

　　本授权不构成对商标权、专利权或其他知识产权的让渡。在适用法律允许的最大范围内，作者不对因使用本软件产生的任何直接、间接、偶然或后果性损害承担责任（包括但不限于数据丢失、业务中断等）。

使用TFadmin即表示您同意遵守上述协议条款。如有任何疑问，请联系开发团队：jiufukeji@qq.com。
                            </p>
                        </div>
                    </div>
                <?php } ?>

                <!-- 第二步：检查信息 -->
                <?php if ($step == '2') { ?>
                    <div class="mounted-content-item show">
                        <div class="mounted-env-container">
                            <div class="mounted-item">
                                <div class="content-header">
                                    服务器信息
                                </div>
                                <div class="content-table">
                                    <table class="layui-table" lay-skin="line">
                                        <colgroup>
                                            <col width="210">
                                            <col width="730">
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th>参数</th>
                                            <th>值</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>服务器操作系统</td>
                                            <td><?php echo PHP_OS ?></td>
                                        </tr>
                                        <tr>
                                            <td>web服务器环境</td>
                                            <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>PHP版本</td>
                                            <td><?php echo @phpversion(); ?></td>
                                        </tr>
                                        <tr>
                                            <td>程序安装目录</td>
                                            <td><?php echo str_replace('/public/install/template', '', realpath(__DIR__)); ?></td>
                                        </tr>
                                        <tr>
                                            <td>磁盘空间</td>
                                            <td><?php echo $modelInstall->freeDiskSpace(str_replace('/public/install/template', '', realpath(__DIR__))) ?></td>
                                        </tr>
                                        <tr>
                                            <td>上传限制</td>
                                            <?php if (ini_get('file_uploads')): ?>
                                                <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                            <?php else: ?>
                                                <td>禁止上传</td>
                                            <?php endif; ?>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mounted-tips mt16">PHP环境要求必须满足下列所有条件，否则系统或系统部分功能将无法使用。</div>
                            <div class="mounted-item mt16">
                                <div class="content-header">
                                    PHP环境要求
                                </div>
                                <div class="content-table">
                                    <table class="layui-table" lay-skin="line">
                                        <colgroup>
                                            <col width="210">
                                            <col width="210">
                                            <col width="120">
                                            <col width="400">
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th>选项</th>
                                            <th>要求</th>
                                            <th>状态</th>
                                            <th>说明及帮助</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>PHP版本</td>
                                            <td>大于8.0</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkPHP()) ?>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>PDO_MYSQL</td>
                                            <td>支持 (强烈建议支持)</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkPDOMySQL()) ?>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>allow_url_fopen</td>
                                            <td>支持 (建议支持cURL)</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkCurl()) ?>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>GD2</td>
                                            <td>支持</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkGd2()) ?>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>DOM</td>
                                            <td>支持</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkDom()) ?>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>session.auto_start</td>
                                            <td>关闭</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkSessionAutoStart()) ?>
                                            <td></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mounted-tips mt16">
                                系统要安装目录下的runtime和upload必须可写，才能使用所有功能。
                            </div>
                            <div class="mounted-item mt16">
                                <div class="content-header">
                                    目录权限监测
                                </div>
                                <div class="content-table">
                                    <table class="layui-table" lay-skin="line">
                                        <colgroup>
                                            <col width="210">
                                            <col width="210">
                                            <col width="120">
                                            <col width="400">
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th>目录</th>
                                            <th>要求</th>
                                            <th>状态</th>
                                            <th>说明及帮助</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>/server/runtime</td>
                                            <td>runtime目录可写</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkDirWrite('runtime')) ?>
                                            <td><?php if($modelInstall->checkDirWrite('runtime') =='fail') echo'请给runtime目录权限，若目录不存在先新建';?></td>
                                        </tr>
                                        <tr>
                                            <td>/server/public/uploads</td>
                                            <td>uploads目录可写</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkDirWrite('public/uploads')) ?>
                                            <td><?php if($modelInstall->checkDirWrite('public/uploads')=='fail') echo'请给public/uploads目录权限，若目录不存在先新建';?></td>
                                        </tr>
                                        <tr>
                                            <td>/server/config</td>
                                            <td>config目录可写</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkDirWrite('config')) ?>
                                            <td><?php if($modelInstall->checkDirWrite('config')=='fail') echo'请给config目录权限，若目录不存在先新建';?></td>
                                        </tr>
                                        <tr>
                                            <td>/server/.env</td>
                                            <td>.env文件可写</td>
                                            <?php echo $modelInstall->correctOrFail($modelInstall->checkDirWrite('.env')) ?>
                                            <td><?php if($modelInstall->checkDirWrite('.env')=='fail') echo'请给.env文件权限，若文件不存在，注意文件名第1字符是" . "';?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!-- 第三步：数据库设置 -->
                <?php if ($step == '3') { ?>
                    <div class="mounted-content-item show">
                        <div class="mounted-item">
                            <div class="content-header">
                                数据库选项
                            </div>
                            <div class="content-form">
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        数据库主机
                                    </div>
                                    <div>
                                        <input type="text" name="host" value="<?= $post['host'] ?>"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        端口号
                                    </div>
                                    <div>
                                        <input type="text" name="port" value="<?= $post['port'] ?>"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        数据库用户
                                    </div>
                                    <div>
                                        <input type="text" name="user" value="<?= $post['user'] ?>"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        数据库密码
                                    </div>
                                    <div>
                                        <input type="text" name="password" value="<?= $post['password'] ?>"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        数据库名称
                                    </div>
                                    <div>
                                        <input type="text" name="name" value="<?= $post['name'] ?>"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        数据表前缀
                                    </div>
                                    <div>
                                        <input type="text" name="prefix" value="<?= $post['prefix'] ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mounted-item">
                            <div class="content-header mt16">
                                管理选项
                            </div>
                            <div class="content-form">
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        管理员账号
                                    </div>
                                    <div>
                                        <input type="text" name="admin_user" value="<?= $post['admin_user'] ?>"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        管理员密码
                                    </div>
                                    <div>
                                        <input type="password" name="admin_password"
                                               value="<?= $post['admin_password'] ?>"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        确认密码
                                    </div>
                                    <div>
                                        <input type="password" name="admin_confirm_password"
                                               value="<?= $post['admin_confirm_password'] ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                
                <!-- 第四步：账号登录/注册 -->
                <?php if ($step == '4') { ?>
                    <div class="mounted-content-item show">
                        <div class="mounted-item">
                            <div class="login-info">
                                <div>请登录您的账号以继续安装，如果没有账号请先注册。</div>
                            </div>
                            
                            <!-- 切换标签 -->
                            <div class="auth-tabs">
                                <div class="auth-tab <?php echo ($post['auth_action'] == 'login' || $post['auth_action'] == '') ? 'active' : ''; ?>" 
                                     onclick="switchAuthTab('login')">账号登录</div>
                                <div class="auth-tab <?php echo $post['auth_action'] == 'register' ? 'active' : ''; ?>" 
                                     onclick="switchAuthTab('register')">账号注册</div>
                            </div>
                
                            <!-- 登录表单 -->
                            <div class="auth-form <?php echo ($post['auth_action'] == 'login' || $post['auth_action'] == '') ? 'show' : ''; ?>" id="login-form">
                                <input type="hidden" name="auth_action" value="login" id="login-action">
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        <span style="color: red;">*</span> 账号
                                    </div>
                                    <div>
                                        <input type="text" name="login_username" id="login-username" 
                                               value="<?= htmlspecialchars($post['login_username'] ?? $post['username']) ?>" 
                                               placeholder="请输入账号"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        <span style="color: red;">*</span> 密码
                                    </div>
                                    <div>
                                        <input type="password" name="login_password" id="login-password" 
                                               value="" 
                                               placeholder="请输入密码"/>
                                    </div>
                                </div>
                            </div>
                
                            <!-- 注册表单 -->
                            <div class="auth-form <?php echo $post['auth_action'] == 'register' ? 'show' : ''; ?>" id="register-form">
                                <input type="hidden" name="auth_action" value="register" id="register-action">
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        <span style="color: red;">*</span> 会员姓名
                                    </div>
                                    <div>
                                        <input type="text" name="customer_name" id="register-customer-name" 
                                               value="<?= htmlspecialchars($post['customer_name']) ?>" 
                                               placeholder="个人填写姓名，企业填写企业名称"/>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        <span style="color: red;">*</span> 注册类型
                                    </div>
                                    <div>
                                        <select name="customer_type" id="register-customer-type">
                                            <option value="1" <?= $post['customer_type'] == '1' ? 'selected' : '' ?>>个人</option>
                                            <option value="2" <?= $post['customer_type'] == '2' ? 'selected' : '' ?>>企业</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        <span style="color: red;">*</span> 联系方式
                                    </div>
                                    <div>
                                        <input type="text" name="customer_phone" id="register-phone" 
                                               value="<?= htmlspecialchars($post['customer_phone']) ?>" 
                                               placeholder="请输入手机号码"/>
                                    </div>
                                    <div class="auth-tip">请填写有效的手机号码</div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        <span style="color: red;">*</span> 账号
                                    </div>
                                    <div>
                                        <input type="text" name="register_username" id="register-username" 
                                               value="<?= htmlspecialchars($post['register_username'] ?? $post['username']) ?>" 
                                               placeholder="请设置登录账号"/>
                                    </div>
                                    <div class="auth-tip">账号长度4-20位</div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        <span style="color: red;">*</span> 密码
                                    </div>
                                    <div>
                                        <input type="password" name="register_password" id="register-password" 
                                               placeholder="请设置密码（不少于6位）"/>
                                    </div>
                                    <div class="auth-tip">密码长度不少于6位</div>
                                </div>
                                <div class="form-box-item">
                                    <div class="form-desc">
                                        <span style="color: red;">*</span> 确认密码
                                    </div>
                                    <div>
                                        <input type="password" name="register_confirm_password" id="register-confirm-password" 
                                               placeholder="请再次输入密码"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!-- 第五步：安装成功 -->
                <?php if ($step == '5') { ?>
                    <div class="mounted-content-item show">
                        <div class="show" id="mounting-success">
                            <div class="content-header">
                                安装成功
                            </div>
                            <div class="success-content">
                                <div style="width: 48px;height: 48px;">
                                    <img src="./images/icon_mountSuccess.png"/>
                                </div>
                                <div class="mt16 result">安装完成，进入管理后台</div>
                                <div style="margin-top: 5px;font-size:14px;">版本号：3.0.3.20231204</div>
                                <div class="tips">
                                    为了您站点的安全，安装完成后即可将网站根目录下的"install"文件夹删除，或者config/install.lock/目录下创建install.lock文件防止重复安装。
                                </div>
                                <div class="btn-group">
                                    <a class="btn" href="/admin" style="margin-left: 20px;">进入管理平台</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </form>
        
        <?php if ($step == '1') { ?>
            <div class="item-btn-group show">
                <button class="accept-btn" onclick="goStep(<?php echo $nextStep ?>)">我已阅读并同意</button>
            </div>
        <?php } elseif (in_array($step, ['2', "3", "4"])) { ?>
            <div class="item-btn-group show">
                <button class="cancel-btn" onclick="cancel()" style="padding: 7px 63px;margin-right: 16px">返回
                </button>
                <?php if ($step == '2'): ?>
                    <?php if ($modelInstall->getAllowNext()): ?>
                        <button class="accept-btn" onclick="goStep(<?php echo $nextStep ?>)" style="padding: 7px 63px;">
                            继续
                        </button>
                    <?php else: ?>
                        <button class="accept-btn" onclick="goStep(<?php echo $step ?>)" style="padding: 7px 63px;">重新检查
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="accept-btn" onclick="goStep(<?php echo $nextStep ?>)" style="padding: 7px 63px;">
                        继续
                    </button>
                <?php endif; ?>
            </div>
        <?php } ?>
    </div>
</div>
<script src="https://www.layuicdn.com/layui/layui.js"></script>
<script src="./js/mounted.js"></script>
<script>
    // 切换登录/注册标签
    function switchAuthTab(type) {
        // 切换标签激活状态
        var tabs = document.querySelectorAll('.auth-tab');
        tabs.forEach(function(tab) {
            tab.classList.remove('active');
        });
        
        // 切换表单显示
        var forms = document.querySelectorAll('.auth-form');
        forms.forEach(function(form) {
            form.classList.remove('show');
        });
        
        if (type === 'login') {
            tabs[0].classList.add('active');
            document.getElementById('login-form').classList.add('show');
            // 更新auth_action隐藏字段
            document.getElementById('login-action').disabled = false;
            document.getElementById('register-action').disabled = true;
            // 清空注册表单
            clearRegisterForm();
        } else {
            tabs[1].classList.add('active');
            document.getElementById('register-form').classList.add('show');
            // 更新auth_action隐藏字段
            document.getElementById('register-action').disabled = false;
            document.getElementById('login-action').disabled = true;
            // 清空登录表单
            clearLoginForm();
        }
    }
    
    // 清空登录表单
    function clearLoginForm() {
        document.getElementById('login-username').value = '';
        document.getElementById('login-password').value = '';
    }
    
    // 清空注册表单
    function clearRegisterForm() {
        document.getElementById('register-customer-name').value = '';
        document.getElementById('register-phone').value = '';
        document.getElementById('register-username').value = '';
        document.getElementById('register-password').value = '';
        document.getElementById('register-confirm-password').value = '';
        document.getElementById('register-customer-type').value = '1';
    }
    
    // 重写goStep函数，添加表单验证
    var originalGoStep = window.goStep;
    if (typeof originalGoStep === 'function') {
        window.goStep = function(step) {
            // 如果是第4步跳转到第5步，需要验证登录/注册表单
            if (step === 5 && <?= $step ?> === 4) {
                var activeTab = document.querySelector('.auth-tab.active');
                var isLogin = activeTab.textContent.trim() === '账号登录';
                console.log('isLogin',isLogin);
                if (isLogin) {
                    // 验证登录表单
                    var username = document.getElementById('login-username').value.trim();
                    var password = document.getElementById('login-password').value.trim();
                    if (!username) {
                        alert('请输入账号');
                        return false;
                    }
                    if (!password) {
                        alert('请输入密码');
                        return false;
                    }
                } else {
                    // 验证注册表单
                    var customerName = document.getElementById('register-customer-name').value.trim();
                    var customerPhone = document.getElementById('register-phone').value.trim();
                    var username = document.getElementById('register-username').value.trim();
                    var password = document.getElementById('register-password').value.trim();
                    var confirmPassword = document.getElementById('register-confirm-password').value.trim();
                    
                    if (!customerName) {
                        alert('请填写会员姓名');
                        return false;
                    }
                    if (!customerPhone) {
                        alert('请填写联系方式');
                        return false;
                    }
                    if (!/^1[3-9]\d{9}$/.test(customerPhone)) {
                        alert('请输入正确的手机号码');
                        return false;
                    }
                    if (!username) {
                        alert('请填写账号');
                        return false;
                    }
                    if (username.length < 4 || username.length > 20) {
                        alert('账号长度应为4-20位');
                        return false;
                    }
                    if (!password) {
                        alert('请设置密码');
                        return false;
                    }
                    if (password.length < 6) {
                        alert('密码不能少于6位');
                        return false;
                    }
                    if (password !== confirmPassword) {
                        alert('两次密码不一致');
                        return false;
                    }
                }
            }
            
            // 调用原始的goStep函数
            originalGoStep(step);
        };
    }
    
    // 页面加载时的初始化
    document.addEventListener('DOMContentLoaded', function() {
        // 初始化登录/注册表单状态
        var activeTab = document.querySelector('.auth-tab.active');
        if (activeTab) {
            var tabText = activeTab.textContent.trim();
            if (tabText === '账号登录') {
                document.getElementById('register-action').disabled = true;
            } else {
                document.getElementById('login-action').disabled = true;
            }
        }
        
        // 监听注册类型的placeholder变化
        var customerType = document.getElementById('register-customer-type');
        if (customerType) {
            customerType.addEventListener('change', function() {
                var customerNameInput = document.getElementById('register-customer-name');
                if (this.value === '1') {
                    customerNameInput.placeholder = '请输入姓名';
                } else {
                    customerNameInput.placeholder = '请输入企业名称';
                }
            });
        }
    });
</script>
</body>
</html>
<?php if ($message != ''): ?>
    <script>alert('<?=$message; ?>');</script>
<?php endif; ?>