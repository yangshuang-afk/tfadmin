<?php

namespace app\admin\controller\Sys;

use think\exception\ValidateException;
use app\admin\controller\Sys\model\Field;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Admin;
use think\facade\Db;

class CreateMysql extends Admin
{
    //生成
    public function create()
    {
        $menu_id = $this->request->post('menu_id');
        $menuInfo = Menu::find($menu_id);
        if (empty($menuInfo)) {
            throw new ValidateException('菜单不存在');
        }
        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
        $prefix = config('database.connections.'.$connect.'.prefix');
        $tableName = $prefix.$menuInfo['table_name'];

        if(config('database.connections.'.$connect.'.type') <> 'mysql'){
            throw new ValidateException('ai创建字段方法暂适用于mysql');
        }

        $fields = Db::name('field')->where('menu_id',$menu_id)->select()->toArray();

        foreach ($fields as &$field) {
            if (!in_array($field['datatype'],['varchar','int','smallint','text','decimal','tinyint','datetime','longtext'])) {
                $field['datatype'] = ['varchar','int','smallint','text','decimal','tinyint','datetime','longtext'][$field['datatype']-1];
            }

            if (!in_array($field['datatype'],['varchar','int','smallint','text','longtext','decimal','tinyint','datetime'])) {
                throw new ValidateException('ai创建字段类型有误：'.$field['datatype']);
            }
        }

        foreach ($fields as $data) {
            if ($menuInfo['pk'] == $data['field']) {
                continue;
            }
            if($data['create_table_field']){
                if($menuInfo['page_type'] == 1){
                    $fieldName = $data['field'];
                    $columnInfo = self::getTableColumn($tableName, $fieldName, $connect);
                    [$originDatatype, $originLength] = self::parseTypeAndLength($columnInfo['Type'] ?? '');
                    $datatype = !empty($data['datatype']) ? strtolower((string)$data['datatype']) : $originDatatype;
                    $length = $data['length'] ?? $originLength;
                    $lengthSql = self::buildLengthSql($datatype, $length);

                    $hasIndexInput = array_key_exists('indexdata', $data);
                    $requestedIndexData = $hasIndexInput ? self::normalizeIndexValue($data['indexdata']) : '';
                    $indexData = $hasIndexInput ? $requestedIndexData : self::normalizeIndexValue($data['indexdata'] ?? '');
                    $columnKey = strtoupper($columnInfo['Key'] ?? '');
                    $currentHasIndex = !empty(self::getTableIndexDetailByField($tableName, $fieldName, $connect)) || in_array($columnKey, ['UNI', 'MUL'], true);
                    $currentIsNotNull = strtoupper($columnInfo['Null'] ?? '') === 'NO';
                    $shouldKeepIndex = $hasIndexInput ? ($requestedIndexData !== '') : (!empty($indexData) || $currentHasIndex);
                    // 强制规则：索引字段必须非空；取消索引后改为可空
                    if ($hasIndexInput) {
                        $isNotNull = $requestedIndexData !== '';
                    } else {
                        $isNotNull = $shouldKeepIndex || $currentIsNotNull;
                    }

                    [$hasDefault, $defaultValue] = self::resolveDefault($data, $columnInfo);
                    $defaultSql = self::buildDefaultSql($hasDefault, $defaultValue, $isNotNull);
                    $nullSql = $isNotNull ? 'NOT NULL' : 'NULL';
                    if ($isNotNull) {
                        $fallbackDefault = $hasDefault ? $defaultValue : self::inferNotNullFallback($datatype, (int)($data['type'] ?? 0));
                        if (empty($columnInfo)) {
                            if (!$hasDefault) {
                                $defaultSql = 'DEFAULT '.self::formatDefaultValue($fallbackDefault);
                            }
                        } else {
                            self::fillNullValues($tableName, $fieldName, $fallbackDefault, $connect);
                        }
                    }

                    $comment = addslashes(self::getDescription($data));
                    if(empty($columnInfo)){
                        $sql = "ALTER TABLE `{$tableName}` ADD `{$fieldName}` {$datatype}{$lengthSql} COMMENT '{$comment}' {$nullSql} {$defaultSql}";
                    } else {
                        $sql = "ALTER TABLE `{$tableName}` MODIFY COLUMN `{$fieldName}` {$datatype}{$lengthSql} COMMENT '{$comment}' {$nullSql} {$defaultSql}";
                    }

                    Db::connect($connect)->execute(trim($sql));

                    if ($hasIndexInput && $requestedIndexData === '') {
                        self::dropFieldIndexes($tableName, $fieldName, $connect);
                    }
                    $indexKeyword = self::buildIndexKeyword($indexData);
                    if($shouldKeepIndex && $indexKeyword !== '' && !self::getTableIndex($tableName, $fieldName, $connect)){
                        Db::connect($connect)->execute("ALTER TABLE `{$tableName}` ADD {$indexKeyword} (`{$fieldName}`)");
                    }
                    self::syncFieldMetaById((int)$data['id'], $tableName, $fieldName, $connect);
                }
                if($menuInfo['page_type'] == 2 && $data['type'] == 30){
                    $fieldName = $data['field'];
                    $columnInfo = self::getTableColumn($tableName, $fieldName, $connect);
                    $hasIndexInput = array_key_exists('indexdata', $data);
                    $requestedIndexData = $hasIndexInput ? self::normalizeIndexValue($data['indexdata']) : '';
                    $indexData = $hasIndexInput ? $requestedIndexData : self::normalizeIndexValue($data['indexdata'] ?? '');
                    $columnKey = strtoupper($columnInfo['Key'] ?? '');
                    $currentHasIndex = !empty(self::getTableIndexDetailByField($tableName, $fieldName, $connect)) || in_array($columnKey, ['UNI', 'MUL'], true);
                    $currentIsNotNull = strtoupper($columnInfo['Null'] ?? '') === 'NO';
                    $shouldKeepIndex = $hasIndexInput ? ($requestedIndexData !== '') : (!empty($indexData) || $currentHasIndex);
                    if ($hasIndexInput) {
                        $isNotNull = $requestedIndexData !== '';
                    } else {
                        $isNotNull = $shouldKeepIndex || $currentIsNotNull;
                    }
                    [$hasDefault, $defaultValue] = self::resolveDefault($data, $columnInfo);
                    $defaultSql = self::buildDefaultSql($hasDefault, $defaultValue, $isNotNull);
                    $nullSql = $isNotNull ? 'NOT NULL' : 'NULL';
                    if ($isNotNull) {
                        $fallbackDefault = $hasDefault ? $defaultValue : self::inferNotNullFallback('varchar', (int)($data['type'] ?? 0));
                        if (empty($columnInfo)) {
                            if (!$hasDefault) {
                                $defaultSql = 'DEFAULT '.self::formatDefaultValue($fallbackDefault);
                            }
                        } else {
                            self::fillNullValues($tableName, $fieldName, $fallbackDefault, $connect);
                        }
                    }

                    if(empty($columnInfo)){
                        Db::connect($connect)->execute("ALTER TABLE `{$tableName}` ADD `{$fieldName}` VARCHAR(50) {$nullSql} {$defaultSql}");
                    } else {
                        Db::connect($connect)->execute("ALTER TABLE `{$tableName}` MODIFY COLUMN `{$fieldName}` VARCHAR(50) {$nullSql} {$defaultSql}");
                    }
                    if ($hasIndexInput && $requestedIndexData === '') {
                        self::dropFieldIndexes($tableName, $fieldName, $connect);
                    }
                    $indexKeyword = self::buildIndexKeyword($indexData);
                    if($shouldKeepIndex && $indexKeyword !== '' && !self::getTableIndex($tableName, $fieldName, $connect)){
                        Db::connect($connect)->execute("ALTER TABLE `{$tableName}` ADD {$indexKeyword} (`{$fieldName}`)");
                    }
                    self::syncFieldMetaById((int)$data['id'], $tableName, $fieldName, $connect);
                }
            }
        }

        return json(['status' => 200]);
    }


    private function getDescription($val){
        $description = '';
        if(in_array($val['type'],[2,3,4,5,6]) && !empty($val['item_config'])){
            if(is_array($val['item_config'])){
                foreach($val['item_config'] as $v){
                    $description .= $v['key'].'-'.$v['val'].' ; ';
                }
            }
        }
        return rtrim($val['title'].' , '.$description,' , ');
    }

    private static function getTableColumn($tablename, $field, $connect){
        if (empty($field)) {
            return [];
        }
        $field = addslashes($field);
        $list = Db::connect($connect)->query("SHOW FULL COLUMNS FROM `{$tablename}` LIKE '{$field}'");
        return $list[0] ?? [];
    }

    private static function parseTypeAndLength(string $columnType): array
    {
        $columnType = strtolower($columnType);
        if (preg_match('/^([a-z0-9_]+)(?:\(([^)]*)\))?/i', $columnType, $match)) {
            return [$match[1], $match[2] ?? ''];
        }
        return ['varchar', '255'];
    }

    private static function buildLengthSql(string $datatype, $length): string
    {
        $datatype = strtolower($datatype);
        if (in_array($datatype, ['datetime', 'date', 'timestamp', 'longtext', 'text'], true)) {
            return '';
        }

        if ($length === null || $length === '') {
            if (in_array($datatype, ['varchar', 'char'], true)) {
                $length = '255';
            } elseif (in_array($datatype, ['int', 'tinyint', 'smallint', 'bigint'], true)) {
                $length = '11';
            }
        }

        return ($length === null || $length === '') ? '' : "({$length})";
    }

    private static function resolveDefault(array $data, array $columnInfo): array
    {
        $columnDefault = array_key_exists('Default', $columnInfo) ? $columnInfo['Default'] : null;
        $inputDefault = $data['default_value'] ?? null;

        if ($inputDefault === '' || $inputDefault === null) {
            if ($columnDefault !== null || $columnDefault === '') {
                return [true, $columnDefault];
            }
            return [false, null];
        }

        if ((int)($data['type'] ?? 0) === 13) {
            return [true, '0'];
        }

        return [true, (string)$inputDefault];
    }

    private static function buildDefaultSql(bool $hasDefault, $defaultValue, bool $isNotNull): string
    {
        if ($hasDefault) {
            return 'DEFAULT '.self::formatDefaultValue($defaultValue);
        }
        return $isNotNull ? '' : 'DEFAULT NULL';
    }

    private static function formatDefaultValue($defaultValue): string
    {
        if ($defaultValue === null) {
            return 'NULL';
        }
        $defaultValue = (string)$defaultValue;
        $upper = strtoupper(trim($defaultValue));
        if ($upper === 'NULL') {
            return 'NULL';
        }
        if (in_array($upper, ['CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP()'], true)) {
            return $upper;
        }
        $defaultValue = str_replace(['\\', '\''], ['\\\\', '\\\''], $defaultValue);
        return "'{$defaultValue}'";
    }

    // 统一索引值存储格式：index/unique
    private static function normalizeIndexValue($indexData): string
    {
        $value = strtolower(trim((string)$indexData));
        return in_array($value, ['index', 'unique'], true) ? $value : '';
    }

    // SQL 索引关键字映射
    private static function buildIndexKeyword($indexData): string
    {
        $value = self::normalizeIndexValue($indexData);
        if ($value === 'unique') {
            return 'UNIQUE';
        }
        if ($value === 'index') {
            return 'INDEX';
        }
        return '';
    }

    // NOT NULL 且默认值为空时使用的兜底值
    private static function inferNotNullFallback(string $datatype, int $fieldType = 0)
    {
        if ($fieldType === 13) {
            return '0';
        }
        $datatype = strtolower($datatype);
        if (in_array($datatype, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real', 'bit'], true)) {
            return '0';
        }
        if ($datatype === 'date') {
            return '1970-01-01';
        }
        if (in_array($datatype, ['datetime', 'timestamp'], true)) {
            return '1970-01-01 00:00:00';
        }
        if ($datatype === 'time') {
            return '00:00:00';
        }
        if ($datatype === 'year') {
            return '1970';
        }
        if ($datatype === 'json') {
            return '{}';
        }
        return '';
    }

    // 将历史 NULL 数据更新为兜底值，再执行 NOT NULL 变更，避免 1138
    private static function fillNullValues(string $tablename, string $field, $fillValue, string $connect): void
    {
        if (empty($field)) {
            return;
        }
        $fillSql = self::formatDefaultValue($fillValue);
        Db::connect($connect)->execute("UPDATE `{$tablename}` SET `{$field}` = {$fillSql} WHERE `{$field}` IS NULL");
    }

    private static function getTableIndex($tablename, $indexName, $connect): bool
    {
        if (empty($indexName)) {
            return false;
        }
        $list = Db::connect($connect)->query('show index from '.$tablename);
        foreach($list as $v){
            if($v['Column_name'] == $indexName){
                return true;
            }
        }
        return false;
    }

    // 获取字段索引明细（不含主键）
    private static function getTableIndexDetailByField(string $tablename, string $field, string $connect): array
    {
        if (empty($field)) {
            return [];
        }
        $list = Db::connect($connect)->query('show index from '.$tablename);
        $result = [];
        foreach ($list as $row) {
            if (($row['Column_name'] ?? '') === $field && ($row['Key_name'] ?? '') !== 'PRIMARY') {
                $result[] = $row;
            }
        }
        return $result;
    }

    // 删除字段关联的所有非主键索引
    private static function dropFieldIndexes(string $tablename, string $field, string $connect): void
    {
        $indexes = self::getTableIndexDetailByField($tablename, $field, $connect);
        if (empty($indexes)) {
            return;
        }
        $keyNames = [];
        foreach ($indexes as $indexRow) {
            $keyName = $indexRow['Key_name'] ?? '';
            if ($keyName !== '') {
                $keyNames[$keyName] = true;
            }
        }
        foreach (array_keys($keyNames) as $keyName) {
            Db::connect($connect)->execute("ALTER TABLE `{$tablename}` DROP INDEX `{$keyName}`");
        }
    }

    // 根据真实表结构反推 default_value/indexdata 到 cd_field
    private static function syncFieldMetaById(int $fieldId, string $tableName, string $fieldName, string $connect): void
    {
        $columnInfo = self::getTableColumn($tableName, $fieldName, $connect);
        if (empty($columnInfo)) {
            return;
        }
        $isNotNull = strtoupper($columnInfo['Null'] ?? '') === 'NO';
        $indexRows = self::getTableIndexDetailByField($tableName, $fieldName, $connect);
        $indexData = '';
        foreach ($indexRows as $indexRow) {
            if ((int)($indexRow['Non_unique'] ?? 1) === 0) {
                $indexData = 'unique';
                break;
            }
            $indexData = 'index';
        }
        $currentValidate = Field::where('id', $fieldId)->value('validate');
        $validate = self::syncValidateByNotNull($currentValidate, $isNotNull);
        Field::where('id', $fieldId)->update([
            'default_value' => array_key_exists('Default', $columnInfo) ? $columnInfo['Default'] : null,
            'indexdata' => $indexData,
            'validate' => $validate
        ]);
    }

    // 根据真实字段是否 NOT NULL 同步 notempty 规则，保证字段配置与表结构一致
    private static function syncValidateByNotNull($currentValidate, bool $isNotNull): string
    {
        $validateList = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$currentValidate)), function ($rule) {
            return $rule !== '';
        })));
        if ($isNotNull) {
            if (!in_array('notempty', $validateList, true)) {
                $validateList[] = 'notempty';
            }
        } else {
            $validateList = array_values(array_filter($validateList, function ($rule) {
                return $rule !== 'notempty';
            }));
        }
        return implode(',', $validateList);
    }

}
