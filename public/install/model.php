<?php
/** 安装界面需要的各种模块 */

class installModel
{
    private $host;
    private $name;
    private $user;
    private $encoding;
    private $password;
    private $port;
    private $prefix;
    private $successTable = [];
    /**
     * @var bool
     */
    private $allowNext = true;
    /**
     * @var PDO|string
     */
    private $dbh = null;
    /**
     * @var bool
     */
    private $clearDB = false;


    /**
     * Notes: php版本
     * @author luzg(2020/8/25 9:56)
     * @return string
     */
    public function getPhpVersion()
    {
        return PHP_VERSION;
    }

    /**
     * Notes: 当前版本是否符合
     * @author luzg(2020/8/25 9:57)
     * @return string
     */
    public function checkPHP()
    {
        return $result = version_compare(PHP_VERSION, '8.0.0') >= 0 ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否有PDO
     * @author luzg(2020/8/25 9:57)
     * @return string
     */
    public function checkPDO()
    {
        return $result = extension_loaded('pdo') ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否有PDO::MySQL
     * @author luzg(2020/8/25 9:58)
     * @return string
     */
    public function checkPDOMySQL()
    {
        return $result = extension_loaded('pdo_mysql') ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否支持JSON
     * @author luzg(2020/8/25 9:58)
     * @return string
     */
    public function checkJSON()
    {
        return $result = extension_loaded('json') ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否支持openssl
     * @author luzg(2020/8/25 9:58)
     * @return string
     */
    public function checkOpenssl()
    {
        return $result = extension_loaded('openssl') ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否支持mbstring
     * @author luzg(2020/8/25 9:58)
     * @return string
     */
    public function checkMbstring()
    {
        return $result = extension_loaded('mbstring') ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否支持zlib
     * @author luzg(2020/8/25 9:59)
     * @return string
     */
    public function checkZlib()
    {
        return $result = extension_loaded('zlib') ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否支持curl
     * @author luzg(2020/8/25 9:59)
     * @return string
     */
    public function checkCurl()
    {
        return $result = extension_loaded('curl') ? 'ok' : 'fail';
    }

    /**
     * Notes: 检查GD2扩展
     * @author luzg(2020/8/26 9:59)
     * @return string
     */
    public function checkGd2()
    {
        return $result = extension_loaded('gd') ? 'ok' : 'fail';
    }

    /**
     * Notes: 检查Dom扩展
     * @author luzg(2020/8/26 9:59)
     * @return string
     */
    public function checkDom()
    {
        return $result = extension_loaded('dom') ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否支持filter
     * @author luzg(2020/8/25 9:59)
     * @return string
     */
    public function checkFilter()
    {
        return $result = extension_loaded('filter') ? 'ok' : 'fail';
    }

    /**
     * Notes: 是否支持iconv
     * @author luzg(2020/8/25 9:59)
     * @return string
     */
    public function checkIconv()
    {
        return $result = extension_loaded('iconv') ? 'ok' : 'fail';
    }

    /**
     * Notes: 取得临时目录路径
     * @author luzg(2020/8/25 10:05)
     * @return array
     */
    public function getTmpRoot()
    {
        $path = $this->getAppRoot() . '/runtime';
        return [
            'path'     => $path,
            'exists'   => is_dir($path),
            'writable' => is_writable($path),
        ];
    }

    /**
     * Notes: 检查临时路径
     * @author luzg(2020/8/25 10:06)
     * @return string
     */
    public function checkTmpRoot()
    {
        $tmpRoot = $this->getTmpRoot()['path'];
        return $result = (is_dir($tmpRoot) and is_writable($tmpRoot)) ? 'ok' : 'fail';
    }

    /**
     * Notes: SESSION路径是否可写
     * @author luzg(2020/8/25 10:06)
     * @return mixed
     */
    public function getSessionSavePath()
    {
        $sessionSavePath = preg_replace("/\d;/", '', session_save_path());

        return [
            'path'     => $sessionSavePath,
            'exists'   => is_dir($sessionSavePath),
            'writable' => is_writable($sessionSavePath),
        ];
    }

    /**
     * Notes: 检查session路径可写状态
     * @author luzg(2020/8/25 10:13)
     * @return string
     */
    public function checkSessionSavePath()
    {
        $sessionSavePath = preg_replace("/\d;/", '', session_save_path());
        $result = (is_dir($sessionSavePath) and is_writable($sessionSavePath)) ? 'ok' : 'fail';
        if ($result == 'fail') return $result;

        file_put_contents($sessionSavePath . '/zentaotest', 'zentao');
        $sessionContent = file_get_contents($sessionSavePath . '/zentaotest');
        if ($sessionContent == 'zentao') {
            unlink($sessionSavePath . '/zentaotest');
            return 'ok';
        }
        return 'fail';
    }

    /**
     * Notes: 取得data目录是否可选
     * @author luzg(2020/8/25 10:58)
     * @return array
     */
    public function getDataRoot()
    {
        $path = $this->getAppRoot();
        return [
            'path'     => $path . 'www' . DS . 'data',
            'exists'   => is_dir($path),
            'writable' => is_writable($path),
        ];
    }

    /**
     * Notes: 取得root路径
     * @author luzg(2020/8/25 11:02)
     * @return string
     */
    public function checkDataRoot()
    {
        $dataRoot = $this->getAppRoot() . 'www' . DS . 'data';
        return $result = (is_dir($dataRoot) and is_writable($dataRoot)) ? 'ok' : 'fail';
    }

    /**
     * Notes: 取得php.ini信息
     * @author luzg(2020/8/25 11:03)
     * @return string
     */
    public function getIniInfo()
    {
        $iniInfo = '';
        ob_start();
        phpinfo(1);
        $lines = explode("\n", strip_tags(ob_get_contents()));
        ob_end_clean();
        foreach ($lines as $line) if (strpos($line, 'ini') !== false) $iniInfo .= $line . "\n";
        return $iniInfo;
    }


    /**
     * Notes: 创建安装锁定文件
     * @author luzg(2020/8/28 11:32)
     * @return bool
     */
    public function mkLockFile()
    {
        return touch($this->getAppRoot() . '/config/install.lock');
    }

    /**
     * Notes: 检查之前是否有安装
     * @author luzg(2020/8/28 11:36)
     */
    public function appIsInstalled()
    {
        return file_exists($this->getAppRoot() . '/config/install.lock');
    }

    /**
     * Notes: 取得配置信息
     * @author luzg(2020/8/25 11:05)
     * @param string $dbName 数据库名称
     * @param array $connectionInfo 连接信息
     * @return stdclass
     * @throws Exception
     */
    public function checkConfig($dbName, $connectionInfo)
    {
        $return = new stdclass();
        $return->result = 'ok';

        /* Connect to database. */
        $this->setDBParam($connectionInfo);
        $this->dbh = $this->connectDB();
        if (strpos($dbName, '.') !== false) {
            $return->result = 'fail';
            $return->error = '没有发现数据库信息';
            return $return;
        }
        if ( !is_object($this->dbh)) {
            $return->result = 'fail';
            $return->error = '安装错误，请检查连接信息:'.mb_strcut($this->dbh,0,30).'...';
            echo $this->dbh;
            return $return;
        }

        /* Get mysql version. */
        $version = $this->getMysqlVersion();

        /* If database no exits, try create it. */
        if ( !$this->dbExists()) {
            if ( !$this->createDB($version)) {
                $return->result = 'fail';
                $return->error = '创建数据库错误';
                return $return;
            }
        } elseif ($this->tableExits() and $this->clearDB == false) {
            $return->result = 'fail';
            $return->error = '数据表已经存在，您之前应该有安装过本系统，继续安装请选择清空现有数据';
            return $return;
        } elseif ($this->dbExists() and $this->clearDB == true) {
            if (!$this->dropDb($connectionInfo['name'])) {
                $return->result = 'fail';
                $return->error = '数据表已经存在，删除已存在库错误,请手动清除';
                return $return;
            } else {
                if ( !$this->createDB($version)) {
                    $return->result = 'fail';
                    $return->error = '创建数据库错误!';
                    return $return;
                }
            }
        }

        /* Create tables. */
        if ( !$this->createTable($version, $connectionInfo)) {
            $return->result = 'fail';
            $return->error = '创建表格失败';
            return $return;
        }

        return $return;
    }

    /**
     * Notes: 设置数据库相关信息
     * @author luzg(2020/8/25 11:17)
     * @param $post
     */
    public function setDBParam($post)
    {
        $this->host = $post['host'];
        $this->name = $post['name'];
        $this->user = $post['user'];
        $this->encoding = 'utf8mb4';
        $this->password = $post['password'];
        $this->port = $post['port'];
        $this->prefix = $post['prefix'];
        $this->clearDB = $post['clear_db'] == 'on';
    }

    /**
     * Notes: 连接数据库
     * @author luzg(2020/8/25 11:56)
     * @return PDO|string
     */
    public function connectDB()
    {
        $dsn = "mysql:host={$this->host}; port={$this->port}";
        try {
            $dbh = new PDO($dsn, $this->user, $this->password);
            $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->exec("SET NAMES {$this->encoding}");
            try{
                $dbh->exec("SET GLOBAL sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");
            }catch (Exception $e){

            }
            return $dbh;
        } catch (PDOException $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * Notes: 检查数据库是否存在
     * @author luzg(2020/8/25 11:56)
     * @return mixed
     */
    public function dbExists()
    {
        $sql = "SHOW DATABASES like '{$this->name}'";
        return $this->dbh->query($sql)->fetch();
    }

    /**
     * Notes: 检查表是否存在
     * @author luzg(2020/8/25 11:56)
     * @return mixed
     */
    public function tableExits()
    {
        $configTable = sprintf("'%s'", $this->prefix . TESTING_TABLE);
        $sql = "SHOW TABLES FROM {$this->name} like $configTable";
        return $this->dbh->query($sql)->fetch();
    }

    /**
     * Notes: 获取mysql版本号
     * @author luzg(2020/8/25 11:56)
     * @return false|string
     */
    public function getMysqlVersion()
    {
        $sql = "SELECT VERSION() AS version";
        $result = $this->dbh->query($sql)->fetch();
        return substr($result->version, 0, 3);
    }

    /**
     * Notes: 创建数据库
     * @author luzg(2020/8/25 11:57)
     * @param $version
     * @return mixed
     */
    public function createDB($version)
    {
        $sql = "CREATE DATABASE `{$this->name}`";
        if ($version > 4.1) $sql .= " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
        return $this->dbh->query($sql);
    }

    /**
     * Notes: 创建表
     * @author luzg(2020/8/25 11:57)
     * @param $version
     * @param $post
     * @return bool
     * @throws Exception
     */
    public function createTable($version, $post)
    {
        // 【重要】先选择数据库
        try {
            $this->dbh->exec("USE `{$this->name}`");
        } catch (PDOException $e) {
            echo '选择数据库失败: ' . $e->getMessage() . "<br>";
            return false;
        }
        
        $dbFile = $this->getInstallRoot() . '/db/import.sql';
        
        if (!file_exists($dbFile)) {
            echo 'SQL文件不存在: ' . $dbFile . "<br>";
            return false;
        }
        
        // 读取 SQL 文件内容
        $content = file_get_contents($dbFile);
        if (empty($content)) {
            echo 'SQL文件内容为空<br>';
            return false;
        }
        
        // 替换表前缀 - 先替换库名.表名的情况，再单独替换表名
        $content = str_replace('`cd_', '`' . $this->prefix, $content);
        
        // 处理 MySQL 8.0 的兼容性问题
        // 移除 COLLATE 子句中的 utf8mb4_0900_ai_ci（如果存在）
        $content = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_general_ci', $content);
        
        // 分离 SQL 语句
        $statements = $this->splitSQL($content);
        
        // 添加管理员账号初始化 SQL
        $initAccountSql = $this->initAccount($post);
        $statements[] = $initAccountSql;
        
        $installTime = (int)(microtime(true) * 10000);
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($statements as $index => $sql) {
            $sql = trim($sql);
            if (empty($sql)) {
                continue;
            }
            
            // 跳过注释
            if (strpos($sql, '--') === 0 || strpos($sql, '/*') === 0) {
                continue;
            }
            
            // 记录创建表的信息
            if (preg_match('/CREATE\s+TABLE\s+`([^`]+)`/i', $sql, $matches)) {
                $tableName = $matches[1];
                $installTime += random_int(3000, 7000);
                $this->successTable[] = [$tableName, date('Y-m-d H:i:s', (int)($installTime / 10000))];
            }
            
            try {
                // 执行 SQL
                $result = $this->dbh->exec($sql);
                $successCount++;
                
                // 调试信息（生产环境可注释掉）
                // if (preg_match('/CREATE\s+TABLE/i', $sql)) {
                //     echo "✓ 创建表成功: " . ($matches[1] ?? 'unknown') . "<br>";
                // }
                
            } catch (PDOException $e) {
                $errorCount++;
                $errorInfo = [
                    'sql' => substr($sql, 0, 200),
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
                $errors[] = $errorInfo;
                
                // 输出错误信息（生产环境可注释掉）
                echo "✗ SQL执行失败 [" . ($index + 1) . "]:<br>";
                echo "SQL: " . htmlspecialchars(substr($sql, 0, 300)) . "...<br>";
                echo "错误: " . $e->getMessage() . "<br><br>";
                
                // 如果是 CREATE TABLE 失败，这是严重错误
                if (preg_match('/CREATE\s+TABLE/i', $sql)) {
                    echo "严重错误：创建表失败，终止安装<br>";
                    return false;
                }
            }
        }
        
        // 输出统计信息（生产环境可注释掉）
        echo "SQL执行完成：成功 {$successCount} 条，失败 {$errorCount} 条<br>";
        
        if ($errorCount > 0) {
            // 记录错误日志
            error_log("SQL import errors: " . json_encode($errors, JSON_UNESCAPED_UNICODE));
        }
        
        return true;
    }

    /**
     * 安全地分割 SQL 语句
     * 处理多行语句、存储过程等复杂情况
     * 
     * @param string $sql 完整的 SQL 内容
     * @return array SQL 语句数组
     */
    private function splitSQL($sql)
    {
        $statements = [];
        $current = '';
        $delimiter = ';';
        $inString = false;
        $stringChar = '';
        
        // 按行处理
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // 检测 DELIMITER 命令（存储过程等）
            if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $trimmedLine, $matches)) {
                // 保存当前语句（如果有）
                if (!empty(trim($current))) {
                    $statements[] = $current;
                    $current = '';
                }
                $delimiter = $matches[1];
                continue;
            }
            
            // 跳过空行
            if (empty($trimmedLine)) {
                $current .= $line . "\n";
                continue;
            }
            
            // 跳过纯注释行
            if (strpos($trimmedLine, '--') === 0 || strpos($trimmedLine, '#') === 0) {
                $current .= $line . "\n";
                continue;
            }
            
            $current .= $line . "\n";
            
            // 检查是否到达分隔符
            if (substr($trimmedLine, -strlen($delimiter)) === $delimiter) {
                // 移除末尾的分隔符
                $statement = substr($current, 0, -strlen($delimiter));
                $statement = trim($statement);
                
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                
                $current = '';
            }
        }
        
        // 处理最后一条可能没有分隔符的语句
        $current = trim($current);
        if (!empty($current) && $current !== ';') {
            $statements[] = $current;
        }
        
        return $statements;
    }

    /**
     * Notes: 删除数据库
     * @param $db
     * @return false|PDOStatement
     */
    public function dropDb($db)
    {
        $sql = "drop database {$db};";
        return $this->dbh->query($sql);
    }

    /**
     * Notes: 取得安装成功的表列表
     * @author luzg(2020/8/26 18:28)
     * @return array
     */
    public function getSuccessTable()
    {
        return $this->successTable;
    }

    /**
     * 将一个文件夹下的所有文件及文件夹
     * 复制到另一个文件夹里（保持原有结构）
     *
     * @param <string> $rootFrom 源文件夹地址（最好为绝对路径）
     * @param <string> $rootTo 目的文件夹地址（最好为绝对路径）
     */
    function cpFiles($rootFrom, $rootTo){

            $handle = opendir($rootFrom);
            while (false !== ($file = readdir($handle))) {
                //DIRECTORY_SEPARATOR 为系统的文件夹名称的分隔符 例如：windos为'/'; linux为'/'
                $fileFrom = $rootFrom . DIRECTORY_SEPARATOR . $file;
                $fileTo = $rootTo . DIRECTORY_SEPARATOR . $file;
                if ($file == '.' || $file == '..') {
                    continue;
                }

                    if (is_dir($fileFrom)) {
                        if (!is_dir($fileTo)) { //目标目录不存在则创建
                            mkdir($fileTo, 0777);
                        }
                        $this->cpFiles($fileFrom, $fileTo);
                    } else {
                        if (!file_exists($fileTo)) {
                            @copy($fileFrom, $fileTo);
                            if (strstr($fileTo, "access_token.txt")) {
                                chmod($fileTo, 0777);
                            }
                        }
                    }

            }
    }

    /**
     * Notes: 当前应用程序的相对路径
     * @author luzg(2020/8/25 10:55)
     * @return string
     */
    public function getAppRoot()
    {
        return realpath($this->getInstallRoot() . '/../../');
    }

    /**
     * Notes: 获取安装目录
     * @author luzg(2020/8/26 16:15)
     * @return string
     */
    public function getInstallRoot()
    {
        return INSTALL_ROOT;
    }

    /**
     * Notes: 目录的容量
     * @author luzg(2020/8/25 15:21)
     * @param $dir
     * @return string
     */
    public function freeDiskSpace($dir)
    {
        // M
        $freeDiskSpace = disk_free_space(realpath(__DIR__)) / 1024 / 1024;

        // G
        if ($freeDiskSpace > 1024) {
            return number_format($freeDiskSpace / 1024, 2) . 'G';
        }

        return number_format($freeDiskSpace, 2) . 'M';
    }

    /**
     * Notes: 获取状态标志
     * @author luzg(2020/8/25 16:10)
     * @param $statusSingle
     * @return string
     */
    public function correctOrFail($statusSingle)
    {
        if ($statusSingle == 'ok')
            return '<td class="layui-icon green">&#xe605;</td>';

        $this->allowNext = false;
        return '<td class="layui-icon wrong">&#x1006;</td>';
    }

    /**
     * Notes: 是否允许下一步
     * @author luzg(2020/8/25 17:29)
     * @return bool
     */
    public function getAllowNext()
    {
        return $this->allowNext;
    }

    /**
     * Notes: 检查session auto start
     * @author luzg(2020/8/25 16:55)
     * @return string
     */
    public function checkSessionAutoStart()
    {
        return $result = ini_get('session.auto_start') == '0' ? 'ok' : 'fail';
    }

    /**
     * Notes: 检查auto tags
     * @author luzg(2020/8/25 16:55)
     * @return string
     */
    public function checkAutoTags()
    {
        return $result = ini_get('session.auto_start') == '0' ? 'ok' : 'fail';
    }

    /**
     * Notes: 检查目录是否可写
     * @param $dir
     * @return string
     */
    public function checkDirWrite($dir='')
    {
        $route = $this->getAppRoot().'/'.$dir;
        return $result = is_writable($route) ? 'ok' : 'fail';
    }

    /**
     * Notes: 检查目录是否可写
     * @param $dir
     * @return string
     */
    public function checkSuperiorDirWrite($dir='')
    {
        $route = $this->getAppRoot().'/'.$dir;
        return $result = is_writable($route) ? 'ok' : 'fail';
    }


    /**
     * Notes: 初始化管理账号
     * @param $post
     * @return string
     */
    public function initAccount($post)
    {
        $time = time();
        $session_token = md5(uniqid(rand(), true));
        $salt = 'tfadmin';//随机密码盐
        $password = md5($post['admin_password'].'tfadmin');
        $sql = "INSERT INTO `{$this->prefix}admin_user` VALUES (1, '超级管理员', '{$post['admin_user']}', '{$password}', '1', '超级管理员', 1, '{$time}', '{$session_token}');";

        return $sql;
    }
    
    /**
     * Notes: 保存或更新secret配置
     * @param string $name
     * @param string $data
     * @return bool
     */
    public function saveSecret($name, $data)
    {
        // 【重要】先重新连接数据库，因为这是新的HTTP请求
        // 从已保存的.env文件中读取数据库配置
        $envFilePath = $this->getAppRoot() . '/.env';
        $envConfig = parse_ini_file($envFilePath, true);
        $dbConfig = [
            'host' => $envConfig['database']['hostname'] ?? '',
            'port' => $envConfig['database']['hostport'] ?? '3306',
            'user' => $envConfig['database']['username'] ?? '',
            'password' => $envConfig['database']['password'] ?? '',
            'name' => $envConfig['database']['database'] ?? '',
            'prefix' => $envConfig['database']['prefix'] ?? 'cd_',
            'clear_db' => 'off',
        ];
    
        /* Connect to database. */
        $this->setDBParam($dbConfig);
        $this->dbh = $this->connectDB();

        try {
            // 选择数据库
            $this->dbh->exec("USE `{$this->name}`");
            
            // 先检查记录是否存在
            $tableName = $this->prefix . 'secrect';
            $sql = "SELECT secrect_id FROM `{$tableName}` WHERE `name` = :name LIMIT 1";
            
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([':name' => $name]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($row) {
                // 存在则更新
                $sql = "UPDATE `{$tableName}` SET `data` = :data WHERE `name` = :name";
                $stmt = $this->dbh->prepare($sql);
                return $stmt->execute([':data' => $data, ':name' => $name]);
            } else {
                // 不存在则插入
                $sql = "INSERT INTO `{$tableName}` (`name`, `data`) VALUES (:name, :data)";
                $stmt = $this->dbh->prepare($sql);
                return $stmt->execute([':name' => $name, ':data' => $data]);
            }
        } catch (PDOException $e) {
            error_log("saveSecret error: " . $e->getMessage());
            return false;
        }
    }
}