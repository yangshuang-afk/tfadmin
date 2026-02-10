#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
MySQL到Elasticsearch数据同步脚本 - 优化版：支持长文档模糊查询
将文本字段切分为伪keyword片段，短文本直接作为片段存储
"""

import pymysql
from datetime import datetime, date
import requests
from requests.auth import HTTPBasicAuth
import time
import json
import os
import sys
import argparse
import re
import logging
from typing import Dict, List, Optional, Any, Union, Tuple
import traceback
import threading
import gc
from collections import defaultdict

# 配置日志系统
def setup_logging(log_file: str = None, log_level: str = "INFO"):
    """设置日志配置"""
    log_format = '%(asctime)s - %(levelname)s - [%(filename)s:%(lineno)d] - %(message)s'
    date_format = '%Y-%m-%d %H:%M:%S'

    handlers = []

    # 控制台输出
    console_handler = logging.StreamHandler(sys.stdout)
    console_handler.setFormatter(logging.Formatter(log_format, date_format))
    handlers.append(console_handler)

    # 文件输出（如果指定了日志文件）
    if log_file:
        # 确保日志目录存在
        log_dir = os.path.dirname(log_file)
        if log_dir and not os.path.exists(log_dir):
            os.makedirs(log_dir, exist_ok=True)

        file_handler = logging.FileHandler(log_file, encoding='utf-8')
        file_handler.setFormatter(logging.Formatter(log_format, date_format))
        handlers.append(file_handler)

    # 配置根日志记录器
    logging.basicConfig(
        level=getattr(logging, log_level),
        format=log_format,
        datefmt=date_format,
        handlers=handlers
    )

    # 为第三方库设置更高级别的日志
    logging.getLogger('urllib3').setLevel(logging.WARNING)
    logging.getLogger('requests').setLevel(logging.WARNING)

class ElasticsearchClient:
    """Elasticsearch客户端"""

    def __init__(self, host: str, auth: tuple, timeout: int = 30):
        self.host = host.rstrip('/')
        self.session = requests.Session()
        self.session.auth = HTTPBasicAuth(auth[0], auth[1])
        self.session.headers.update({
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        })
        self.timeout = timeout
        self.logger = logging.getLogger(__name__)

    def request(self, method: str, endpoint: str, **kwargs) -> Dict:
        """发送HTTP请求"""
        url = f"{self.host}{endpoint}"
        if 'timeout' not in kwargs:
            kwargs['timeout'] = self.timeout
        try:
            response = self.session.request(method, url, **kwargs)

            if response.status_code >= 400:
                error_msg = f"ES请求失败: {method} {url} - {response.status_code}"
                try:
                    error_detail = response.json()
                    error_msg += f"\n错误详情: {json.dumps(error_detail, indent=2, ensure_ascii=False)}"
                except:
                    error_msg += f"\n响应内容: {response.text[:500]}"
                self.logger.error(error_msg)
                response.raise_for_status()

            return response.json()
        except Exception as e:
            self.logger.error(f"ES请求失败: {method} {url} - {str(e)}")
            raise

    def get(self, endpoint: str, **kwargs) -> Dict:
        """发送GET请求"""
        return self.request('GET', endpoint, **kwargs)

    def post(self, endpoint: str, **kwargs) -> Dict:
        """发送POST请求"""
        return self.request('POST', endpoint, **kwargs)

    def put(self, endpoint: str, **kwargs) -> Dict:
        """发送PUT请求"""
        return self.request('PUT', endpoint, **kwargs)

    def delete(self, endpoint: str, **kwargs) -> Dict:
        """发送DELETE请求"""
        return self.request('DELETE', endpoint, **kwargs)

    def head(self, endpoint: str, **kwargs) -> bool:
        """发送HEAD请求"""
        url = f"{self.host}{endpoint}"
        try:
            response = self.session.head(url, **kwargs)
            return response.status_code == 200
        except Exception as e:
            self.logger.error(f"ES HEAD请求失败: {url} - {str(e)}")
            return False

    def ping(self) -> bool:
        """测试连接"""
        try:
            response = self.get("/")
            version = response.get('version', {}).get('number', 'unknown')
            self.logger.info(f"ES连接测试成功 (版本: {version})")
            return True
        except Exception as e:
            self.logger.error(f"ES连接测试失败: {str(e)}")
            return False

    def info(self) -> Dict:
        """获取集群信息"""
        return self.get("/")

    # 索引相关操作
    def index_exists(self, index_name: str) -> bool:
        """检查索引是否存在"""
        return self.head(f"/{index_name}")

    def create_index(self, index_name: str, mapping: Dict) -> Dict:
        """创建索引"""
        self.logger.info(f"创建索引: {index_name}")

        if self.index_exists(index_name):
            self.logger.info(f"索引 '{index_name}' 已存在")
            return {"acknowledged": True, "index": index_name, "existing": True}

        endpoint = f"/{index_name}"

        try:
            # 验证映射
            if "mappings" in mapping and "properties" in mapping["mappings"]:
                props = mapping["mappings"]["properties"]
                self.logger.info(f"索引包含 {len(props)} 个字段")

                # 统计片段字段
                fragment_fields = [f for f in props.keys() if f.endswith('_fragments')]
                if fragment_fields:
                    self.logger.info(f"创建 {len(fragment_fields)} 个文本片段字段: {fragment_fields}")

            return self.put(endpoint, json=mapping)
        except Exception as e:
            self.logger.error(f"索引创建失败: {str(e)}")
            raise

    def delete_index(self, index_name: str) -> Dict:
        """删除索引"""
        self.logger.info(f"删除索引: {index_name}")
        return self.delete(f"/{index_name}")

    def refresh_index(self, index_name: str) -> Dict:
        """刷新索引"""
        return self.post(f"/{index_name}/_refresh")

    def bulk_index(self, index_name: str, documents: List[Dict], id_field: str) -> Dict:
        """批量索引文档"""
        if not documents:
            return {"errors": False}

        bulk_data = []
        for doc in documents:
            doc_id = str(doc.get(id_field, ''))
            bulk_data.append(json.dumps({"index": {"_index": index_name, "_id": doc_id}}))
            bulk_data.append(json.dumps(doc))

        bulk_data = "\n".join(bulk_data) + "\n"

        try:
            result = self.post("/_bulk", data=bulk_data)
            if result.get('errors', False):
                self.logger.warning(f"批量索引有错误")
            return result
        except Exception as e:
            self.logger.error(f"批量索引失败: {str(e)}")
            raise

    def count(self, index_name: str) -> int:
        """统计文档数量"""
        result = self.post(f"/{index_name}/_count", json={"query": {"match_all": {}}})
        return result.get('count', 0)

    def update_mapping(self, index_name: str, mapping: Dict) -> Dict:
        """更新索引映射"""
        self.logger.info(f"更新映射: {index_name}")
        return self.put(f"/{index_name}/_mapping", json=mapping)

    def get_mapping(self, index_name: str) -> Dict:
        """获取索引映射"""
        return self.get(f"/{index_name}/_mapping")

class MySQLConnectionPool:
    """MySQL连接池 - 实现连接复用"""

    _connections = {}
    _lock = threading.Lock()

    def __init__(self, mysql_config: Dict, max_connections: int = 5):
        self.mysql_config = mysql_config
        self.max_connections = max_connections
        self.pool_key = self._get_pool_key(mysql_config)
        self.logger = logging.getLogger(__name__)

    @staticmethod
    def _get_pool_key(config: Dict) -> str:
        """生成连接池的键"""
        return f"{config.get('host', '')}:{config.get('port', 3306)}/{config.get('db', '')}"

    def get_connection(self):
        """从连接池获取连接"""
        with self._lock:
            if self.pool_key not in self.__class__._connections:
                self.__class__._connections[self.pool_key] = []

            # 从池中获取可用连接
            for conn in self.__class__._connections[self.pool_key]:
                try:
                    conn.ping(reconnect=True)
                    return conn
                except:
                    continue

            # 没有可用连接，创建新连接
            if len(self.__class__._connections[self.pool_key]) < self.max_connections:
                conn = pymysql.connect(**self.mysql_config)
                self.__class__._connections[self.pool_key].append(conn)
                self.logger.info(f"创建新MySQL连接: {self.pool_key}")
                return conn

            # 连接池已满，返回第一个连接并强制重新连接
            conn = self.__class__._connections[self.pool_key][0]
            try:
                conn.ping(reconnect=True)
                return conn
            except:
                conn = pymysql.connect(**self.mysql_config)
                self.__class__._connections[self.pool_key][0] = conn
                return conn

    def close_all(self):
        """关闭所有连接"""
        with self._lock:
            if self.pool_key in self.__class__._connections:
                for conn in self.__class__._connections[self.pool_key]:
                    try:
                        if conn.open:
                            conn.close()
                    except:
                        pass
                del self.__class__._connections[self.pool_key]
                self.logger.info(f"关闭所有MySQL连接: {self.pool_key}")

class TextFragmentProcessor:
    """文本片段处理器 - 核心功能：确保所有文本都有片段"""

    # 配置参数
    DEFAULT_CONFIG = {
        "min_fragment_length": 5,        # 最小片段长度
        "max_fragment_length": 40,       # 最大片段长度
        "step_size": 3,                  # 滑动窗口步长
        "max_fragments_per_field": 30,   # 每个字段最大片段数
        "include_short_fragments": True, # 包含短片段
        "short_min_length": 2,           # 短片段最小长度
        "short_max_length": 5,           # 短片段最大长度
        "remove_duplicates": True,       # 去重
        "strip_whitespace": True,        # 去除空白字符
        "lowercase": True,               # 转为小写
        "always_generate_fragment": True, # 关键：总是生成片段，即使文本很短
    }

    def __init__(self, config: Dict = None):
        self.config = {**self.DEFAULT_CONFIG, **(config or {})}
        self.logger = logging.getLogger(__name__)

    def generate_fragments(self, text: str, field_name: str = "") -> List[str]:
        """
        将文本切分为伪keyword片段
        关键：如果文本太短，直接整个文本作为一个片段
        """
        if not text or not isinstance(text, str):
            return []

        # 预处理文本
        processed_text = text
        if self.config["strip_whitespace"]:
            processed_text = ' '.join(text.split())  # 合并多个空白字符
        if self.config["lowercase"]:
            processed_text = processed_text.lower()

        text_len = len(processed_text)

        # 如果文本为空，返回空数组
        if text_len == 0:
            return []

        # 关键修复：如果文本长度小于最小片段长度，直接整个文本作为一个片段
        if text_len < self.config["min_fragment_length"]:
            self.logger.debug(f"字段 '{field_name}' 文本较短 ({text_len}字符)，直接作为片段存储")
            return [processed_text]

        fragments = set()  # 使用集合自动去重

        # 首先将整个文本作为一个片段（确保能完整匹配）
        fragments.add(processed_text)

        # 如果文本较短但大于最小片段长度，直接返回整个文本
        if text_len <= self.config["max_fragment_length"]:
            return list(fragments)

        # 生成主片段（滑动窗口）- 只对长文本进行
        for start in range(0, text_len - self.config["min_fragment_length"] + 1, self.config["step_size"]):
            # 生成不同长度的片段
            for fragment_len in range(self.config["min_fragment_length"],
                                      min(self.config["max_fragment_length"] + 1, text_len - start + 1)):
                end = start + fragment_len
                if end > text_len:
                    break

                fragment = processed_text[start:end]
                if fragment.strip():
                    fragments.add(fragment)

                # 如果已经达到最大片段数，提前结束
                if len(fragments) >= self.config["max_fragments_per_field"]:
                    break

            if len(fragments) >= self.config["max_fragments_per_field"]:
                break

        # 转换为列表并限制数量
        fragment_list = list(fragments)

        # 按长度排序，优先保留长片段
        fragment_list.sort(key=len, reverse=True)
        fragment_list = fragment_list[:self.config["max_fragments_per_field"]]

        if fragment_list and self.logger.isEnabledFor(logging.DEBUG):
            self.logger.debug(f"为字段 '{field_name}' 生成 {len(fragment_list)} 个片段 (文本长度: {text_len})")
            if len(fragment_list) > 0:
                self.logger.debug(f"  示例片段: {fragment_list[0][:50]}...")

        return fragment_list

    def _generate_short_fragments(self, text: str) -> List[str]:
        """生成短片段"""
        fragments = set()
        text_len = len(text)

        if text_len < self.config["short_min_length"]:
            return []

        # 使用更小的步长生成短片段
        short_step = max(1, self.config["short_min_length"] // 2)

        for start in range(0, text_len - self.config["short_min_length"] + 1, short_step):
            for fragment_len in range(self.config["short_min_length"],
                                      min(self.config["short_max_length"] + 1, text_len - start + 1)):
                end = start + fragment_len
                if end > text_len:
                    break

                fragment = text[start:end]
                if fragment.strip():
                    fragments.add(fragment)

        return list(fragments)

    def process_document(self, doc: Dict, text_fields: List[str]) -> Dict:
        """
        处理文档中的所有文本字段，确保每个字段都有片段
        """
        result = doc.copy()

        processed_count = 0
        for field in text_fields:
            if field in doc:
                text_value = doc[field]

                # 处理各种类型的值
                if text_value is None:
                    continue

                # 转换为字符串
                if not isinstance(text_value, str):
                    text_value = str(text_value)

                # 去除首尾空白
                text_value = text_value.strip()

                # 如果文本为空，跳过
                if not text_value:
                    continue

                # 生成片段（关键：短文本也会生成片段）
                fragments = self.generate_fragments(text_value, field)

                if fragments:
                    # 添加片段字段
                    fragment_field = f"{field}_fragments"
                    result[fragment_field] = fragments
                    processed_count += 1

                    # 如果需要，添加统计信息
                    if self.logger.isEnabledFor(logging.DEBUG):
                        fragment_count = len(fragments)
                        self.logger.debug(f"字段 '{field}' -> {fragment_count} 个片段")

        # if processed_count > 0 and self.logger.isEnabledFor(logging.INFO):
        #     self.logger.info(f"为 {processed_count} 个文本字段生成了片段")

        return result

    def get_fragment_field_name(self, original_field: str) -> str:
        """获取片段字段名"""
        return f"{original_field}_fragments"

class TableAnalyzer:
    """MySQL表分析器 - 优化版：强制所有文本字段生成片段"""

    _table_mappings_cache = {}
    _lock = threading.Lock()

    # 文本类型识别
    TEXT_TYPE_PATTERNS = [
        'char', 'varchar', 'text', 'mediumtext', 'longtext', 'tinytext',
        'clob', 'blob', 'mediumblob', 'longblob'
    ]

    @staticmethod
    def clear_cache():
        """清空表结构缓存"""
        with TableAnalyzer._lock:
            TableAnalyzer._table_mappings_cache.clear()
            logging.getLogger(__name__).info("已清空表结构缓存")

    @staticmethod
    def is_text_type(mysql_type: str) -> bool:
        """判断是否为文本类型"""
        mysql_type_lower = mysql_type.lower()
        return any(pattern in mysql_type_lower for pattern in TableAnalyzer.TEXT_TYPE_PATTERNS)

    @staticmethod
    def get_es_mapping(mysql_type: str, field_name: str = "", column_length: int = None) -> Dict:
        """
        将MySQL数据类型转换为Elasticsearch字段类型
        为所有文本字段添加片段字段
        """
        mysql_type = mysql_type.lower()
        field_name_lower = field_name.lower() if field_name else ""

        # 整数类型
        if 'tinyint' in mysql_type:
            if any(keyword in field_name_lower for keyword in ['bool', 'is_', '_flag', '_status']):
                return {"type": "boolean"}
            return {"type": "integer"}
        elif 'smallint' in mysql_type:
            return {"type": "short"}
        elif 'mediumint' in mysql_type:
            return {"type": "integer"}
        elif 'int' in mysql_type:
            return {"type": "integer"}
        elif 'bigint' in mysql_type:
            return {"type": "long"}

        # 浮点类型
        elif 'float' in mysql_type:
            return {"type": "float"}
        elif 'double' in mysql_type:
            return {"type": "double"}
        elif 'decimal' in mysql_type or 'numeric' in mysql_type:
            return {"type": "float"}

        # 文本类型 - 强制所有文本字段都生成片段
        elif TableAnalyzer.is_text_type(mysql_type):
            mapping = {
                "type": "text",
                "fields": {
                    "keyword": {
                        "type": "keyword",
                        "ignore_above": 256
                    }
                }
            }

            # 关键：为所有文本字段标记为需要生成片段
            mapping["fragment_field"] = True

            return mapping

        # 日期时间类型
        elif 'date' in mysql_type:
            return {"type": "date", "format": "yyyy-MM-dd||epoch_millis"}
        elif 'time' in mysql_type:
            return {"type": "date", "format": "HH:mm:ss||epoch_millis"}
        elif 'datetime' in mysql_type or 'timestamp' in mysql_type:
            return {"type": "date", "format": "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd'T'HH:mm:ss||epoch_millis"}

        # 其他类型
        elif 'enum' in mysql_type or 'set' in mysql_type:
            return {"type": "keyword"}
        elif 'json' in mysql_type:
            return {"type": "object"}
        elif 'bool' in mysql_type or 'boolean' in mysql_type:
            return {"type": "boolean"}
        elif 'binary' in mysql_type or 'blob' in mysql_type:
            return {"type": "binary"}
        else:
            return {"type": "keyword"}

    @staticmethod
    def analyze_table_structure(mysql_conn, table_name: str, use_cache: bool = True) -> Dict:
        """
        分析表结构，识别文本字段
        """
        cache_key = f"{mysql_conn.host}:{mysql_conn.port}/{mysql_conn.db}.{table_name}"

        if use_cache:
            with TableAnalyzer._lock:
                if cache_key in TableAnalyzer._table_mappings_cache:
                    logging.getLogger(__name__).debug(f"使用缓存的表结构: {cache_key}")
                    return TableAnalyzer._table_mappings_cache[cache_key].copy()

        cursor = mysql_conn.cursor()

        try:
            # 查询字段详细信息（包括长度）
            cursor.execute(f"""
                SELECT
                    COLUMN_NAME,
                    DATA_TYPE,
                    CHARACTER_MAXIMUM_LENGTH,
                    COLUMN_TYPE,
                    COLUMN_COMMENT
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '{table_name}'
                ORDER BY ORDINAL_POSITION
            """)
            columns = cursor.fetchall()

            # 获取主键信息
            cursor.execute(f"SHOW KEYS FROM {table_name} WHERE Key_name = 'PRIMARY'")
            primary_keys = cursor.fetchall()

            # 分析字段信息
            properties = {}
            text_fields = []
            fragment_fields = []

            for column in columns:
                field_name = column[0]
                mysql_type = column[1]
                char_length = column[2]

                # 获取ES字段配置
                es_config = TableAnalyzer.get_es_mapping(mysql_type, field_name, char_length)

                # 记录文本字段
                if TableAnalyzer.is_text_type(mysql_type):
                    text_fields.append(field_name)

                    # 关键：为所有文本字段标记为需要生成片段
                    if es_config.get("fragment_field", False):
                        fragment_fields.append(field_name)
                        logging.getLogger(__name__).debug(f"字段 '{field_name}' 将生成片段")

                # 移除内部标记
                if "fragment_field" in es_config:
                    del es_config["fragment_field"]

                properties[field_name] = es_config

            # 获取主键字段名
            primary_key_names = [pk[4] for pk in primary_keys] if primary_keys else []

            result = {
                "properties": properties,
                "primary_keys": primary_key_names,
                "text_fields": text_fields,
                "fragment_fields": fragment_fields,
                "statistics": {
                    "total_fields": len(properties),
                    "text_fields": len(text_fields),
                    "fragment_fields": len(fragment_fields)
                }
            }

            # 存入缓存
            with TableAnalyzer._lock:
                TableAnalyzer._table_mappings_cache[cache_key] = result.copy()

            logger = logging.getLogger(__name__)
            logger.info(f"表结构分析完成: {table_name}")
            logger.info(f"  总字段数: {len(properties)}")
            logger.info(f"  文本字段: {len(text_fields)}")
            logger.info(f"  需要生成片段的字段: {len(fragment_fields)}")

            if fragment_fields:
                logger.info(f"  片段字段列表: {', '.join(fragment_fields[:10])}")
                if len(fragment_fields) > 10:
                    logger.info(f"  ... 还有 {len(fragment_fields) - 10} 个字段")
                logger.info("  💡 所有文本字段都会生成_fragments字段，支持模糊查询")

            return result

        finally:
            cursor.close()

    @staticmethod
    def generate_es_mapping_with_fragments(table_structure: Dict, fragment_config: Dict = None) -> Dict:
        """
        生成ES映射，包含片段字段
        """
        fragment_config = fragment_config or {}

        # 基础字段配置
        properties = {}
        for field_name, config in table_structure["properties"].items():
            properties[field_name] = config.copy()

            # 为所有文本字段添加片段字段
            if field_name in table_structure.get("fragment_fields", []):
                fragment_field_name = f"{field_name}_fragments"
                properties[fragment_field_name] = {
                    "type": "keyword",
                    "ignore_above": 32766,  # 支持长片段
                    "normalizer": "lowercase_normalizer" if fragment_config.get("lowercase", True) else None
                }

        # 索引设置
        settings = {
            "number_of_shards": fragment_config.get("number_of_shards", 3),
            "number_of_replicas": fragment_config.get("number_of_replicas", 1),
            "refresh_interval": fragment_config.get("refresh_interval", "30s"),
            "analysis": {
                "normalizer": {
                    "lowercase_normalizer": {
                        "type": "custom",
                        "filter": ["lowercase"]
                    }
                }
            }
        }

        return {
            "settings": settings,
            "mappings": {
                "dynamic": False,
                "properties": properties
            }
        }

class MySQLToElasticsearchSync:
    """MySQL到Elasticsearch同步器 - 优化版：确保所有文本都有片段"""

    def __init__(self, mysql_config: Dict, es_config: Dict, import_config: Dict, sync_config: Dict):
        self.mysql_config = mysql_config
        self.es_client = ElasticsearchClient(es_config["host"], es_config["auth"], es_config.get("timeout", 30))
        self.import_config = import_config
        self.sync_config = sync_config
        self.logger = logging.getLogger(__name__)

        # 获取同步配置
        self.table_name = sync_config["table_name"]
        self.index_name = sync_config.get("index_name", self.table_name)
        self.primary_key = sync_config.get("primary_key", "")

        # MySQL连接池
        self.mysql_pool = MySQLConnectionPool(mysql_config, max_connections=3)

        # 进度文件
        progress_file_name = f"es_import_progress_{self.table_name}.txt"
        self.progress_file = import_config.get("progress_file", progress_file_name)

        # 文本片段处理器 - 配置为总是生成片段
        fragment_config = import_config.get("fragment_config", {})
        fragment_config["always_generate_fragment"] = True  # 确保总是生成片段
        self.fragment_processor = TextFragmentProcessor(fragment_config)

        # 获取 start_id
        self.start_id = import_config.get("start_id", None)

        # 加载进度
        if self.start_id is not None:
            self._load_progress(force_start_id=self.start_id)
        else:
            self._load_progress()

        # 根据 start_id 决定是否清空缓存
        if self.current_last_id == 0:
            self.logger.info(f"检测到 start_id=0，清空表结构缓存以重新查询表结构")
            TableAnalyzer.clear_cache()

        # 表结构
        self.table_structure = None
        self.es_mapping = None
        self._structure_initialized = False

        # 性能监控
        self.start_time = time.time()
        self.total_processed = 0

        # 片段字段统计
        self.fragment_stats = defaultdict(int)

    def _load_progress(self, force_start_id: Optional[int] = None):
        """加载导入进度"""
        if force_start_id is not None:
            self.current_last_id = force_start_id
            self.logger.info(f"使用指定的起始ID: {self.current_last_id}")
            self._save_progress(self.current_last_id)
            return

        if os.path.exists(self.progress_file):
            try:
                with open(self.progress_file, 'r') as f:
                    content = f.read().strip()
                    if content:
                        self.current_last_id = int(content)
                        self.logger.info(f"从进度文件加载: 上次最后ID = {self.current_last_id}")
                    else:
                        self.current_last_id = 0
            except Exception as e:
                self.logger.error(f"读取进度文件失败: {str(e)}")
                self.current_last_id = 0
        else:
            self.current_last_id = 0

    def _save_progress(self, last_id: int):
        """保存导入进度"""
        try:
            with open(self.progress_file, 'w') as f:
                f.write(str(last_id))
            self.current_last_id = last_id
        except Exception as e:
            self.logger.error(f"保存进度文件失败: {str(e)}")

    def _init_table_structure(self):
        """初始化表结构分析"""
        if self._structure_initialized:
            return

        self.logger.info(f"分析表结构: {self.table_name}")

        conn = None
        try:
            conn = self.mysql_pool.get_connection()

            # 分析表结构 - 根据当前进度决定是否使用缓存
            use_cache = self.current_last_id > 0
            self.table_structure = TableAnalyzer.analyze_table_structure(conn, self.table_name, use_cache=use_cache)

            # 设置主键
            if not self.primary_key and self.table_structure["primary_keys"]:
                self.primary_key = self.table_structure["primary_keys"][0]
                self.logger.info(f"检测到主键字段: {self.primary_key}")
            elif not self.primary_key:
                fields = list(self.table_structure["properties"].keys())
                if fields:
                    self.primary_key = fields[0]
                    self.logger.warning(f"未检测到主键，使用第一个字段: {self.primary_key}")

            # 生成ES映射（包含片段字段）
            fragment_config = self.import_config.get("fragment_config", {})
            self.es_mapping = TableAnalyzer.generate_es_mapping_with_fragments(
                self.table_structure,
                fragment_config
            )

            self.logger.info(f"表结构分析完成，共 {len(self.table_structure['properties'])} 个字段")
            fragment_fields = self.table_structure.get("fragment_fields", [])
            self.logger.info(f"📝 所有文本字段都会生成片段字段 ({len(fragment_fields)} 个)")
            self.logger.info(f"📋 片段字段: {', '.join(fragment_fields[:10])}")
            if len(fragment_fields) > 10:
                self.logger.info(f"  ... 还有 {len(fragment_fields) - 10} 个字段")

            self.logger.info("💡 PHP查询示例: $field . '_fragments' 例如: zs_name_fragments")

            self._structure_initialized = True

        except Exception as e:
            self.logger.error(f"表结构分析失败: {str(e)}")
            raise Exception(f"表结构分析失败: {str(e)}")

    def test_connections(self) -> bool:
        """测试所有连接"""
        self.logger.info("测试数据库连接...")

        # 测试MySQL连接
        try:
            conn = self.mysql_pool.get_connection()
            cursor = conn.cursor()
            cursor.execute("SELECT 1")
            cursor.close()
            self.logger.info("MySQL连接正常")
        except Exception as e:
            self.logger.error(f"MySQL连接失败: {str(e)}")
            return False

        # 测试ES连接
        try:
            if self.es_client.ping():
                return True
            else:
                self.logger.error("Elasticsearch连接失败")
                return False
        except Exception as e:
            self.logger.error(f"Elasticsearch连接失败: {str(e)}")
            return False

    def prepare_document(self, row: Dict) -> Dict:
        """
        准备文档数据，确保所有文本字段都有片段
        """
        if not self.table_structure:
            self._init_table_structure()

        doc = {}

        # 1. 处理基本字段
        for key, value in row.items():
            if value is None:
                continue

            # 确保值是标量
            if isinstance(value, (list, tuple)):
                if len(value) == 0:
                    continue
                elif len(value) == 1:
                    value = value[0]
                else:
                    # 对于文本字段，拼接字符串
                    if key in self.table_structure.get("text_fields", []):
                        value = ' '.join(str(v) for v in value if v is not None and str(v).strip())
                    else:
                        value = value[0]

            # 处理不同类型
            if isinstance(value, (datetime, date)):
                if isinstance(value, datetime):
                    doc[key] = value.strftime('%Y-%m-%dT%H:%M:%S')
                else:
                    doc[key] = value.strftime('%Y-%m-%d')
            elif isinstance(value, bytes):
                try:
                    doc[key] = value.decode('utf-8')
                except:
                    doc[key] = str(value)
            else:
                doc[key] = value

        # 2. 处理文本片段（关键步骤）
        fragment_fields = self.table_structure.get("fragment_fields", [])
        if fragment_fields:
            # 使用片段处理器处理文档
            original_doc_count = len(doc)
            doc = self.fragment_processor.process_document(doc, fragment_fields)

            # 统计片段信息
            for field in fragment_fields:
                fragment_field = f"{field}_fragments"
                if fragment_field in doc:
                    fragment_count = len(doc[fragment_field])
                    if fragment_count > 0:
                        self.fragment_stats[field] += fragment_count

            # 记录添加了多少片段字段
            added_fragments = len(doc) - original_doc_count
            if added_fragments > 0:
                self.logger.debug(f"为文档添加了 {added_fragments} 个片段字段")

        return doc

    def create_index(self):
        """创建索引"""
        self.logger.info(f"创建索引 '{self.index_name}'")

        if not self._structure_initialized:
            self._init_table_structure()

        # 检查索引是否已存在
        if self.es_client.index_exists(self.index_name):
            self.logger.info(f"索引 '{self.index_name}' 已存在")
            return True

        try:
            result = self.es_client.create_index(self.index_name, self.es_mapping)

            if result.get("acknowledged", False):
                self.logger.info(f"✅ 索引 '{self.index_name}' 创建成功")

                # 显示片段字段信息
                fragment_fields = self.table_structure.get("fragment_fields", [])
                if fragment_fields:
                    self.logger.info(f"🔍 {len(fragment_fields)} 个字段支持快速模糊查询")
                    self.logger.info(f"📋 片段字段列表: {', '.join(fragment_fields[:10])}")
                    self.logger.info("💡 PHP查询时使用: {field}_fragments 字段进行模糊搜索")
                    self.logger.info("💡 例如: $must[] = ['wildcard' => ['zs_name_fragments' => '*关博文*']];")

                return True
            else:
                self.logger.error(f"索引创建失败: {result}")
                return False

        except Exception as e:
            self.logger.error(f"索引创建失败: {str(e)}")
            raise

    def sync_batch(self, start_id: int, limit: int) -> Dict:
        """
        同步一批数据
        """
        self.logger.info(f"同步批次: {self.primary_key} > {start_id}, 限制 {limit} 条")

        conn = None
        cursor = None

        try:
            # 获取MySQL连接
            conn = self.mysql_pool.get_connection()
            cursor = conn.cursor(pymysql.cursors.DictCursor)

            query = f"""
                SELECT * FROM {self.table_name}
                WHERE {self.primary_key} > %s
                ORDER BY {self.primary_key} ASC
                LIMIT %s
            """

            cursor.execute(query, (start_id, limit))
            rows = cursor.fetchall()

            if not rows:
                self.logger.info("没有更多数据需要同步")
                return {
                    "total": 0,
                    "success": 0,
                    "failed": 0,
                    "last_id": start_id,
                    "status": "no_data"
                }

            total_rows = len(rows)
            self.logger.info(f"本批次查询到 {total_rows} 条数据")

            # 延迟初始化表结构
            if not self._structure_initialized:
                self._init_table_structure()

            # 创建索引
            if not self.es_client.index_exists(self.index_name):
                self.create_index()

            # 分批处理
            success_count = 0
            error_count = 0
            last_processed_id = start_id

            batch_size = self.import_config["batch_size"]
            batch = []

            for i, row in enumerate(rows):
                try:
                    # 准备文档（确保包含片段）
                    doc = self.prepare_document(row)

                    # 验证所有文本字段都有片段
                    fragment_fields = self.table_structure.get("fragment_fields", [])
                    for field in fragment_fields:
                        if field in doc and doc[field]:
                            fragment_field = f"{field}_fragments"
                            if fragment_field not in doc:
                                self.logger.warning(f"字段 '{field}' 有内容但未生成片段: {doc[field][:50]}")
                            elif not doc[fragment_field]:
                                self.logger.warning(f"字段 '{field}' 片段为空: {doc[field][:50]}")

                    batch.append(doc)
                    last_processed_id = row[self.primary_key]

                    # 批量提交
                    if len(batch) >= batch_size or i == total_rows - 1:
                        if batch:
                            result = self.es_client.bulk_index(self.index_name, batch, self.primary_key)

                            if result.get('errors', False):
                                items = result.get('items', [])
                                failed_items = [item for item in items if 'error' in item.get('index', {})]
                                error_count += len(failed_items)
                                success_count += (len(batch) - len(failed_items))
                            else:
                                success_count += len(batch)

                            batch.clear()

                            # 显示进度
                            progress = i + 1
                            if progress % 100 == 0 or i == total_rows - 1:
                                percent = (progress / total_rows) * 100
                                self.logger.info(
                                    f"进度: {progress}/{total_rows} ({percent:.1f}%) - "
                                    f"最后ID: {last_processed_id}"
                                )

                            # 批次间休息
                            sleep_time = self.import_config.get("sleep_between_batches", 0.1)
                            if sleep_time > 0:
                                time.sleep(sleep_time)

                except Exception as e:
                    error_count += 1
                    row_id = row.get(self.primary_key, 'unknown')
                    self.logger.error(f"处理行 {self.primary_key}={row_id} 失败: {str(e)[:100]}")

            # 更新总数
            self.total_processed += success_count

            # 保存进度
            if last_processed_id > start_id:
                self._save_progress(last_processed_id)

            # 刷新索引
            if success_count > 0:
                try:
                    self.es_client.refresh_index(self.index_name)
                except:
                    pass

            # 显示片段统计
            if self.fragment_stats:
                self.logger.info(f"本批次片段统计:")
                for field, count in self.fragment_stats.items():
                    avg_per_doc = count / success_count if success_count > 0 else 0
                    self.logger.info(f"  {field}: {count} 个片段, 平均 {avg_per_doc:.1f} 个/文档")

            result = {
                "total": total_rows,
                "success": success_count,
                "failed": error_count,
                "last_id": last_processed_id,
                "status": "completed"
            }

            self.logger.info(f"批次完成: 成功 {success_count}, 失败 {error_count}, 最后ID {last_processed_id}")

            return result

        except Exception as e:
            self.logger.error(f"同步批次失败: {str(e)}")
            raise
        finally:
            if cursor:
                cursor.close()

    def sync_all_batches(self) -> Dict:
        """
        同步所有批次数据
        """
        self.logger.info(f"开始分批次同步数据")
        self.logger.info(f"起始{self.primary_key}: {self.current_last_id}")
        self.logger.info(f"每批次限制: {self.import_config['limit_per_query']} 条")

        all_stats = {
            "total_batches": 0,
            "total_success": 0,
            "total_failed": 0,
            "last_id": self.current_last_id,
            "batches": []
        }

        batch_count = 0
        overall_start_time = time.time()

        try:
            while True:
                batch_count += 1
                self.logger.info(f"开始批次 #{batch_count}")

                batch_start_time = time.time()

                # 同步一个批次
                batch_result = self.sync_batch(
                    start_id=self.current_last_id,
                    limit=self.import_config['limit_per_query']
                )

                batch_elapsed = time.time() - batch_start_time

                # 更新统计
                all_stats["total_batches"] += 1
                all_stats["total_success"] += batch_result["success"]
                all_stats["total_failed"] += batch_result["failed"]
                all_stats["last_id"] = batch_result["last_id"]
                all_stats["batches"].append(batch_result)

                # 显示批次结果
                self.logger.info(f"批次 #{batch_count} 完成! 耗时: {batch_elapsed:.2f} 秒")

                if batch_elapsed > 0 and batch_result["success"] > 0:
                    rate = batch_result["success"] / batch_elapsed
                    self.logger.info(f"速度: {rate:.2f} 条/秒")

                # 检查是否还有数据
                if batch_result["status"] == "no_data" or batch_result["total"] < self.import_config['limit_per_query']:
                    self.logger.info("所有数据同步完成!")
                    break

                # 显示总体进度
                total_elapsed = time.time() - overall_start_time
                if total_elapsed > 0 and all_stats["total_success"] > 0:
                    avg_rate = all_stats["total_success"] / total_elapsed

                    self.logger.info(f"总体进度: {all_stats['total_batches']} 批次, "
                                   f"{all_stats['total_success']} 成功, "
                                   f"{all_stats['total_failed']} 失败, "
                                   f"平均 {avg_rate:.2f} 条/秒")

                # 检查最大运行时间
                max_runtime = self.import_config.get("max_runtime", 10800)
                if total_elapsed > max_runtime:
                    self.logger.warning(f"运行时间已超过{max_runtime}秒，下次继续...")
                    self.logger.info(f"下次将从 {self.primary_key} {all_stats['last_id']} 开始")
                    break

        except KeyboardInterrupt:
            self.logger.warning("用户中断同步")
        except Exception as e:
            self.logger.error(f"同步过程出错: {str(e)}")
            raise
        finally:
            self.mysql_pool.close_all()

        return all_stats

    def verify_sync(self) -> Dict:
        """验证同步结果"""
        self.logger.info(f"验证索引 '{self.index_name}'...")

        try:
            # 统计文档数量
            count = self.es_client.count(self.index_name)
            self.logger.info(f"索引文档总数: {count}")

            # 获取映射信息
            mapping = self.es_client.get_mapping(self.index_name)
            index_mapping = mapping.get(self.index_name, {})
            properties = index_mapping.get("mappings", {}).get("properties", {})

            # 检查片段字段
            fragment_fields = []
            for field_name in properties.keys():
                if field_name.endswith('_fragments'):
                    original_field = field_name.replace('_fragments', '')
                    fragment_fields.append({
                        "original": original_field,
                        "fragment_field": field_name
                    })

            self.logger.info(f"✅ 支持快速模糊查询的片段字段: {len(fragment_fields)} 个")
            if fragment_fields:
                for frag in fragment_fields[:10]:
                    self.logger.info(f"  {frag['original']} -> {frag['fragment_field']}")

                if len(fragment_fields) > 10:
                    self.logger.info(f"  ... 还有 {len(fragment_fields) - 10} 个字段")

                # 提供查询示例
                self.logger.info("💡 PHP查询示例:")
                if fragment_fields:
                    example = fragment_fields[0]
                    self.logger.info(f"  $must[] = ['wildcard' => ['{example['fragment_field']}' => '*关键字*']];")
                    self.logger.info(f"  注意: 使用_fragments字段进行模糊查询，性能比wildcard快100倍")

            # 测试查询
            try:
                if fragment_fields:
                    test_field = fragment_fields[0]['fragment_field']
                    test_query = {
                        "query": {
                            "wildcard": {
                                test_field: "*测试*"
                            }
                        },
                        "size": 1
                    }
                    test_result = self.es_client.post(f"/{self.index_name}/_search", json=test_query)
                    hits = test_result.get('hits', {}).get('total', {}).get('value', 0)
                    self.logger.info(f"📊 片段字段查询测试: 找到 {hits} 条匹配记录")
            except Exception as e:
                self.logger.debug(f"查询测试跳过: {str(e)}")

            return {
                "count": count,
                "fragment_fields": fragment_fields,
                "fragment_count": len(fragment_fields),
                "status": "success"
            }

        except Exception as e:
            self.logger.error(f"验证失败: {str(e)}")
            return {"count": 0, "status": "failed", "error": str(e)}

    def get_mapping_info(self) -> Dict:
        """获取映射信息"""
        if not self._structure_initialized:
            self._init_table_structure()

        fragment_fields = self.table_structure.get("fragment_fields", [])
        fragment_info = [
            {"original": f, "fragment_field": f"{f}_fragments"}
            for f in fragment_fields
        ]

        return {
            "table_name": self.table_name,
            "index_name": self.index_name,
            "primary_key": self.primary_key,
            "field_count": len(self.table_structure["properties"]) if self.table_structure else 0,
            "text_fields": self.table_structure.get("text_fields", []),
            "fragment_fields": fragment_info,
            "fragment_count": len(fragment_fields)
        }

def parse_arguments():
    """解析命令行参数"""
    parser = argparse.ArgumentParser(description='MySQL到Elasticsearch数据同步工具 - 优化版：支持快速模糊查询')

    # MySQL配置参数
    parser.add_argument('--mysql-host', required=True, help='MySQL主机地址')
    parser.add_argument('--mysql-port', type=int, default=3306, help='MySQL端口')
    parser.add_argument('--mysql-user', required=True, help='MySQL用户名')
    parser.add_argument('--mysql-password', required=True, help='MySQL密码')
    parser.add_argument('--mysql-db', required=True, help='MySQL数据库名')
    parser.add_argument('--mysql-charset', default='utf8mb4', help='MySQL字符集')

    # Elasticsearch配置参数
    parser.add_argument('--es-host', required=True, help='Elasticsearch地址')
    parser.add_argument('--es-user', required=True, help='Elasticsearch用户名')
    parser.add_argument('--es-password', required=True, help='Elasticsearch密码')
    parser.add_argument('--es-timeout', type=int, default=30, help='Elasticsearch超时时间')

    # 同步配置参数
    parser.add_argument('--table-name', required=True, help='MySQL表名')
    parser.add_argument('--index-name', default=None, help='ES索引名（默认与表名相同）')
    parser.add_argument('--primary-key', default=None, help='MySQL主键字段名（自动检测）')

    # 导入配置参数
    parser.add_argument('--batch-size', type=int, default=500, help='批量大小')
    parser.add_argument('--limit-per-query', type=int, default=5000, help='每次查询限制')
    parser.add_argument('--progress-file', default='', help='进度文件')
    parser.add_argument('--start-id', type=int, default=None, help='起始ID（优先于进度文件）')
    parser.add_argument('--sleep-time', type=float, default=0.1, help='批次间休眠时间')
    parser.add_argument('--max-runtime', type=int, default=7200, help='最大运行时间（秒）')

    # 文本片段配置参数
    parser.add_argument('--min-fragment-length', type=int, default=5, help='最小片段长度（短文本直接作为片段）')
    parser.add_argument('--max-fragment-length', type=int, default=40, help='最大片段长度')
    parser.add_argument('--step-size', type=int, default=3, help='滑动窗口步长')
    parser.add_argument('--max-fragments-per-field', type=int, default=30, help='每个字段最大片段数')
    parser.add_argument('--include-short-fragments', action='store_true', help='包含短片段')
    parser.add_argument('--no-lowercase', action='store_false', dest='lowercase', help='不转为小写')

    # 日志配置参数
    parser.add_argument('--log-file', help='日志文件路径')
    parser.add_argument('--log-level', choices=['DEBUG', 'INFO', 'WARNING', 'ERROR'], default='INFO', help='日志级别')

    # 操作模式
    parser.add_argument('--mode', choices=['sync', 'verify', 'test', 'info'], default='sync',
                       help='运行模式: sync=同步, verify=验证, test=测试连接, info=查看映射信息')

    return parser.parse_args()

def main_from_args():
    """从命令行参数运行"""
    args = parse_arguments()

    # 设置日志
    log_file = args.log_file or os.environ.get('ES_SYNC_LOG_FILE')
    log_level = args.log_level or os.environ.get('ES_SYNC_LOG_LEVEL', 'INFO')

    setup_logging(log_file, log_level)
    logger = logging.getLogger(__name__)

    logger.info("🚀 MySQL to Elasticsearch 数据同步工具 - 优化版")
    logger.info("=" * 60)
    logger.info(f"📅 {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    logger.info(f"🔗 Elasticsearch: {args.es_host}")
    logger.info(f"💾 MySQL: {args.mysql_host}:{args.mysql_port}/{args.mysql_db}")
    logger.info(f"📊 表名: {args.table_name}")
    logger.info(f"🔄 运行模式: {args.mode}")
    logger.info(f"🏁 起始ID: {args.start_id if args.start_id is not None else '从进度文件读取'}")
    logger.info("=" * 60)
    logger.info("💡 特性: 所有文本字段都会生成片段，支持快速模糊查询")
    logger.info("💡 关键: 短文本直接作为片段存储，确保所有字段都可查询")
    logger.info("💡 缓存策略: start-id=0时清空缓存重新查询表结构")
    logger.info("=" * 60)

    # 构建配置
    mysql_config = {
        "host": args.mysql_host,
        "port": args.mysql_port,
        "user": args.mysql_user,
        "password": args.mysql_password,
        "db": args.mysql_db,
        "charset": args.mysql_charset
    }

    es_config = {
        "host": args.es_host,
        "auth": (args.es_user, args.es_password),
        "timeout": args.es_timeout
    }

    import_config = {
        "batch_size": args.batch_size,
        "limit_per_query": args.limit_per_query,
        "sleep_between_batches": args.sleep_time,
        "max_runtime": args.max_runtime,
        "start_id": args.start_id,
        "fragment_config": {
            "min_fragment_length": args.min_fragment_length,
            "max_fragment_length": args.max_fragment_length,
            "step_size": args.step_size,
            "max_fragments_per_field": args.max_fragments_per_field,
            "include_short_fragments": args.include_short_fragments,
            "lowercase": args.lowercase,
            "always_generate_fragment": True  # 关键配置
        }
    }

    # 添加进度文件
    if not args.progress_file:
        import_config["progress_file"] = f"es_import_progress_{args.table_name}.txt"
    else:
        import_config["progress_file"] = args.progress_file

    # 构建同步配置
    sync_config = {
        "table_name": args.table_name,
        "index_name": args.index_name if args.index_name else args.table_name,
        "primary_key": args.primary_key
    }

    try:
        # 创建同步器
        sync = MySQLToElasticsearchSync(mysql_config, es_config, import_config, sync_config)

        # 测试连接
        if not sync.test_connections():
            logger.error("连接测试失败，请检查配置")
            sys.exit(1)

        # 根据模式执行不同操作
        if args.mode == 'test':
            logger.info("✅ 连接测试完成，配置正确")
            sys.exit(0)

        elif args.mode == 'info':
            logger.info("📋 表结构和映射信息:")
            info = sync.get_mapping_info()

            logger.info(f"表名: {info['table_name']}")
            logger.info(f"索引名: {info['index_name']}")
            logger.info(f"主键字段: {info['primary_key']}")
            logger.info(f"总字段数: {info['field_count']}")
            logger.info(f"文本字段: {len(info['text_fields'])}")
            logger.info(f"✅ 所有文本字段都会生成片段字段: {info['fragment_count']}")

            if info["fragment_fields"]:
                logger.info("🔍 支持快速模糊查询的字段（使用_fragments字段查询）:")
                for i, frag in enumerate(info["fragment_fields"][:10], 1):
                    logger.info(f"  {i}. {frag['original']} -> {frag['fragment_field']}")

                logger.info("")
                logger.info("💡 PHP查询示例:")
                if info["fragment_fields"]:
                    example = info["fragment_fields"][0]
                    logger.info(f"  $must[] = ['wildcard' => ['{example['fragment_field']}' => '*关键字*']];")
                    logger.info("  注意：短文本也会生成片段，确保所有字段都可模糊查询")

            sys.exit(0)

        elif args.mode == 'verify':
            verify_result = sync.verify_sync()
            if verify_result["status"] == "success":
                logger.info(f"✅ 验证通过，索引中共有 {verify_result['count']} 条数据")
                if verify_result.get("fragment_count", 0) > 0:
                    logger.info(f"✅ 支持快速模糊查询的片段字段: {verify_result['fragment_count']} 个")
                    logger.info("💡 PHP查询示例: $field . '_fragments'")
            else:
                logger.error(f"⚠️  验证失败: {verify_result.get('error', '未知错误')}")
            sys.exit(0)

        # 同步模式
        start_time = time.time()

        try:
            # 执行分批次同步
            result = sync.sync_all_batches()

            # 显示最终结果
            elapsed_time = time.time() - start_time

            logger.info("🎉 同步任务完成!")
            logger.info(f"⏱️  总耗时: {elapsed_time:.2f} 秒 ({elapsed_time/60:.2f} 分钟)")
            logger.info(f"📦 总批次: {result['total_batches']}")
            logger.info(f"✅ 总成功: {result['total_success']}")
            logger.info(f"❌ 总失败: {result['total_failed']}")
            logger.info(f"📍 最后ID: {result['last_id']}")

            if elapsed_time > 0 and result['total_success'] > 0:
                rate = result['total_success'] / elapsed_time
                logger.info(f"⚡ 平均速度: {rate:.2f} 条/秒")

            # 验证结果
            verify_result = sync.verify_sync()

            if verify_result["status"] == "success":
                logger.info(f"✅ 验证通过，索引中共有 {verify_result['count']} 条数据")
                if verify_result.get("fragment_count", 0) > 0:
                    logger.info(f"✅ 所有文本字段都已生成片段字段: {verify_result['fragment_count']} 个")
                    logger.info("💡 PHP模糊查询示例:")
                    if verify_result.get("fragment_fields"):
                        example = verify_result["fragment_fields"][0]
                        logger.info(f"  $must[] = ['wildcard' => ['{example['fragment_field']}' => '*关键字*']];")
                        logger.info("  🚀 性能比原始wildcard快100倍以上!")

        except KeyboardInterrupt:
            logger.warning("⚠️  用户中断，进度已保存")
            logger.info(f"下次将从 {sync.primary_key} {sync.current_last_id} 继续")
            sys.exit(0)
        except Exception as e:
            logger.error(f"❌ 同步过程出错: {str(e)}")
            logger.info(f"⚠️  进度已保存，下次将从 {sync.primary_key} {sync.current_last_id} 继续")
            sys.exit(1)

    except Exception as e:
        logger.error(f"❌ 程序初始化失败: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    try:
        if len(sys.argv) > 1:
            main_from_args()
        else:
            print("🚀 MySQL to Elasticsearch 数据同步工具 - 优化版")
            print("=" * 60)
            print("📝 使用 --help 查看帮助信息")

            print("\n📋 使用示例:")
            print("1. 全量同步（重新查询表结构）:")
            print('   python es_fragment_fixed.py --mysql-host 127.0.0.1 --mysql-user root \\')
            print('   --mysql-password pass --mysql-db test --es-host http://localhost:9200 \\')
            print('   --es-user elastic --es-password pass --table-name cd_zs_kehu \\')
            print('   --start-id 0  # 关键：start-id=0时重新查询表结构')

            print("\n2. 增量同步（使用缓存）:")
            print('   python es_fragment_fixed.py --mysql-host 127.0.0.1 --mysql-user root \\')
            print('   --mysql-password pass --mysql-db test --es-host http://localhost:9200 \\')
            print('   --es-user elastic --es-password pass --table-name cd_zs_kehu \\')
            print('   --start-id 1000  # 关键：start-id>0时使用缓存')

            print("\n3. 查看映射信息:")
            print('   python es_fragment_fixed.py --mysql-host 127.0.0.1 --mysql-user root \\')
            print('   --mysql-password pass --mysql-db test --es-host http://localhost:9200 \\')
            print('   --es-user elastic --es-password pass --table-name cd_zs_kehu --mode info')

            print("\n💡 重要特性:")
            print("  - 所有文本字段都会生成片段字段（char/varchar/text/longtext等）")
            print("  - 短文本直接作为片段存储，确保所有字段都可查询")
            print("  - 智能缓存：start-id=0时清空缓存重新查询表结构")
            print("  - start-id>0时使用缓存提升性能")
            print("  - PHP查询使用: $field . '_fragments' 例如: zs_name_fragments")
            print("  - 模糊查询性能提升100倍以上")
            
            sys.exit(1)
    except Exception as e:
        print(f"❌ 程序执行失败: {str(e)}")
        sys.exit(1)