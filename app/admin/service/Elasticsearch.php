<?php

namespace app\admin\service;

use think\facade\Db;
use think\facade\Cache;
use Elastic\Elasticsearch\ClientBuilder;
use think\facade\Config;

class Elasticsearch
{
    /**
     * Elasticsearch客户端实例
     * @var mixed
     */
    private $esClient = null;
    
    /**
     * ES配置
     * @var array
     */
    private $esConfig = [
        'host' => '',
        'index' => '',
        'auth' => []
    ];
    
    /**
     * 文本片段处理器配置
     * @var array
     */
    private $fragmentConfig = [
        'min_fragment_length' => 5,
        'max_fragment_length' => 40,
        'step_size' => 3,
        'max_fragments_per_field' => 30,
        'include_short_fragments' => true,
        'strip_whitespace' => true,
        'lowercase' => true,
        'always_generate_fragment' => true
    ];
    
    /**
     * 构造函数
     * @param string $index ES索引名称
     * @param array  $sys_config 配置数据
     */
    public function __construct(string $index, array $sys_config = [])
    {
        if (empty($sys_config)) {
            $this->esConfig['host'] = config('my.esdb_hostname');
            $this->esConfig['index'] = $index;
            $this->esConfig['auth'] = [
                config('my.esdb_username'),
                config('my.esdb_password')
            ];
        } else {
            $this->esConfig['host'] = $sys_config['esdb_hostname'];
            $this->esConfig['index'] = $index;
            $this->esConfig['auth'] = [
                $sys_config['esdb_username'],
                $sys_config['esdb_password']
            ];
            
        }
        $this->initClient();
    }
    
    /**
     * 初始化ES客户端
     * @return bool
     */
    private function initClient()
    {
        if ($this->esClient === null) {
            try {
                $this->esClient = ClientBuilder::create()
                    ->setHosts([$this->esConfig['host']])
                    ->setBasicAuthentication($this->esConfig['auth'][0], $this->esConfig['auth'][1])
                    ->build();
                
                if (!$this->esClient->ping()->asBool()) {
                    throw new \Exception('ES服务器无响应');
                }
                
                return true;
                
            } catch (\Exception $e) {
                $this->esClient = false;
                return false;
            }
        }
        
        return $this->esClient !== false;
    }
    
    /**
     * 设置文本片段处理器配置
     * @param array $config 配置数组
     */
    public function setFragmentConfig(array $config): void
    {
        $this->fragmentConfig = array_merge($this->fragmentConfig, $config);
    }
    
    public function runEsScriptWithShellExec($tableName, $pk)
    {
        $logFile = root_path() . "/public/es_sync_{$tableName}.log";
        $pidFile = root_path() . "/public/es_sync_{$tableName}.pid";
        
        $password = escapeshellarg(Config::get('database.connections.mysql.password'));
        $esPassword = escapeshellarg(config('my.esdb_password'));
        $database = escapeshellarg(Config::get('database.connections.mysql.database'));
        $username = escapeshellarg(Config::get('database.connections.mysql.username'));
        $port = escapeshellarg(Config::get('database.connections.mysql.hostport'));
        $path = escapeshellarg(root_path() . 'public/es.py');
        $escapedTableName = escapeshellarg($tableName);
        $escapedPk = escapeshellarg($pk);
        $escapedLogFile = escapeshellarg($logFile);
        
        // 方法1: 使用Python内置的日志功能（推荐）
        $command = "nohup python3 {$path} \
        --mysql-host=127.0.0.1 \
        --mysql-port={$port} \
        --mysql-user={$username} \
        --mysql-password={$password} \
        --mysql-db={$database} \
        --es-host={$this->esConfig['host']} \
        --es-user=elastic \
        --es-password={$esPassword} \
        --table-name={$escapedTableName} \
        --primary-key={$escapedPk} \
        --start-id=0 \
        --log-file={$escapedLogFile} \
        --log-level=INFO > /dev/null 2>&1 & echo $!";
        
        // dump($command);die;
        // 执行命令，获取进程ID
        $pid = shell_exec($command);
        $pid = trim($pid);
        
        // 将PID保存到文件，方便后续管理
        if ($pid && is_numeric($pid)) {
            file_put_contents($pidFile, $pid);
        }
        
        return json([
            'code' => 200,
            'msg' => '任务已启动，正在后台执行',
            'data' => [
                'pid' => $pid,
                'log_file' => $logFile
            ]
        ]);
    }
    
    public function viewLog($tableName)
    {
        $logFile = root_path() . "/public/es_sync_{$tableName}.log";
        
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $fileSize = filesize($logFile);
            $lastModified = date('Y-m-d H:i:s', filemtime($logFile));
            // 方法2：或者使用更严格的方法
            $cleanContent = $this->removeNonUtf8($logContent);
            
            return json([
                'status' => 200,
                'msg' => '日志获取成功',
                'data' => [
                    'file_size' => $fileSize,
                    'last_modified' => $lastModified,
                    'content' => $cleanContent,
                    'tail' => substr($cleanContent, -2000) // 最后2000字符
                ]
            ]);
        } else {
            return json([
                'status' => 404,
                'msg' => '日志文件不存在'
            ]);
        }
    }
    
    private function removeNonUtf8($string)
    {
        // 移除BOM头
        $string = preg_replace('/\x{FEFF}/u', '', $string);
        
        // 使用iconv转换
        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);
        
        // 或者使用正则过滤
        $string = preg_replace('/[^\x{0000}-\x{FFFF}]/u', '', $string);
        
        return $string;
    }
    
    /**
     * 通用ES搜索方法
     * @param array $params 查询参数
     * @param array $options 选项
     * @return array|false
     */
    public function search($params, $options = [])
    {
        if (!$this->esClient) {
            return false;
        }
        
        $startTime = microtime(true);
        
        // 默认选项
        $defaultOptions = [
            'limit' => 20,
            'page' => 1,
            'direction' => 'next',
            'last_id' => 0,
            'order_field' => $params['sort'] ?? 'id',
            'order_direction' => $params['order'] ?? 'desc',
            'with_relations' => false,
            'with_count' => true,
            'primary_key' => 'id',
            'or_conditions' => [], // 传入LIKE查询字段
            'like_fields' => [], // LIKE查询字段（由控制器传入）
            'eq_conditions' => [], // 等值查询条件（由控制器传入）
            'where_conditions' => [], // 权限条件（由控制器传入）
            'where_or_groups' => [], // where里的OR分组条件（由控制器传入）
            'deleted_field' => null, // 删除标记字段
            'deleted_value' => 0, // 删除标记值
            'join_tables' => [], // 多表联查配置
            'join_fields' => [], // 联查字段映射
            'main_table' => null, // 主表名称
            'join_table_first' => false, // 是否先查询关联表
        ];
        
        $options = array_merge($defaultOptions, $options);
        try {
            // 1. 处理多表联查逻辑（新版：先查子表再查主表）
            if ($options['with_relations'] && !empty($options['join_tables']) && $options['join_table_first']) {
                $result = $this->searchWithJoinsNew($params, $options);
                return $result;
            }
            
            // 3. 构建ES查询
            $query = $this->buildEsQuery($params, $options);
            // 4. 构建排序
            $sort = $this->buildSort(
                $options['order_field'],
                $options['order_direction'],
                $options['primary_key']
            );
            
            // 5. 构建搜索参数
            $esParams = [
                'index' => $this->esConfig['index'],
                'body' => [
                    'query' => $query,
                    'sort' => $sort,
                    'size' => $options['limit'],
                    'track_total_hits' => $options['with_count']
                ]
            ];
//            dump($esParams);die;
            // 6. 处理游标分页
            if ($options['last_id'] > 0) {
                if ($options['direction'] === 'prev') {
                    // 上一页逻辑
                    $firstRecordSortValues = $this->getRecordSortValues(
                        $options['last_id'],
                        $options['order_field'],
                        $options['order_direction'],
                        $options['primary_key']
                    );
                    
                    if (!empty($firstRecordSortValues)) {
                        // 构建反转排序
                        $reversedSort = $this->buildReversedSort(
                            $options['order_field'],
                            $options['order_direction'],
                            $options['primary_key']
                        );
                        
                        $esParams = [
                            'index' => $this->esConfig['index'],
                            'body' => [
                                'query' => $query,
                                'sort' => $reversedSort,
                                'size' => $options['limit'],
                                'track_total_hits' => $options['with_count'],
                                'search_after' => $firstRecordSortValues
                            ]
                        ];
                        
                        $response = $this->esClient->search($esParams);
                    }
                } else {
                    // 下一页逻辑
                    $lastRecordSortValues = $this->getRecordSortValues(
                        $options['last_id'],
                        $options['order_field'],
                        $options['order_direction'],
                        $options['primary_key']
                    );
                    
                    // 构建正常排序
                    $sort = $this->buildSort(
                        $options['order_field'],
                        $options['order_direction'],
                        $options['primary_key']
                    );
                    
                    $esParams = [
                        'index' => $this->esConfig['index'],
                        'body' => [
                            'query' => $query,
                            'sort' => $sort,
                            'size' => $options['limit'],
                            'track_total_hits' => $options['with_count']
                        ]
                    ];
                    
                    if (!empty($lastRecordSortValues)) {
                        $esParams['body']['search_after'] = $lastRecordSortValues;
                    }
                    
                    $response = $this->esClient->search($esParams);
                }
            } else {
                $response = $this->esClient->search($esParams);
            }
            
            // 7. 执行查询
//            $response = $this->esClient->search($esParams);
            // 8. 处理结果
            $hits = $response['hits']['hits'] ?? [];
            $total = $options['with_count'] ? ($response['hits']['total']['value'] ?? 0) : 0;
            
            $list = $this->processHits($hits);
            
            // 9. 处理上一页方向的数据反转
            if ($options['direction'] === 'prev' && !empty($list)) {
                $list = array_reverse($list);
            }
            
            // 10. 构建返回数据
            $result = $this->buildResponse($list, $total, $options);
            $result['search_engine'] = 'elasticsearch';
            
            // 11. 清理ES特殊字段并处理数组格式
            $result['data'] = array_map(function ($item) {
                unset($item['es_score'], $item['es_id']);
                // 将数组类型的值转换为普通值（取第一个元素）
                foreach ($item as $key => $value) {
                    if (is_array($value) && !str_ends_with($key, '.keyword')) {
                        // 如果是空数组，设置为空字符串
                        if (empty($value)) {
                            $item[$key] = '';
                        } else {
                            // 取第一个元素，如果是嵌套数组，递归处理
                            $firstValue = reset($value);
                            if (is_array($firstValue)) {
                                // 对于嵌套数组，转为JSON字符串或逗号分隔
                                $item[$key] = json_encode($firstValue, JSON_UNESCAPED_UNICODE);
                            } else {
                                $item[$key] = $firstValue;
                            }
                        }
                    }
                }
                return $item;
            }, $result['data']);
            
            // 12. 记录查询信息
            $queryTime = microtime(true) - $startTime;
            
            return [
                'status' => 200,
                'data' => $result,
                'query_time' => round($queryTime, 3),
                'index' => $this->esConfig['index'],
                'query_info' => [
                    'query_type' => 'elasticsearch',
                    'total_hits' => $total,
                    'returned_hits' => count($list)
                ]
            ];
            
        } catch (\Exception $e) {
            dump($e->getMessage());
            die;
            // 记录详细错误信息
            \think\facade\Log::error('ES查询异常: ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * 新版多表联查搜索方法（先查子表再查主表）
     * @param array $params 查询参数
     * @param array $options 选项
     * @return array
     */
    public function searchWithJoinsNew($params, $options = [])
    {
        if (!$this->esClient) {
            return [
                'status' => 500,
                'msg' => 'ES连接失败',
                'data' => []
            ];
        }
        
        $startTime = microtime(true);
        
        try {
            // 1. 首先在关联表中查询，获取满足条件的主表ID
            $mainIds = $this->queryJoinTablesFirst($params, $options);
            // 如果没有在关联表中找到数据，直接返回空结果
            if (empty($mainIds)) {
                return [
                    'status' => 200,
                    'data' => $this->buildEmptyResponse($options),
                    'query_time' => round(microtime(true) - $startTime, 3),
                    'index' => $this->esConfig['index'],
                    'query_info' => [
                        'query_type' => 'elasticsearch_with_joins_new',
                        'join_table_first' => true,
                        'found_main_ids' => 0
                    ]
                ];
            }
            
            // 2. 构建主表查询条件
            $mainParams = $params;
            // 添加主表ID条件（IN查询）
            $mainParams[$options['primary_key']] = implode(',', $mainIds);
            // 3. 构建主表查询选项
            $mainOptions = $options;
            $mainOptions['eq_conditions'] = [$options['primary_key'] => implode(',', $mainIds)];
            $mainOptions['with_relations'] = false;
            $mainOptions['join_table_first'] = false;
            // 4. 查询主表数据
            \think\facade\Log::info('开始查询主表数据，主表ID数量: ' . count($mainIds));
            $mainResult = $this->search($mainParams, $mainOptions);
            if ($mainResult === false || $mainResult['status'] !== 200) {
                throw new \Exception('主表查询失败');
            }
            
            $mainData = $mainResult['data']['data'] ?? [];
            \think\facade\Log::info('主表查询结果数量: ' . count($mainData));
            if (empty($mainData)) {
                return [
                    'status' => 200,
                    'data' => $this->buildEmptyResponse($options),
                    'query_time' => round(microtime(true) - $startTime, 3),
                    'index' => $this->esConfig['index'],
                    'query_info' => [
                        'query_type' => 'elasticsearch_with_joins_new',
                        'join_table_first' => true,
                        'found_main_ids' => count($mainIds),
                        'main_records' => 0
                    ]
                ];
            }
            // 5. 再次查询关联表，获取详细的关联数据
            $joinResults = $this->queryJoinTablesDetails($mainIds, $options);
            // 6. 将关联数据合并到主表数据中
            $this->mergeJoinResults($mainData, $joinResults, $options);
            
            // 7. 处理字段别名
            $this->processFieldAliases($mainData, $options);
            
            // 8. 构建返回结果
            $result = $this->buildJoinResponse($mainData, $mainResult['data'], $options);
            
            $queryTime = microtime(true) - $startTime;
            
            \think\facade\Log::info('新版多表联查完成，总耗时: ' . $queryTime . 's');
            
            return [
                'status' => 200,
                'data' => $result,
                'query_time' => round($queryTime, 3),
                'index' => $this->esConfig['index'],
                'query_info' => [
                    'query_type' => 'elasticsearch_with_joins_new',
                    'join_table_first' => true,
                    'found_main_ids' => count($mainIds),
                    'main_records' => count($mainData)
                ]
            ];
            
        } catch (\Exception $e) {
            \think\facade\Log::error('新版多表联查异常: ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return [
                'status' => 500,
                'msg' => '多表联查失败: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * 首先查询关联表，获取主表ID
     * @param array $params 查询参数
     * @param array $options 选项
     * @return array
     */
    private function queryJoinTablesFirst($params, $options)
    {
        $mainIds = [];
        
        foreach ($options['join_tables'] as $joinTable => $joinConfig) {
            try {
                \think\facade\Log::info("开始查询关联表（聚合去重）: {$joinTable}");
                
                // 1. 构建关联查询条件
                $joinQuery = $this->buildJoinQueryForFirst($params, $joinConfig);
                if (empty($joinQuery)) {
                    \think\facade\Log::info("关联表 {$joinTable} 没有查询条件");
                    continue;
                }
                // 2. 获取外键字段名
                $foreignKey = $joinConfig['foreign_key'];
                
                // 3. 使用聚合查询直接去重获取主表ID
                $esParams = [
                    'index' => $joinConfig['es_index'] ?? $joinTable,
                    'body' => [
                        'query' => $joinQuery,
                        'size' => 0, // 不需要返回文档，只需要聚合结果
                        'aggs' => [
                            'distinct_main_ids' => [
                                'terms' => [
                                    'field' => $foreignKey, // 使用keyword字段进行聚合
                                    'size' => $joinConfig['limit_first'] ?? 1000, // 最多返回的不重复ID数量
                                    'order' => [
                                        '_key' => 'desc' // 按ID倒序排列
                                    ]
                                ]
                            ]
                        ],
                        'track_total_hits' => true
                    ]
                ];
                
                $response = $this->esClient->search($esParams);
                // 4. 从聚合结果中提取主表ID
                $buckets = $response['aggregations']['distinct_main_ids']['buckets'] ?? [];
                foreach ($buckets as $bucket) {
                    $mainId = $bucket['key'] ?? null;
                    if ($mainId && !in_array($mainId, $mainIds)) {
                        $mainIds[] = $mainId;
                    }
                }
                \think\facade\Log::info("从关联表 {$joinTable} 聚合提取到主表ID数量: " . count($mainIds));
            } catch (\Exception $e) {
                return false;
            }
            
            // 只要找到一个符合条件的关联表就返回
            if (!empty($mainIds)) {
                break;
            }
        }
        
        // 已经是去重的，直接返回
        return array_filter($mainIds);
    }
    
    /**
     * 构建关联查询条件（用于首次查询）
     * @param array $params 查询参数
     * @param array $joinConfig 关联配置
     * @return array|null
     */
    private function buildJoinQueryForFirst($params, $joinConfig)
    {
        $must = [];
        // 1. 基本条件
        if (!empty($joinConfig['conditions'])) {
            foreach ($joinConfig['conditions'] as $condition) {
                if (isset($condition['field'], $condition['value'])) {
                    $field = $condition['field'];
                    $value = $condition['value'];
                    $operator = $condition['operator'] ?? '=';
                    
                    $query = $this->buildConditionQuery($field, $operator, $value);
                    if ($query) {
                        $must[] = $query;
                    }
                }
            }
        }
        
        // 2. 关联表特定的搜索条件
        if (!empty($joinConfig['search_conditions'])) {
            foreach ($joinConfig['search_conditions'] as $condition) {
                if (isset($condition['field'], $condition['value'])) {
                    $field = $condition['field'];
                    $value = $condition['value'];
                    $operator = $condition['operator'] ?? '=';
                    
                    $query = $this->buildConditionQuery($field, $operator, $value);
                    if ($query) {
                        $must[] = $query;
                    }
                }
            }
        }
        
        // 3. 如果没有条件，返回match_all
        if (empty($must)) {
            return ['match_all' => new \stdClass()];
        }
        return ['bool' => ['must' => $must]];
    }
    
    /**
     * 查询关联表详细信息
     * @param array $mainIds 主表ID数组
     * @param array $options 查询选项
     * @return array
     */
    private function queryJoinTablesDetails($mainIds, $options)
    {
        $results = [];
        
        foreach ($options['join_tables'] as $joinTable => $joinConfig) {
            if (empty($mainIds)) {
                $results[$joinTable] = [];
                continue;
            }
            
            try {
                // 1. 构建关联查询条件
                $joinQuery = $this->buildJoinQueryForDetails($mainIds, $joinConfig);
                // 2. 构建排序
                $joinSort = $this->buildJoinSort($joinConfig);
                
                // 3. 执行关联查询
                $esParams = [
                    'index' => $joinConfig['es_index'] ?? $joinTable,
                    'body' => [
                        'query' => $joinQuery,
                        'sort' => $joinSort,
                        'size' => $joinConfig['limit'] ?? 1000
                    ]
                ];
                $response = $this->esClient->search($esParams);
                $hits = $response['hits']['hits'] ?? [];
                // 4. 处理查询结果
                $joinData = $this->processJoinHits($hits, $joinConfig);
                
                // 5. 按关联键分组
                $groupedData = [];
                $foreignKey = $joinConfig['foreign_key'];
                
                foreach ($joinData as $item) {
                    if (isset($item[$foreignKey])) {
                        $key = $item[$foreignKey];
                        
                        if ($joinConfig['multiple'] ?? false) {
                            // 一对多关系
                            if (!isset($groupedData[$key])) {
                                $groupedData[$key] = [];
                            }
                            $groupedData[$key][] = $item;
                        } else {
                            // 一对一关系
                            $groupedData[$key] = $item;
                        }
                    }
                }
                
                $results[$joinTable] = $groupedData;
                
            } catch (\Exception $e) {
                \think\facade\Log::error("关联表 {$joinTable} 详情查询失败: " . $e->getMessage());
                $results[$joinTable] = [];
            }
        }
        
        return $results;
    }
    
    /**
     * 构建关联查询条件（用于详情查询）
     * @param array $mainIds 主表ID数组
     * @param array $joinConfig 关联配置
     * @return array
     */
    private function buildJoinQueryForDetails($mainIds, $joinConfig)
    {
        $must = [];
        
        // 1. 添加关联条件
        $foreignKey = $joinConfig['foreign_key'];
        if (count($mainIds) > 1) {
            $must[] = ['terms' => [$foreignKey => $mainIds]];
        } else {
            $must[] = ['term' => [$foreignKey => $mainIds[0]]];
        }
        //        $must[] = ['terms' => [$foreignKey => implode(',',$mainIds)]];
        // 2. 添加额外的过滤条件
        if (!empty($joinConfig['conditions'])) {
            foreach ($joinConfig['conditions'] as $condition) {
                if (isset($condition['field'], $condition['value'])) {
                    $field = $condition['field'];
                    $value = $condition['value'];
                    $operator = $condition['operator'] ?? '=';
                    
                    $query = $this->buildConditionQuery($field, $operator, $value);
                    if ($query) {
                        $must[] = $query;
                    }
                }
            }
        }
        
        return ['bool' => ['must' => $must]];
    }
    
    /**
     * 旧版多表联查搜索方法（保持兼容）
     * @param array $params 查询参数
     * @param array $options 选项
     * @return array
     */
    public function searchWithJoins($params, $options = [])
    {
        if (!$this->esClient) {
            return [
                'status' => 500,
                'msg' => 'ES连接失败',
                'data' => []
            ];
        }
        
        $startTime = microtime(true);
        
        try {
            // 1. 首先查询主表数据
            $mainOptions = $options;
            $mainOptions['with_relations'] = false;
            $mainOptions['limit'] = $options['limit'];
            $mainOptions['page'] = $options['page'];
            
            $mainResult = $this->search($params, $mainOptions);
            
            if ($mainResult['status'] !== 200 || empty($mainResult['data']['data'])) {
                return $mainResult;
            }
            
            $mainData = $mainResult['data']['data'];
            $mainIds = array_column($mainData, $options['primary_key']);
            
            // 2. 查询所有关联表
            $joinResults = $this->queryJoinTablesDetails($mainIds, $options);
            
            // 3. 将关联数据合并到主表数据中
            $this->mergeJoinResults($mainData, $joinResults, $options);
            
            // 4. 处理字段别名
            $this->processFieldAliases($mainData, $options);
            
            // 5. 构建返回结果
            $result = $this->buildJoinResponse($mainData, $mainResult['data'], $options);
            
            $queryTime = microtime(true) - $startTime;
            
            return [
                'status' => 200,
                'data' => $result,
                'query_time' => round($queryTime, 3),
                'index' => $this->esConfig['index'],
                'query_info' => [
                    'query_type' => 'elasticsearch_with_joins',
                    'main_table' => $options['main_table'],
                    'join_tables' => array_keys($options['join_tables']),
                    'main_records' => count($mainData)
                ]
            ];
            
        } catch (\Exception $e) {
            \think\facade\Log::error('多表联查异常: ' . $e->getMessage());
            return [
                'status' => 500,
                'msg' => '多表联查失败: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * 构建空的响应数据
     * @param array $options 选项
     * @return array
     */
    private function buildEmptyResponse($options)
    {
        return [
            'data' => [],
            'total' => 0,
            'per_page' => $options['limit'] ?? 20,
            'current_page' => $options['page'] ?? 1,
            'last_page' => 1,
            'has_prev' => false,
            'has_next' => false,
            'first_id' => 0,
            'last_id' => 0,
            'first_order_value' => 0,
            'last_order_value' => 0,
            'order_field' => $options['order_field'] ?? 'id',
            'order_direction' => $options['order_direction'] ?? 'desc',
            'search_engine' => 'elasticsearch'
        ];
    }
    
    /**
     * 构建关联查询的响应数据
     * @param array $mainData 主表数据
     * @param array $originalResponse 原始响应数据
     * @param array $options 选项
     * @return array
     */
    private function buildJoinResponse($mainData, $originalResponse, $options)
    {
        return [
            'data' => $mainData,
            'total' => $originalResponse['total'] ?? count($mainData),
            'per_page' => $options['limit'],
            'current_page' => $options['page'],
            'last_page' => $originalResponse['last_page'] ?? 1,
            'has_prev' => $originalResponse['has_prev'] ?? false,
            'has_next' => $originalResponse['has_next'] ?? false,
            'first_id' => $originalResponse['first_id'] ?? 0,
            'last_id' => $originalResponse['last_id'] ?? 0,
            'first_order_value' => $originalResponse['first_order_value'] ?? 0,
            'last_order_value' => $originalResponse['last_order_value'] ?? 0,
            'order_field' => $options['order_field'],
            'order_direction' => $options['order_direction'],
            'search_engine' => 'elasticsearch'
        ];
    }
    
    /**
     * 构建ES查询（支持WHERE OR逻辑）
     * @param array $params 查询参数
     * @param array $options 选项
     * @return array
     */
    private function buildEsQuery($params, $options)
    {
        $must = [];
        $filter = [];
        $should = []; // 新增：用于WHERE OR逻辑
        $must_not = []; // 新增：用于WHERE NOT逻辑
        
        // 1. 处理WHERE OR条件（通过选项传入）
        $orConditions = $options['or_conditions'] ?? [];
        if (!empty($orConditions)) {
            foreach ($orConditions as $orCondition) {
                if (is_array($orCondition) && isset($orCondition['field'], $orCondition['value'])) {
                    $field = $orCondition['field'];
                    $value = $orCondition['value'];
                    
                    if (!empty($value) && trim($value) !== '') {
                        // 处理不同的操作符
                        $operator = $orCondition['operator'] ?? '=';
                        
                        switch ($operator) {
                            case '=':
                            case 'eq':
                                $should[] = ['term' => [$field => $value]];
                                break;
                            case 'like':
                                $should[] = [
                                    'wildcard' => [
                                        $field . '_fragments' => '*' . $this->escapeWildcard($value) . '*'
                                    ]
                                ];
                                break;
                            case 'in':
                                if (is_string($value) && strpos($value, ',') !== false) {
                                    $values = array_filter(array_map('trim', explode(',', $value)));
                                    if (!empty($values)) {
                                        $should[] = ['terms' => [$field => $values]];
                                    }
                                } elseif (is_array($value)) {
                                    $should[] = ['terms' => [$field => $value]];
                                }
                                break;
                            case '>':
                            case 'gte':
                                $should[] = ['range' => [$field => ['gte' => $value]]];
                                break;
                            case '<':
                            case 'lte':
                                $should[] = ['range' => [$field => ['lte' => $value]]];
                                break;
                            case 'between':
                                if (is_array($value) && count($value) == 2) {
                                    $should[] = [
                                        'range' => [
                                            $field => [
                                                'gte' => $value[0] ?? null,
                                                'lte' => $value[1] ?? null
                                            ]
                                        ]
                                    ];
                                }
                                break;
                        }
                    }
                }
            }
        }
        
        // 2. 处理LIKE查询条件（使用.keyword字段和通配符查询）
        $likeFields = $options['like_fields'] ?? [];
        foreach ($likeFields as $field) {
            if (isset($params[$field]) && !empty(trim($params[$field]))) {
                $keyword = trim($params[$field]);
                $must[] = [
                    'wildcard' => [
                        $field . '_fragments' => '*' . $this->escapeWildcard($keyword) . '*'
                    ]
                ];
            }
        }
        
        // 3. 处理等值查询条件（兼容数组范围、数组列表、字符串、数字）
        $eqConditions = $options['eq_conditions'] ?? [];
        foreach ($eqConditions as $key => $value) {
            if (is_array($value)) {
                $hasRangeShape = array_key_exists(0, $value) || array_key_exists(1, $value);
                if ($hasRangeShape) {
                    $startValue = $value[0] ?? null;
                    $endValue = $value[1] ?? null;
                    $range = [];
                    if ($startValue !== null && !(is_string($startValue) && trim($startValue) === '')) {
                        $range['gte'] = is_string($startValue) ? trim($startValue) : $startValue;
                    }
                    if ($endValue !== null && !(is_string($endValue) && trim($endValue) === '')) {
                        $range['lte'] = is_string($endValue) ? trim($endValue) : $endValue;
                    }
                    if (!empty($range)) {
                        $filter[] = ['range' => [$key => $range]];
                    }
                    continue;
                }
                
                $values = [];
                foreach ($value as $item) {
                    if (is_string($item)) {
                        $item = trim($item);
                    }
                    if ($item === '' || $item === null) {
                        continue;
                    }
                    $values[] = $item;
                }
                if (!empty($values)) {
                    $filter[] = ['terms' => [$key => array_values($values)]];
                }
                continue;
            }
            
            if ($value === null) {
                continue;
            }
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
                if (strpos($value, ',') !== false) {
                    $values = array_filter(array_map('trim', explode(',', $value)), function ($item) {
                        return $item !== '';
                    });
                    if (!empty($values)) {
                        $filter[] = ['terms' => [$key => array_values($values)]];
                    }
                    continue;
                }
            }
            
            $filter[] = ['term' => [$key => $value]];
        }
        
        // 4. 处理权限条件
        $whereConditions = $options['where_conditions'] ?? [];
        if (!empty($whereConditions)) {
            foreach ($whereConditions as $condition) {
                if (!isset($condition[0], $condition[1])) {
                    continue;
                }
                
                $field = $condition[0];
                $operator = $condition[1];
                $value = $condition[2] ?? null;
                $query = $this->buildConditionQuery($field, $operator, $value);
                
                if ($query) {
                    $filter[] = $query;
                }
            }
        }
        
        // 4.1 处理WHERE OR分组条件
        $whereOrGroups = $options['where_or_groups'] ?? [];
        if (!empty($whereOrGroups)) {
            foreach ($whereOrGroups as $groupConditions) {
                if (!is_array($groupConditions) || empty($groupConditions)) {
                    continue;
                }
                
                $groupShould = [];
                foreach ($groupConditions as $condition) {
                    if (!is_array($condition) || !isset($condition['field'], $condition['operator'])) {
                        continue;
                    }
                    
                    $query = $this->buildConditionQuery(
                        $condition['field'],
                        $condition['operator'],
                        $condition['value'] ?? null
                    );
                    
                    if ($query) {
                        $groupShould[] = $query;
                    }
                }
                
                if (empty($groupShould)) {
                    continue;
                }
                
                $filter[] = count($groupShould) === 1
                    ? $groupShould[0]
                    : [
                        'bool' => [
                            'should' => $groupShould,
                            'minimum_should_match' => 1,
                        ]
                    ];
            }
        }
        
        // 5. 处理范围查询（时间范围和其他范围查询通用）
        // 已在eq_conditions标准化过的字段，不再从原始params重复构建，避免同字段类型冲突（如时间戳/日期字符串）
        $skipParamFields = array_flip(['limit', 'page', '_t', 'sort', 'order', 'last_id', 'direction']);
        $normalizedConditionFields = array_flip(array_keys($eqConditions));
        foreach ($params as $field => $value) {
            if (isset($skipParamFields[$field]) || isset($normalizedConditionFields[$field])) {
                continue;
            }
            if (empty($value)) {
                continue;
            }
            
            // 检查是否是数组且长度为2（表示范围查询）
            if (is_array($value) && count($value) == 2) {
                $startValue = $value[0];
                $endValue = $value[1];
                
                // 如果两个值都是空，跳过
                if ($startValue === '' && $endValue === '') {
                    continue;
                }
                
                // 构建range查询条件
                $rangeQuery = ['range' => [$field => []]];
                
                // 处理起始值
                if ($startValue !== '' && $startValue !== null) {
                    $rangeQuery['range'][$field]['gte'] = $startValue;
                }
                
                // 处理结束值
                if ($endValue !== '' && $endValue !== null) {
                    $rangeQuery['range'][$field]['lte'] = $endValue;
                }
                
                // 如果range条件为空，跳过
                if (empty($rangeQuery['range'][$field])) {
                    continue;
                }
                
                // 根据字段类型决定使用filter还是must
                if (is_numeric($startValue)) {
                    $filter[] = $rangeQuery;
                } else {
                    $must[] = $rangeQuery;
                }
            }
            
            // 6. 处理逗号分隔的IN查询
            if (is_string($value) && strpos($value, ',') !== false) {
                $values = array_filter(array_map('trim', explode(',', $value)));
                if (!empty($values)) {
                    $filter[] = ['terms' => [$field => $values]];
                }
            }
            
            // 7. 处理WHERE NOT IN查询（通过参数名判断）
            if (strpos($field, '_not_in') !== false || strpos($field, 'exclude_') === 0) {
                $realField = str_replace(['_not_in', 'exclude_'], '', $field);
                
                if (is_string($value) && strpos($value, ',') !== false) {
                    $excludeValues = array_filter(array_map('trim', explode(',', $value)));
                    if (!empty($excludeValues)) {
                        $must_not[] = ['terms' => [$realField => $excludeValues]];
                    }
                } elseif (is_array($value)) {
                    $must_not[] = ['terms' => [$realField => $value]];
                }
            }
            
            // 8. 处理FIND_IN_SET查询（通过参数名判断）
            if (strpos($field, 'find_in_') === 0) {
                $realField = substr($field, 8);
                
                $must[] = [
                    'bool' => [
                        'should' => [
                            ['term' => [$realField => $value]],
                            ['wildcard' => [$realField . '.keyword' => $value . ',*']],
                            ['wildcard' => [$realField . '.keyword' => '*,' . $value]],
                            ['wildcard' => [$realField . '.keyword' => '*,' . $value . ',*']]
                        ],
                        'minimum_should_match' => 1
                    ]
                ];
            }
        }
        
        // 9. 构建完整的bool查询
        $boolQuery = ['bool' => []];
        
        if (!empty($must)) {
            $boolQuery['bool']['must'] = $must;
        }
        
        if (!empty($filter)) {
            $boolQuery['bool']['filter'] = $filter;
        }
        
        // 10. 处理WHERE OR逻辑（使用should）
        if (!empty($should)) {
            // 如果有多个OR条件，使用bool查询包装
            if (count($should) > 1) {
                $boolQuery['bool']['should'] = $should;
                // 至少匹配一个OR条件
                $boolQuery['bool']['minimum_should_match'] = 1;
            } else {
                // 只有一个OR条件，直接添加到must中
                $boolQuery['bool']['must'][] = $should[0];
            }
            
            // 如果有must条件，需要调整查询结构
            if (!empty($must)) {
                // 重新构建查询：must条件和(should条件)组合
                $boolQuery = [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'must' => $must,
                                    'should' => $should,
                                    'minimum_should_match' => 1
                                ]
                            ]
                        ]
                    ]
                ];
                
                if (!empty($filter)) {
                    $boolQuery['bool']['must'][0]['bool']['filter'] = $filter;
                }
            }
        }
        
        // 11. 处理WHERE NOT逻辑
        if (!empty($must_not)) {
            $boolQuery['bool']['must_not'] = $must_not;
        }
        
        // 12. 添加删除标记过滤
        $deletedField = $options['deleted_field'];
        $deletedValue = $options['deleted_value'];
        if ($deletedField && isset($deletedValue)) {
            if (isset($boolQuery['bool']['filter'])) {
                $boolQuery['bool']['filter'][] = ['term' => [$deletedField => $deletedValue]];
            } else {
                $boolQuery['bool']['filter'] = [['term' => [$deletedField => $deletedValue]]];
            }
        }
        
        // 13. 如果没有查询条件，返回match_all
        if (empty($must) && empty($filter) && empty($should) && empty($must_not)) {
            return ['match_all' => new \stdClass()];
        }
        return $boolQuery;
    }
    
    /**
     * 处理复杂的WHERE OR组合查询（高级版本）
     * @param array $orGroups OR条件分组
     * @return array
     */
    private function buildComplexOrQuery($orGroups)
    {
        $shouldQueries = [];
        
        foreach ($orGroups as $group) {
            if (!is_array($group) || empty($group)) {
                continue;
            }
            $groupShould = [];
            foreach ($group as $condition) {
                if (!isset($condition['field'], $condition['value']) || empty($condition['value'])) {
                    continue;
                }
                $field = $condition['field'];
                $value = $condition['value'];
                $operator = $condition['operator'] ?? '=';
                
                $query = $this->buildConditionQuery($field, $operator, $value);
                if ($query) {
                    $groupShould[] = $query;
                }
            }
            
            if (!empty($groupShould)) {
                if (count($groupShould) === 1) {
                    $shouldQueries[] = $groupShould[0];
                } else {
                    $shouldQueries[] = [
                        'bool' => [
                            'should' => $groupShould,
                            'minimum_should_match' => 1
                        ]
                    ];
                }
            }
        }
        
        return $shouldQueries;
    }
    
    /**
     * 构建单个条件查询
     * @param string $field 字段名
     * @param string $operator 操作符
     * @param mixed  $value 值
     * @return array|null
     */
    private function buildConditionQuery($field, $operator, $value)
    {
        $operator = strtolower(trim((string)$operator));
        
        switch ($operator) {
            case '=':
            case 'eq':
                return $this->buildEqualsCondition($field, $value);
            
            case '!=':
            case '<>':
            case 'neq':
                return $this->buildNotEqualsCondition($field, $value);
            
            case 'null':
                return $this->buildMissingCondition($field);
            
            case 'not null':
                return $this->buildExistsCondition($field);
            
            case 'like':
                return [
                    'wildcard' => [
                        $field . '_fragments' => '*' . $this->escapeWildcard($value) . '*'
                    ]
                ];
            
            case 'not like':
                return [
                    'bool' => [
                        'must_not' => [[
                            'wildcard' => [
                                $field . '_fragments' => '*' . $this->escapeWildcard($value) . '*'
                            ]
                        ]]
                    ]
                ];
            
            case 'in':
                $values = is_array($value) ? $value : array_filter(explode(',', $value));
                if (!empty($values)) {
                    return ['terms' => [$field => $values]];
                }
                break;
            
            case 'not in':
                $values = is_array($value) ? $value : array_filter(explode(',', $value));
                if (!empty($values)) {
                    return ['bool' => ['must_not' => [['terms' => [$field => $values]]]]];
                }
                break;
            
            case '>':
            case 'gt':
                return ['range' => [$field => ['gt' => $value]]];
            
            case '>=':
            case 'gte':
                return ['range' => [$field => ['gte' => $value]]];
            
            case '<':
            case 'lt':
                return ['range' => [$field => ['lt' => $value]]];
            
            case '<=':
            case 'lte':
                return ['range' => [$field => ['lte' => $value]]];
            
            case 'between':
                if (is_array($value) && count($value) == 2) {
                    return [
                        'range' => [
                            $field => [
                                'gte' => $value[0],
                                'lte' => $value[1]
                            ]
                        ]
                    ];
                }
                break;
        }
        
        return null;
    }
    
    /**
     * 标准化条件值
     * @param mixed $value
     * @return mixed
     */
    private function normalizeConditionValue($value)
    {
        return is_string($value) ? trim($value) : $value;
    }
    
    /**
     * 判断是否是 null 关键字
     * @param mixed $value
     * @return bool
     */
    private function isNullKeyword($value): bool
    {
        return $value === null || (is_string($value) && strtolower(trim($value)) === 'null');
    }
    
    /**
     * 判断是否是 not null 关键字
     * @param mixed $value
     * @return bool
     */
    private function isNotNullKeyword($value): bool
    {
        return is_string($value) && strtolower(trim($value)) === 'not null';
    }
    
    /**
     * 字段存在查询
     * @param string $field
     * @return array
     */
    private function buildExistsCondition(string $field): array
    {
        return [
            'exists' => [
                'field' => $field
            ]
        ];
    }
    
    /**
     * 字段不存在查询
     * 当前 ES 写入链路会跳过 null / 空字符串，所以缺失字段可视为 null 或 ''。
     * @param string $field
     * @return array
     */
    private function buildMissingCondition(string $field): array
    {
        return [
            'bool' => [
                'must_not' => [
                    [
                        'exists' => [
                            'field' => $field
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 构建等于条件
     * @param string $field
     * @param mixed  $value
     * @return array
     */
    private function buildEqualsCondition(string $field, $value): array
    {
        $value = $this->normalizeConditionValue($value);
        
        if ($this->isNotNullKeyword($value)) {
            return $this->buildExistsCondition($field);
        }
        
        if ($this->isNullKeyword($value) || $value === '') {
            return $this->buildMissingCondition($field);
        }
        
        return ['term' => [$field => $value]];
    }
    
    /**
     * 构建不等于条件
     * 当前 ES 写入链路会跳过空字符串，所以 field <> '' 可等价为字段存在。
     * @param string $field
     * @param mixed  $value
     * @return array
     */
    private function buildNotEqualsCondition(string $field, $value): array
    {
        $value = $this->normalizeConditionValue($value);
        
        if ($this->isNotNullKeyword($value)) {
            return $this->buildMissingCondition($field);
        }
        
        if ($this->isNullKeyword($value) || $value === '') {
            return $this->buildExistsCondition($field);
        }
        
        return [
            'bool' => [
                'must' => [
                    $this->buildExistsCondition($field)
                ],
                'must_not' => [
                    ['term' => [$field => $value]]
                ]
            ]
        ];
    }
    
    /**
     * 转义通配符特殊字符
     * @param string $keyword
     * @return string
     */
    private function escapeWildcard($keyword)
    {
        $specialChars = ['\\', '+', '-', '=', '&&', '||', '>', '<', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '/'];
        foreach ($specialChars as $char) {
            $keyword = str_replace($char, '\\' . $char, $keyword);
        }
        return $keyword;
    }
    
    /**
     * 构建排序（始终返回与 search_after 值数量一致的排序字段）
     * @param string $orderField 排序字段
     * @param string $orderDir 排序方向
     * @param string $primaryKey 主键
     * @return array
     */
    private function buildSort($orderField, $orderDir, $primaryKey)
    {
        $sort = [];
        
        // 判断排序字段是否就是主键
        if ($orderField === $primaryKey) {
            // 如果排序字段就是主键，只用一个排序字段
            $sort[] = [$primaryKey => ['order' => $orderDir]];
        } else {
            // 如果排序字段不是主键，使用两个排序字段
            $sort[] = [$orderField => ['order' => $orderDir]];
            $sort[] = [$primaryKey => ['order' => $orderDir]];
        }
        
        return $sort;
    }
    
    /**
     * 构建反转排序（用于上一页查询）
     * @param string $orderField 排序字段
     * @param string $orderDir 原排序方向
     * @param string $primaryKey 主键
     * @return array
     */
    private function buildReversedSort($orderField, $orderDir, $primaryKey)
    {
        $sort = [];
        $reversedDir = $orderDir === 'desc' ? 'asc' : 'desc';
        
        // 判断排序字段是否就是主键
        if ($orderField === $primaryKey) {
            // 如果排序字段就是主键，只用一个排序字段
            $sort[] = [$primaryKey => ['order' => $reversedDir]];
        } else {
            // 如果排序字段不是主键，使用两个排序字段
            $sort[] = [$orderField => ['order' => $reversedDir]];
            $sort[] = [$primaryKey => ['order' => $reversedDir]];
        }
        
        return $sort;
    }
    
    /**
     * 获取指定记录的排序值（用于 search_after）
     * @param mixed  $recordId 记录ID
     * @param string $orderField 排序字段
     * @param string $orderDir 排序方向
     * @param string $primaryKey 主键
     * @return array
     */
    private function getRecordSortValues($recordId, $orderField, $orderDir, $primaryKey)
    {
        try {
            // 构建查询条件，获取指定记录
            $searchParams = [
                'index' => $this->esConfig['index'],
                'body' => [
                    'query' => [
                        'term' => [$primaryKey => $recordId]
                    ],
                    'size' => 1,
                    '_source' => [$orderField, $primaryKey]
                ]
            ];
            
            $response = $this->esClient->search($searchParams);
            $hits = $response['hits']['hits'] ?? [];
            
            if (empty($hits)) {
                return [];
            }
            
            $source = $hits[0]['_source'] ?? [];
            $sortValues = [];
            
            // 判断排序字段是否就是主键
            if ($orderField === $primaryKey) {
                // 如果排序字段就是主键，只返回主键值
                $sortValues[] = $recordId;
            } else {
                // 如果排序字段不是主键，返回排序字段值 + 主键值
                if (isset($source[$orderField])) {
                    $fieldValue = $source[$orderField];
                    if (is_array($fieldValue)) {
                        $sortValues[] = !empty($fieldValue) ? reset($fieldValue) : null;
                    } else {
                        $sortValues[] = $fieldValue;
                    }
                } else {
                    $sortValues[] = null;
                }
                $sortValues[] = $recordId;
            }
            
            return $sortValues;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('获取记录排序值失败: ' . $e->getMessage());
            return [];
        }
    }
    
    
    /**
     * 获取search_after值
     * @param mixed  $lastId 最后ID
     * @param string $orderField 排序字段
     * @param string $orderDir 排序方向
     * @param string $primaryKey 主键
     * @return array
     */
    private function getSearchAfterValues($lastId, $orderField, $orderDir, $primaryKey)
    {
        try {
            // 使用 search 而不是 get，避免文档不存在时报错
            $searchParams = [
                'index' => $this->esConfig['index'],
                'body' => [
                    'query' => [
                        'term' => [$primaryKey => $lastId]
                    ],
                    'size' => 1,
                    '_source' => [$orderField, $primaryKey]
                ]
            ];
            
            $response = $this->esClient->search($searchParams);
            $hits = $response['hits']['hits'] ?? [];
            
            if (empty($hits)) {
                return [];
            }
            
            $source = $hits[0]['_source'] ?? [];
            $searchAfter = [];
            
            // 获取排序字段的值
            if ($orderField && $orderField !== $primaryKey && isset($source[$orderField])) {
                $fieldValue = $source[$orderField];
                if (is_array($fieldValue)) {
                    $searchAfter[] = $fieldValue[0] ?? reset($fieldValue);
                } else {
                    $searchAfter[] = $fieldValue;
                }
            } elseif ($orderField === $primaryKey) {
                // 如果排序字段就是主键，直接使用主键值
                $searchAfter[] = $lastId;
            }
            
            // 添加主键作为第二个排序字段
            $searchAfter[] = $lastId;
            
            return $searchAfter;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('获取search_after值失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 处理查询结果
     * @param array $hits ES返回的hits
     * @return array
     */
    private function processHits($hits)
    {
        $list = [];
        foreach ($hits as $hit) {
            $data = $hit['_source'];
            $data['es_score'] = $hit['_score'] ?? 0;
            $data['es_id'] = $hit['_id'] ?? '';
            $list[] = $data;
        }
        return $list;
    }
    
    /**
     * 处理关联查询结果
     * @param array $hits ES返回的hits
     * @param array $joinConfig 关联配置
     * @return array
     */
    private function processJoinHits($hits, $joinConfig)
    {
        $list = [];
        foreach ($hits as $hit) {
            $data = $hit['_source'];
            
            // 应用字段映射
            if (!empty($joinConfig['field_mapping'])) {
                $data = $this->applyFieldMapping($data, $joinConfig['field_mapping']);
            }
            
            // 添加ES元数据
            $data['_es_score'] = $hit['_score'] ?? 0;
            $data['_es_id'] = $hit['_id'] ?? '';
            
            $list[] = $data;
        }
        
        return $list;
    }
    
    /**
     * 应用字段映射
     * @param array $data 原始数据
     * @param array $mapping 字段映射
     * @return array
     */
    private function applyFieldMapping($data, $mapping)
    {
        $mappedData = [];
        foreach ($data as $field => $value) {
            if (isset($mapping[$field])) {
                // 如果有映射配置，使用映射后的字段名
                $mappedField = $mapping[$field];
                $mappedData[$mappedField] = $value;
            } else {
                // 如果没有映射，保持原字段名
                $mappedData[$field] = $value;
            }
        }
        
        return $mappedData;
    }
    
    /**
     * 构建响应数据
     * @param array $list 数据列表
     * @param int   $total 总数
     * @param array $options 选项
     * @return array
     */
    private function buildResponse($list, $total, $options)
    {
        $primaryKey = $options['primary_key'];
        $orderField = $options['order_field'];
        
        $firstId = !empty($list) ? ($this->getFieldValue($list[0], $primaryKey) ?? 0) : 0;
        $lastId = !empty($list) ? ($this->getFieldValue(end($list), $primaryKey) ?? 0) : 0;
        $firstOrderValue = !empty($list) ? ($this->getFieldValue($list[0], $orderField) ?? 0) : 0;
        $lastOrderValue = !empty($list) ? ($this->getFieldValue(end($list), $orderField) ?? 0) : 0;
        
        // 修复：has_prev 的判断不应该基于页码，而应该基于是否有上一页游标
        // 如果有 last_id 且 direction 是 next，说明不是第一页，应该有上一页
        // 如果 direction 是 prev，说明正在向前翻页，也应该有上一页（除非已经到了第一页）
        $hasPrev = false;
        if ($options['last_id'] > 0) {
            // 有游标，说明不是第一页
            $hasPrev = true;
        } elseif ($options['direction'] === 'prev' && !empty($list)) {
            // 如果正在向前翻页且返回了数据，说明还有上一页
            $hasPrev = true;
        }
        
        // 计算是否有下一页
        $hasNext = false;
        if (count($list) == $options['limit']) {
            // 如果返回的数据量等于限制数，可能还有下一页
            $hasNext = true;
        }
        
        // 计算总页数（仅用于显示，游标分页中可能不准确）
        $lastPage = $total > 0 ? ceil($total / $options['limit']) : 1;
        
        return [
            'data' => $list,
            'total' => $total,
            'per_page' => $options['limit'],
            'current_page' => $options['page'],
            'last_page' => $lastPage,
            'has_prev' => $hasPrev,      // 修复：使用正确的上一页判断逻辑
            'has_next' => $hasNext,       // 修复：使用正确的下一页判断逻辑
            'first_id' => $firstId,
            'last_id' => $lastId,
            'first_order_value' => $firstOrderValue,
            'last_order_value' => $lastOrderValue,
            'order_field' => $orderField,
            'order_direction' => $options['order_direction']
        ];
    }
    
    /**
     * 获取字段值（处理数组格式）
     * @param array  $data 数据
     * @param string $field 字段名
     * @return mixed
     */
    private function getFieldValue($data, $field)
    {
        if (!isset($data[$field])) {
            return null;
        }
        
        $value = $data[$field];
        if (is_array($value)) {
            return !empty($value) ? reset($value) : null;
        }
        
        return $value;
    }
    
    /**
     * 合并关联数据到主表数据
     * @param array &$mainData 主表数据
     * @param array  $joinResults 关联查询结果
     * @param array  $options 查询选项
     */
    private function mergeJoinResults(&$mainData, $joinResults, $options)
    {
        $mainKeyField = $options['primary_key'];
        
        foreach ($mainData as &$mainItem) {
            $mainId = $mainItem[$mainKeyField] ?? null;
            
            if (!$mainId) {
                continue;
            }
            
            foreach ($options['join_tables'] as $joinTable => $joinConfig) {
                $joinData = $joinResults[$joinTable][$mainId] ?? null;
                
                if ($joinData !== null) {
                    // 获取关联字段名
                    $joinField = $this->getJoinFieldName($joinTable, $joinConfig, $options);
                    
                    // 根据关系类型处理数据
                    if ($joinConfig['multiple'] ?? false) {
                        // 一对多：将数据放入数组
                        $mainItem[$joinField] = is_array($joinData) ? $joinData : [$joinData];
                    } else {
                        // 一对一：直接合并到主记录
                        if (is_array($joinData)) {
                            foreach ($joinData as $field => $value) {
                                $mainItem[$field] = $value;
                            }
                        } else {
                            $mainItem[$joinField] = $joinData;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * 获取关联字段名称
     * @param string $joinTable 关联表名
     * @param array  $joinConfig 关联配置
     * @param array  $options 查询选项
     * @return string
     */
    private function getJoinFieldName($joinTable, $joinConfig, $options)
    {
        // 1. 首先检查是否有自定义字段名
        if (!empty($joinConfig['as_field'])) {
            return $joinConfig['as_field'];
        }
        
        // 2. 检查字段映射配置
        foreach ($options['join_fields'] as $field => $mapping) {
            if ($mapping['table'] === $joinTable) {
                return $field;
            }
        }
        
        // 3. 默认使用表名作为字段名
        return $joinTable;
    }
    
    /**
     * 构建关联查询排序
     * @param array $joinConfig 关联配置
     * @return array
     */
    private function buildJoinSort($joinConfig)
    {
        $sort = [];
        
        // 添加默认排序
        if (!empty($joinConfig['order_field'])) {
            $sort[] = [
                $joinConfig['order_field'] => [
                    'order' => $joinConfig['order_direction'] ?? 'asc'
                ]
            ];
        }
        return $sort;
    }
    
    /**
     * 处理字段别名
     * @param array &$data 数据
     * @param array  $options 查询选项
     */
    private function processFieldAliases(&$data, $options)
    {
        if (empty($options['join_fields'])) {
            return;
        }
        
        foreach ($data as &$item) {
            foreach ($options['join_fields'] as $field => $mapping) {
                if (isset($item[$field]) && !empty($mapping['alias'])) {
                    // 如果有别名配置，创建别名字段
                    $item[$mapping['alias']] = $item[$field];
                }
            }
        }
    }
    
    /**
     * 添加文档到ES（根据传入的字段动态处理）
     * @param array  $data 数据
     * @param string $idField ID字段名（由控制器传入）
     * @param array  $fieldsMapping 字段映射配置（由控制器传入）
     * @return bool
     */
    public function add($data, $idField = 'id', $fieldsMapping = [])
    {
        if (!$this->esClient || empty($data)) {
            return false;
        }
        
        try {
            // 确保索引存在
            $this->ensureIndexExists($data, $fieldsMapping);
            
            $id = $data[$idField] ?? null;
            
            if (!$id) {
                throw new \Exception('缺少主键ID字段');
            }
            
            // 根据字段映射将数据转换为ES需要的格式
            $esData = $this->convertToEsFormat($data, $fieldsMapping);
            
            $params = [
                'index' => $this->esConfig['index'],
                'id' => $id,
                'body' => $esData
            ];
            
            $response = $this->esClient->index($params);
            
            // 刷新索引，使文档立即可搜索
            $this->esClient->indices()->refresh(['index' => $this->esConfig['index']]);
            
            return true;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('ES添加文档失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 批量添加文档到ES
     * @param array  $dataList 数据列表
     * @param string $idField ID字段名（由控制器传入）
     * @param array  $fieldsMapping 字段映射配置（由控制器传入）
     * @return bool|int
     */
    public function addAll($dataList, $idField = 'id', $fieldsMapping = [])
    {
        if (!$this->esClient || empty($dataList)) {
            return false;
        }
        
        try {
            // 确保索引存在（使用第一条数据推断字段类型）
            if (!empty($dataList)) {
                $this->ensureIndexExists($dataList[0], $fieldsMapping);
            }
            
            $params = ['body' => []];
            
            foreach ($dataList as $data) {
                $id = $data[$idField] ?? null;
                if (!$id) {
                    continue;
                }
                
                $params['body'][] = [
                    'index' => [
                        '_index' => $this->esConfig['index'],
                        '_id' => $id
                    ]
                ];
                
                // 根据字段映射将数据转换为ES需要的格式
                $esData = $this->convertToEsFormat($data, $fieldsMapping);
                $params['body'][] = $esData;
            }
            
            if (empty($params['body'])) {
                return 0;
            }
            
            $response = $this->esClient->bulk($params);
            
            // 检查错误
            if ($response['errors'] ?? false) {
                foreach ($response['items'] as $item) {
                    if (isset($item['index']['error'])) {
                        \think\facade\Log::error('ES批量添加错误: ' . json_encode($item['index']['error'], JSON_UNESCAPED_UNICODE));
                    }
                }
                return false;
            }
            
            // 刷新索引
            $this->esClient->indices()->refresh(['index' => $this->esConfig['index']]);
            
            $count = count($dataList);
            
            return $count;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('ES批量添加失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 更新ES文档
     * @param array  $data 数据（必须包含ID）
     * @param string $idField ID字段名（由控制器传入）
     * @param array  $fieldsMapping 字段映射配置（由控制器传入）
     * @return bool
     */
    public function update($data, $idField = 'id', $fieldsMapping = [])
    {
        if (!$this->esClient || empty($data)) {
            return false;
        }
        
        try {
            $id = $data[$idField] ?? null;
            
            if (!$id) {
                throw new \Exception('缺少主键ID字段');
            }
            
            // 先检查文档是否存在
            try {
                $this->esClient->get([
                    'index' => $this->esConfig['index'],
                    'id' => $id
                ]);
            } catch (\Exception $e) {
                // 文档不存在，创建新文档
                return $this->add($data, $idField, $fieldsMapping);
            }
            
            // 根据字段映射将数据转换为ES需要的格式
            $esData = $this->convertToEsFormat($data, $fieldsMapping);
            
            $params = [
                'index' => $this->esConfig['index'],
                'id' => $id,
                'body' => ['doc' => $esData]
            ];
            
            $response = $this->esClient->update($params);
            
            return true;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('ES更新文档失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 删除ES文档
     * @param mixed $id 文档ID
     * @return bool
     */
    public function delete($id)
    {
        if (!$this->esClient || !$id) {
            return false;
        }
        
        try {
            $params = [
                'index' => $this->esConfig['index'],
                'id' => $id
            ];
            
            $response = $this->esClient->delete($params);
            
            return true;
            
        } catch (\Exception $e) {
            // 如果文档不存在，也认为是成功
            if (strpos($e->getMessage(), '404') !== false) {
                return true;
            }
            
            \think\facade\Log::error('ES删除文档失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 批量删除ES文档
     * @param array $ids 文档ID数组
     * @return bool|int
     */
    public function deleteAll($ids)
    {
        if (!$this->esClient || empty($ids)) {
            return false;
        }
        
        try {
            $params = ['body' => []];
            
            foreach ($ids as $id) {
                $params['body'][] = [
                    'delete' => [
                        '_index' => $this->esConfig['index'],
                        '_id' => $id
                    ]
                ];
            }
            
            $response = $this->esClient->bulk($params);
            
            // 刷新索引
            $this->esClient->indices()->refresh(['index' => $this->esConfig['index']]);
            
            $count = count($ids);
            
            return $count;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('ES批量删除失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 同步MySQL数据到ES
     * @param array  $where MySQL查询条件
     * @param string $idField ID字段名（由控制器传入）
     * @param array  $fieldsMapping 字段映射配置（由控制器传入）
     * @param int    $batchSize 批量大小
     * @return array
     */
    public function syncFromMySQL($where = [], $idField = 'id', $fieldsMapping = [], $batchSize = 1000)
    {
        if (!$this->esClient) {
            return ['success' => false, 'message' => 'ES连接失败'];
        }
        
        try {
            $tableName = $this->esConfig['index'];
            $total = 0;
            $success = 0;
            $fail = 0;
            
            // 分页查询MySQL数据
            $page = 1;
            while (true) {
                $query = Db::name($tableName);
                
                if (!empty($where)) {
                    $query->where($where);
                }
                
                $dataList = $query->page($page, $batchSize)
                    ->select()
                    ->toArray();
                
                if (empty($dataList)) {
                    break;
                }
                
                $total += count($dataList);
                
                // 批量添加到ES
                $result = $this->addAll($dataList, $idField, $fieldsMapping);
                
                if ($result === false) {
                    $fail += count($dataList);
                    \think\facade\Log::error("ES同步失败: 第{$page}页，{$batchSize}条记录");
                } else {
                    $success += $result;
                }
                
                $page++;
                
                // 避免内存占用过高
                if ($page % 10 == 0) {
                    gc_collect_cycles();
                }
            }
            
            return [
                'success' => true,
                'total' => $total,
                'success_count' => $success,
                'fail_count' => $fail,
                'message' => "同步完成：总数 {$total}，成功 {$success}，失败 {$fail}"
            ];
            
        } catch (\Exception $e) {
            \think\facade\Log::error('ES同步MySQL失败: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * 将数据转换为ES格式（支持数组格式）
     * @param array $data 原始数据
     * @param array $fieldsMapping 字段映射配置
     * @return array
     */
    /**
     * 将数据转换为ES格式（支持数组格式）
     * @param array $data 原始数据
     * @param array $fieldsMapping 字段映射配置
     * @return array
     */
    private function convertToEsFormat($data, $fieldsMapping = [])
    {
        $esData = [];
        
        foreach ($data as $field => $value) {
            // 跳过空值
            if ($value === null || $value === '') {
                continue;
            }
            
            // 根据字段映射决定是否转换为数组格式
            if (isset($fieldsMapping[$field]) && $fieldsMapping[$field] === 'array') {
                // 转换为数组格式
                if (is_array($value)) {
                    $esData[$field] = $value;
                } else {
                    $esData[$field] = [$value];
                }
            } else {
                // 保持原样
                $esData[$field] = $value;
            }
            
            // 关键：为所有文本字段生成片段字段
            if (is_string($value) && !empty(trim($value))) {
                $fragments = $this->generateTextFragments($value, $field);
                if (!empty($fragments)) {
                    $esData[$field . '_fragments'] = $fragments;
                }
            }
        }
        
        return $esData;
    }
    
    /**
     * 生成文本片段（仿照Python脚本的逻辑）
     * @param string $text 原始文本
     * @param string $fieldName 字段名（用于日志）
     * @return array
     */
    private function generateTextFragments(string $text, string $fieldName = ''): array
    {
        // 预处理文本
        $processedText = $text;
        
        if ($this->fragmentConfig['strip_whitespace']) {
            $processedText = preg_replace('/\s+/', ' ', trim($processedText));
        }
        
        if ($this->fragmentConfig['lowercase']) {
            $processedText = mb_strtolower($processedText, 'UTF-8');
        }
        
        $textLen = mb_strlen($processedText, 'UTF-8');
        
        // 如果文本为空，返回空数组
        if ($textLen === 0) {
            return [];
        }
        
        $fragments = [];
        
        // 1. 首先将整个文本作为一个片段
        $fragments[] = $processedText;
        
        // 2. 如果文本长度小于最小片段长度，直接返回整个文本
        if ($textLen < $this->fragmentConfig['min_fragment_length']) {
            return array_unique($fragments);
        }
        
        // 3. 如果文本长度小于等于最大片段长度，返回整个文本
        if ($textLen <= $this->fragmentConfig['max_fragment_length']) {
            return array_unique($fragments);
        }
        
        // 4. 生成滑动窗口片段（只对长文本进行）
        $fragmentSet = []; // 使用关联数组去重
        
        // 首先添加完整文本
        $fragmentSet[$processedText] = true;
        
        // 滑动窗口生成片段
        $minLen = $this->fragmentConfig['min_fragment_length'];
        $maxLen = $this->fragmentConfig['max_fragment_length'];
        $stepSize = $this->fragmentConfig['step_size'];
        $maxFragments = $this->fragmentConfig['max_fragments_per_field'];
        
        for ($start = 0; $start <= $textLen - $minLen; $start += $stepSize) {
            // 生成不同长度的片段
            for ($fragmentLen = $minLen; $fragmentLen <= min($maxLen, $textLen - $start); $fragmentLen++) {
                $end = $start + $fragmentLen;
                if ($end > $textLen) {
                    break;
                }
                
                $fragment = mb_substr($processedText, $start, $fragmentLen, 'UTF-8');
                if (trim($fragment) !== '') {
                    $fragmentSet[$fragment] = true;
                }
                
                // 如果已经达到最大片段数，提前结束
                if (count($fragmentSet) >= $maxFragments) {
                    break 2;
                }
            }
            
            if (count($fragmentSet) >= $maxFragments) {
                break;
            }
        }
        
        // 转换为数组
        $fragments = array_keys($fragmentSet);
        
        // 按长度排序，优先保留长片段
        usort($fragments, function ($a, $b) {
            return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8');
        });
        
        // 限制数量
        $fragments = array_slice($fragments, 0, $maxFragments);
        
        // 如果需要，添加短片段
        if ($this->fragmentConfig['include_short_fragments']) {
            $shortFragments = $this->generateShortFragments($processedText);
            $fragments = array_merge($fragments, $shortFragments);
            $fragments = array_unique($fragments);
            
            // 重新限制数量
            if (count($fragments) > $maxFragments) {
                $fragments = array_slice($fragments, 0, $maxFragments);
            }
        }
        return $fragments;
    }
    
    /**
     * 生成短片段
     * @param string $text 原始文本
     * @return array
     */
    private function generateShortFragments(string $text): array
    {
        $textLen = mb_strlen($text, 'UTF-8');
        $minLen = 2; // 短片段最小长度
        $maxLen = 5; // 短片段最大长度
        
        if ($textLen < $minLen) {
            return [];
        }
        
        $fragments = [];
        $stepSize = max(1, (int)($minLen / 2));
        
        for ($start = 0; $start <= $textLen - $minLen; $start += $stepSize) {
            for ($fragmentLen = $minLen; $fragmentLen <= min($maxLen, $textLen - $start); $fragmentLen++) {
                $end = $start + $fragmentLen;
                if ($end > $textLen) {
                    break;
                }
                
                $fragment = mb_substr($text, $start, $fragmentLen, 'UTF-8');
                if (trim($fragment) !== '') {
                    $fragments[] = $fragment;
                }
            }
        }
        
        return array_unique($fragments);
    }
    
    /**
     * 确保索引存在，并根据数据自动创建映射
     * @param array $sampleData 样本数据
     * @param array $fieldsMapping 字段映射配置（由控制器传入）
     * @return bool
     */
    private function ensureIndexExists($sampleData, $fieldsMapping = [])
    {
        try {
            // 检查索引是否存在
            $exists = $this->esClient->indices()->exists(['index' => $this->esConfig['index']]);
            
            if (!$exists->asBool()) {
                // 索引不存在，创建索引
                $mapping = $this->generateMapping($sampleData, $fieldsMapping);
                
                $params = [
                    'index' => $this->esConfig['index'],
                    'body' => [
                        'settings' => [
                            'number_of_shards' => 1,
                            'number_of_replicas' => 1,
                            'max_result_window' => 10000000,
                            'analysis' => [
                                'normalizer' => [
                                    'lowercase_normalizer' => [
                                        'type' => 'custom',
                                        'filter' => ['lowercase']
                                    ]
                                ]
                            ]
                        ],
                        'mappings' => [
                            'properties' => $mapping
                        ]
                    ]
                ];
                
                $this->esClient->indices()->create($params);
                \think\facade\Log::info('创建ES索引: ' . $this->esConfig['index']);
            }
            
            return true;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('确保ES索引存在失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 根据样本数据生成字段映射
     * @param array $sampleData 样本数据
     * @param array $fieldsMapping 字段映射配置（由控制器传入）
     * @return array
     */
    private function generateMapping($sampleData, $fieldsMapping = [])
    {
        $mapping = [];
        
        foreach ($sampleData as $field => $value) {
            if ($value === null) {
                continue;
            }
            
            // 如果控制器指定了字段类型，使用指定的类型
            if (isset($fieldsMapping[$field])) {
                $type = $fieldsMapping[$field];
            } else {
                $type = $this->detectFieldType($value);
            }
            
            // 对于数组格式的字段，需要特殊处理
            if (is_array($value)) {
                // 如果是空数组，跳过
                if (empty($value)) {
                    continue;
                }
                // 获取数组元素的类型
                $firstValue = reset($value);
                $elementType = $this->detectFieldType($firstValue);
                
                // 使用数组类型的映射
                $mapping[$field] = [
                    'type' => $elementType
                ];
            } else {
                $mapping[$field] = ['type' => $type];
            }
            
            // 对于文本字段，创建keyword子字段和fragments字段
            if ($type === 'text') {
                $mapping[$field]['fields'] = [
                    'keyword' => [
                        'type' => 'keyword',
                        'ignore_above' => 256
                    ]
                ];
                
                // 为文本字段添加_fragments字段映射
                $fragmentField = $field . '_fragments';
                $mapping[$fragmentField] = [
                    'type' => 'keyword',
                    'ignore_above' => 32766, // 支持长片段
                    'normalizer' => 'lowercase_normalizer'
                ];
            }
            
            // 对于日期字段，指定格式
            if ($type === 'date') {
                $mapping[$field]['format'] = 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_second||epoch_millis';
            }
        }
        
        return $mapping;
    }
    
    /**
     * 检测字段类型
     * @param mixed $value 字段值
     * @return string
     */
    private function detectFieldType($value)
    {
        if (is_int($value)) {
            return 'integer';
        }
        
        if (is_float($value)) {
            return 'float';
        }
        
        if (is_numeric($value) && strpos($value, '.') !== false) {
            return 'float';
        }
        
        if (is_numeric($value)) {
            return 'integer';
        }
        
        if (is_bool($value)) {
            return 'boolean';
        }
        
        if (is_array($value)) {
            // 如果是数组，返回第一个元素的类型
            if (!empty($value)) {
                return $this->detectFieldType(reset($value));
            }
            return 'text';
        }
        
        // 检查是否是日期格式
        if (is_string($value)) {
            // 检查是否是时间戳
            if (is_numeric($value) && (int)$value > 1000000000 && (int)$value < 2000000000) {
                return 'date';
            }
            
            // 检查是否是日期字符串
            if (strtotime($value) !== false) {
                return 'date';
            }
        }
        
        // 默认返回文本类型
        return 'text';
    }
    
    
    /**
     * 获取索引的映射信息
     * @return array
     */
    public function getMapping()
    {
        if (!$this->esClient) {
            return [];
        }
        
        try {
            $response = $this->esClient->indices()->getMapping([
                'index' => $this->esConfig['index']
            ]);
            
            return $response[$this->esConfig['index']]['mappings']['properties'] ?? [];
            
        } catch (\Exception $e) {
            \think\facade\Log::error('获取ES映射失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 检查索引是否存在
     * @return bool
     */
    public function indexExists($indexName = null)
    {
        if (!$this->esClient) {
            return false;
        }
        
        try {
            $index = $indexName ?: $this->esConfig['index'];
            return $this->esClient->indices()->exists(['index' => $index])->asBool();
        } catch (\Exception $e) {
            \think\facade\Log::error('检查ES索引存在失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 删除索引
     * @return bool
     */
    public function deleteIndex()
    {
        if (!$this->esClient) {
            return false;
        }
        
        try {
            $response = $this->esClient->indices()->delete(['index' => $this->esConfig['index']]);
            \think\facade\Log::info('删除ES索引: ' . $this->esConfig['index']);
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('删除ES索引失败: ' . $e->getMessage());
            return $e->getMessage();
        }
    }
    
    /**
     * 重建索引（先删除后创建）
     * @param array $mapping 映射配置（可选）
     * @return bool
     */
    public function rebuildIndex($mapping = null)
    {
        if (!$this->esClient) {
            return false;
        }
        
        try {
            // 删除现有索引
            if ($this->indexExists()) {
                $this->deleteIndex();
            }
            
            // 等待索引删除完成
            sleep(1);
            
            // 创建新索引
            $params = [
                'index' => $this->esConfig['index'],
                'body' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 1,
                        'max_result_window' => 10000000
                    ]
                ]
            ];
            
            if ($mapping) {
                $params['body']['mappings'] = ['properties' => $mapping];
            }
            
            $this->esClient->indices()->create($params);
            
            \think\facade\Log::info('重建ES索引: ' . $this->esConfig['index']);
            return true;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('重建ES索引失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取索引统计信息
     * @return array
     */
    public function getStats()
    {
        if (!$this->esClient) {
            return [];
        }
        
        try {
            $response = $this->esClient->indices()->stats(['index' => $this->esConfig['index']]);
            return $response['indices'][$this->esConfig['index']] ?? [];
        } catch (\Exception $e) {
            \think\facade\Log::error('获取ES统计失败: ' . $e->getMessage());
            return [];
        }
    }
    
    public function getClient()
    {
        return $this->esClient;
    }
}
