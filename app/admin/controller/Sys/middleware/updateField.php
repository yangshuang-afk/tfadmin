<?php
namespace app\admin\controller\Sys\middleware;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Sys\model\Field;
use app\admin\controller\Admin;
use think\exception\ValidateException;
use think\facade\Db;

class updateField extends Admin
{

    public function handle($request, \Closure $next)
    {
        $data = $request->param();

        $menuId = $data['menu_id'] ?? null;
        if (empty($menuId)) {
            return $next($request);
        }

        $menuInfo = Menu::find($menuId);
        if (empty($menuInfo)) {
            return $next($request);
        }

        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
        $prefix = config('database.connections.'.$connect.'.prefix');
        $tableName = $prefix.$menuInfo['table_name'];
        $dbType = config('database.connections.'.$connect.'.type');
        $syncFieldId = null;
        $syncFieldName = null;

        if($dbType === 'mysql' && (int)($data['create_table_field'] ?? 0) === 1){
            $type = (int)($data['type'] ?? 0);
            if($menuInfo['page_type'] == 1 || ($menuInfo['page_type'] == 2 && $type == 30)){
                $fieldId = $data['id'] ?? null;
                $fieldInfo = empty($fieldId) ? null : Field::find($fieldId);
                if (!empty($fieldInfo)) {
                    $oldField = $fieldInfo['field'];
                    $newField = !empty($data['field']) ? $data['field'] : $oldField;

                    $columnInfo = self::getTableColumn($tableName, $oldField, $connect);
                    if (empty($columnInfo) && $newField !== $oldField) {
                        $columnInfo = self::getTableColumn($tableName, $newField, $connect);
                    }

                    $columnType = $columnInfo['Type'] ?? '';
                    [$originDatatype, $originLength] = self::parseTypeAndLength($columnType);

                    $datatype = !empty($data['datatype']) ? strtolower((string)$data['datatype']) : (!empty($fieldInfo['datatype']) ? strtolower((string)$fieldInfo['datatype']) : $originDatatype);
                    $length = $data['length'] ?? ($fieldInfo['length'] ?? $originLength);
                    $lengthSql = self::buildLengthSql($datatype, $length);

                    $pkId = Db::connect($connect)->name($menuInfo['table_name'])->getPk();
                    $isPrimaryKeyField = ($oldField === $pkId);

                    $hasIndexInput = array_key_exists('indexdata', $data);
                    $requestedIndexData = $hasIndexInput ? self::normalizeIndexValue($data['indexdata']) : '';
                    $columnKey = strtoupper($columnInfo['Key'] ?? '');
                    $currentIndexes = self::getTableIndexDetailByField($tableName, $oldField, $connect);
                    if ($newField !== $oldField) {
                        $currentIndexes = array_merge($currentIndexes, self::getTableIndexDetailByField($tableName, $newField, $connect));
                    }
                    $currentHasIndex = !empty($currentIndexes) || in_array($columnKey, ['UNI', 'MUL'], true);
                    $currentIsNotNull = strtoupper($columnInfo['Null'] ?? '') === 'NO';
                    $indexData = $hasIndexInput ? $requestedIndexData : self::normalizeIndexValue($fieldInfo['indexdata'] ?? '');
                    $shouldKeepIndex = $hasIndexInput ? ($requestedIndexData !== '') : (!empty($indexData) || $currentHasIndex);

                    // 强制规则：索引字段必须非空；取消索引后改为可空（主键除外）
                    if ($isPrimaryKeyField) {
                        $isNotNull = true;
                    } elseif ($hasIndexInput) {
                        $isNotNull = $requestedIndexData !== '';
                    } else {
                        $isNotNull = $shouldKeepIndex || $currentIsNotNull;
                    }

                    [$hasDefault, $defaultValue] = self::resolveDefault($data, $fieldInfo, $columnInfo);
                    $defaultSql = self::buildDefaultSql($hasDefault, $defaultValue, $isNotNull);
                    $nullSql = $isNotNull ? 'NOT NULL' : 'NULL';

                    $auto = '';
                    $primaryKey = '';
                    if($isPrimaryKeyField){
                        $auto = 'AUTO_INCREMENT';
                        $nullSql = 'NOT NULL';
                        $defaultSql = '';
                        $primaryKey = 'PRIMARY KEY';
                        Menu::field('pk')->where('menu_id',$fieldInfo['menu_id'])->update(['pk'=>$newField]);
                    }

                    $fields = self::getFieldS($tableName, $connect);
                    $commentData = $data;
                    if (empty($commentData['title']) && !empty($fieldInfo['title'])) {
                        $commentData['title'] = $fieldInfo['title'];
                    }
                    $comment = addslashes(self::getDescription($commentData));

                    $currentField = in_array($oldField, $fields, true) ? $oldField : $newField;
                    $fieldExists = in_array($currentField, $fields, true);
                    if ($isNotNull && !$isPrimaryKeyField) {
                        self::guardNotNullChange($tableName, $currentField, $fieldExists, $hasDefault, $defaultValue, $connect);
                        if ($fieldExists && $hasDefault) {
                            self::fillNullValues($tableName, $currentField, $defaultValue, $connect);
                        }
                    }
                    if($fieldExists){
                        $sql="ALTER TABLE `{$tableName}` CHANGE `{$currentField}` `{$newField}` {$datatype}{$lengthSql} COMMENT '{$comment}' {$nullSql} {$defaultSql} {$auto}";
                    }else{
                        $sql="ALTER TABLE `{$tableName}` ADD `{$newField}` {$datatype}{$lengthSql} COMMENT '{$comment}' {$nullSql} {$defaultSql} {$auto} {$primaryKey}";
                    }

                    Db::connect($connect)->execute(trim($sql));

                    // 索引联动：取消索引时删除索引；设置索引时确保存在
                    if (!$isPrimaryKeyField && $hasIndexInput && $requestedIndexData === '') {
                        self::dropFieldIndexes($tableName, $newField, $connect);
                        if ($newField !== $oldField) {
                            self::dropFieldIndexes($tableName, $oldField, $connect);
                        }
                    }
                    $indexKeyword = self::buildIndexKeyword($indexData);
                    if(!$isPrimaryKeyField && $shouldKeepIndex && $indexKeyword !== '' && !self::getTableIndex($tableName, $newField, $connect)){
                        Db::connect($connect)->execute("ALTER TABLE `{$tableName}` ADD {$indexKeyword} (`{$newField}`)");
                    }

                    $syncFieldId = $fieldId;
                    $syncFieldName = $newField;
                }
            }
        }

        $response = $next($request);

        if (!empty($syncFieldId) && !empty($syncFieldName)) {
            self::syncFieldMetaById((int)$syncFieldId, $tableName, $syncFieldName, $connect);
        }

        return $response;
    }

    //判断数据表字段是否存在
    public static function getFieldS($tablename,$connect){
        $arr = [];
        $list = Db::connect($connect)->query('show columns from '.$tablename);
        foreach($list as $v){
            $arr[] = $v['Field'];
        }
        return $arr;
    }


    //查看索引是否存在
    public static function getTableIndex($tablename,$indexName,$connect){
        $status = false;
        $list = Db::connect($connect)->query('show index from '.$tablename);
        foreach($list as $k=>$v){
            if($v['Column_name'] == $indexName){
                $status = true;
            }
        }
        return $status;
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
            try {
                Db::connect($connect)->execute("ALTER TABLE `{$tablename}` DROP INDEX `{$keyName}`");
            } catch (\Throwable $e) {
                if (!self::isDropIndexMissingException($e)) {
                    throw $e;
                }
            }
        }
    }

    // 获取表字段详情
    private static function getTableColumn($tablename, $field, $connect){
        if (empty($field)) {
            return [];
        }
        $field = addslashes($field);
        $list = Db::connect($connect)->query("SHOW FULL COLUMNS FROM `{$tablename}` LIKE '{$field}'");
        return $list[0] ?? [];
    }

    // 从字段类型文本中拆分 datatype 和 length
    private static function parseTypeAndLength(string $columnType): array
    {
        $columnType = strtolower($columnType);
        if (preg_match('/^([a-z0-9_]+)(?:\(([^)]*)\))?/i', $columnType, $match)) {
            return [$match[1], $match[2] ?? ''];
        }
        return ['varchar', '255'];
    }

    // 生成长度片段
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

    // 计算默认值：请求为空时优先回退真实表结构
    private static function resolveDefault(array $data, $fieldInfo, array $columnInfo): array
    {
        $columnDefault = array_key_exists('Default', $columnInfo) ? $columnInfo['Default'] : null;
        $fieldDefault = $fieldInfo['default_value'] ?? null;
        $hasInput = array_key_exists('default_value', $data);

        if ($hasInput) {
            $inputDefault = $data['default_value'];
            if ($inputDefault === '' || $inputDefault === null) {
                if ($columnDefault !== null || $columnDefault === '') {
                    return [true, $columnDefault];
                }
                if ($fieldDefault !== null && $fieldDefault !== '') {
                    return [true, $fieldDefault];
                }
                return [false, null];
            }
            // 兼容历史逻辑：开关字段默认强制 0
            if ((int)($data['type'] ?? 0) === 13) {
                return [true, '0'];
            }
            return [true, (string)$inputDefault];
        }

        if ($columnDefault !== null || $columnDefault === '') {
            return [true, $columnDefault];
        }
        if ($fieldDefault !== null && $fieldDefault !== '') {
            return [true, $fieldDefault];
        }

        return [false, null];
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

    // 使用明确默认值填充历史 NULL，再执行 NOT NULL 变更，避免 1138
    private static function fillNullValues(string $tablename, string $field, $fillValue, string $connect): void
    {
        if (empty($field)) {
            return;
        }
        $fillSql = self::formatDefaultValue($fillValue);
        Db::connect($connect)->execute("UPDATE `{$tablename}` SET `{$field}` = {$fillSql} WHERE `{$field}` IS NULL");
    }

    // NOT NULL 变更前置校验：无法确定默认值时直接报错，避免错误写入
    private static function guardNotNullChange(string $tablename, string $field, bool $fieldExists, bool $hasDefault, $defaultValue, string $connect): void
    {
        if ($hasDefault && $defaultValue === null) {
            throw new ValidateException("字段[{$field}]设置为NOT NULL时，默认值不能为NULL，请先设置有效默认值");
        }

        if ($fieldExists) {
            $nullCount = self::countFieldNullRows($tablename, $field, $connect);
            if ($nullCount > 0 && !$hasDefault) {
                throw new ValidateException("字段[{$field}]存在{$nullCount}条NULL数据，且未提供默认值，无法改为NOT NULL，请先修复数据或设置默认值");
            }
            return;
        }

        if (!$hasDefault && self::countTableRows($tablename, $connect) > 0) {
            throw new ValidateException("新增字段[{$field}]设置为NOT NULL时未提供默认值，且表中已有数据，无法自动确定默认值，请先设置默认值");
        }
    }

    private static function countFieldNullRows(string $tablename, string $field, string $connect): int
    {
        $res = Db::connect($connect)->query("SELECT COUNT(*) AS c FROM `{$tablename}` WHERE `{$field}` IS NULL");
        return (int)($res[0]['c'] ?? 0);
    }

    private static function countTableRows(string $tablename, string $connect): int
    {
        $res = Db::connect($connect)->query("SELECT COUNT(*) AS c FROM `{$tablename}`");
        return (int)($res[0]['c'] ?? 0);
    }

    private static function isDropIndexMissingException(\Throwable $e): bool
    {
        $message = $e->getMessage();
        return strpos($message, '1091') !== false || stripos($message, "Can't DROP") !== false;
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

}
