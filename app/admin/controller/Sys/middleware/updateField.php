<?php
namespace app\admin\controller\Sys\middleware;
use think\exception\ValidateException;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Sys\model\Field;
use app\admin\controller\Admin;
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
        
        if(config('database.connections.'.$connect.'.type') <> 'mysql'){
            return $next($request);
        }
        
        if($data['create_table_field']){
            if($menuInfo['page_type'] == 1 || ($menuInfo['page_type'] == 2 && $data['type'] == 30)){
                $fieldInfo = Field::find($data['id']);
                if(empty($fieldInfo)){
                    return $next($request);
                }

                $oldField = $fieldInfo['field'];
                $newField = !empty($data['field']) ? $data['field'] : $oldField;
                $columnInfo = self::getTableColumn($tableName,$oldField,$connect);
                if(empty($columnInfo) && $newField !== $oldField){
                    $columnInfo = self::getTableColumn($tableName,$newField,$connect);
                }

                $tableIndexData = self::getFieldIndexType($tableName,$oldField,$connect);
                if($tableIndexData === '' && $newField !== $oldField){
                    $tableIndexData = self::getFieldIndexType($tableName,$newField,$connect);
                }
                $tableIsNotNull = strtoupper($columnInfo['Null'] ?? '') === 'NO';
                $tableHasDefault = self::columnHasDefault($columnInfo);
                $tableDefaultValue = $tableHasDefault ? $columnInfo['Default'] : null;

                $smoothedMeta = self::smoothFieldMeta($data,$tableDefaultValue,$tableHasDefault,$tableIndexData,$tableIsNotNull);
                $data['default_value'] = $smoothedMeta['default_value'];
                $data['indexdata'] = $smoothedMeta['indexdata'];
                $data['validate'] = $smoothedMeta['validate'];
                $request->withPost($data);

                $pk_id = Db::connect($connect)->name($menuInfo['table_name'])->getPk();
                $primary_key = '';
                $isNotNull = $smoothedMeta['is_not_null'];
                $nullSql = $isNotNull ? 'NOT NULL' : 'NULL';
                $defaultSql = self::buildDefaultSql($smoothedMeta['has_default_for_sql'],$smoothedMeta['default_for_sql'],$isNotNull);
                
                if($fieldInfo['field'] == $pk_id){
                    $auto = 'AUTO_INCREMENT';
                    $nullSql = 'NOT NULL';
                    $defaultSql = '';
                    $primary_key = 'PRIMARY KEY';
                    
                    Menu::field('pk')->where('menu_id',$fieldInfo['menu_id'])->update(['pk'=>$newField]);
                    
                }else{
                    $auto = '';
                }

                if(!empty($columnInfo) && strtoupper($columnInfo['Null'] ?? '') !== 'NO' && $nullSql === 'NOT NULL'){
                    $checkField = ($columnInfo['Field'] ?? '') ?: $oldField;
                    self::assertNoNullValues($tableName,$checkField,$connect);
                }

                if(!isset($data['datatype']) || $data['datatype'] === ''){
                    $data['datatype'] = $fieldInfo['datatype'] ?? ($columnInfo['Type'] ?? 'varchar');
                }
                if(!isset($data['length']) || $data['length'] === ''){
                    $data['length'] = $fieldInfo['length'] ?? '';
                }
                
                if(in_array($data['datatype'],['datetime','longtext'])){
                    $data['length'] = ' null';
                }else{
                    $data['length'] = "({$data['length']})";
                }
                
                $fields = self::getFieldS($tableName,$connect);
                $commentData = $data;
                if(empty($commentData['title']) && !empty($fieldInfo['title'])){
                    $commentData['title'] = $fieldInfo['title'];
                }
                $comment = addslashes(self::getDescription($commentData));
                $currentField = in_array($oldField,$fields,true) ? $oldField : $newField;
                
                if(in_array($currentField,$fields,true)){
                    $sql="ALTER TABLE ".$tableName." CHANGE `{$currentField}` `{$newField}` {$data['datatype']}{$data['length']} COMMENT '{$comment}' {$nullSql} {$defaultSql} {$auto}";
                }else{
                    $sql="ALTER TABLE ".$tableName." ADD `{$newField}` {$data['datatype']}{$data['length']} COMMENT '{$comment}' {$nullSql} {$defaultSql} {$auto} {$primary_key}";
                }
                
                Db::connect($connect)->execute($sql);

                if($fieldInfo['field'] != $pk_id && $smoothedMeta['request_indexdata'] !== '' && self::getFieldIndexType($tableName,$newField,$connect) !== $smoothedMeta['indexdata']){
                    self::dropFieldIndexes($tableName,$newField,$connect);
                    if($newField !== $oldField){
                        self::dropFieldIndexes($tableName,$oldField,$connect);
                    }
                    Db::connect($connect)->execute("ALTER TABLE ".$tableName." ADD ".strtoupper($smoothedMeta['indexdata'])." (  `".$newField."` )");
                }
            }
        }
        
        return $next($request);
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

    private static function getTableColumn($tablename,$field,$connect){
        if(empty($field)){
            return [];
        }
        $field = addslashes($field);
        $list = Db::connect($connect)->query("SHOW FULL COLUMNS FROM `{$tablename}` LIKE '{$field}'");
        return $list[0] ?? [];
    }

    private static function getFieldIndexType($tablename,$field,$connect){
        if(empty($field)){
            return '';
        }
        $list = Db::connect($connect)->query('show index from '.$tablename);
        $indexType = '';
        foreach($list as $row){
            if(($row['Column_name'] ?? '') !== $field || ($row['Key_name'] ?? '') === 'PRIMARY'){
                continue;
            }
            if((int)($row['Non_unique'] ?? 1) === 0){
                return 'unique';
            }
            $indexType = 'index';
        }
        return $indexType;
    }

    private static function dropFieldIndexes($tablename,$field,$connect){
        if(empty($field)){
            return;
        }
        $list = Db::connect($connect)->query('show index from '.$tablename);
        $keyNames = [];
        foreach($list as $row){
            if(($row['Column_name'] ?? '') === $field && ($row['Key_name'] ?? '') !== 'PRIMARY'){
                $keyNames[$row['Key_name']] = true;
            }
        }
        foreach(array_keys($keyNames) as $keyName){
            Db::connect($connect)->execute("ALTER TABLE `{$tablename}` DROP INDEX `{$keyName}`");
        }
    }

    private static function smoothFieldMeta($data,$tableDefaultValue,$tableHasDefault,$tableIndexData,$tableIsNotNull){
        $requestIndexData = self::normalizeIndexValue($data['indexdata'] ?? '');
        $indexdata = $requestIndexData !== '' ? $requestIndexData : $tableIndexData;

        $validateList = self::parseValidateList(array_key_exists('validate',$data) ? $data['validate'] : []);
        $shouldHaveNotEmpty = $tableIsNotNull || $indexdata !== '' || in_array('notempty',$validateList,true);
        $validateList = array_values(array_filter($validateList,function($rule){
            return $rule !== 'notempty';
        }));
        if($shouldHaveNotEmpty){
            $validateList[] = 'notempty';
        }

        $hasDefaultInput = self::hasDefaultInput($data);
        if($hasDefaultInput){
            $defaultValue = self::normalizeDefaultInputValue($data);
            return [
                'default_value' => (string)$defaultValue,
                'indexdata' => $indexdata,
                'request_indexdata' => $requestIndexData,
                'validate' => $validateList,
                'is_not_null' => $shouldHaveNotEmpty,
                'has_default_for_sql' => true,
                'default_for_sql' => $defaultValue,
            ];
        }

        if($tableHasDefault){
            return [
                'default_value' => $tableDefaultValue === null ? '' : (string)$tableDefaultValue,
                'indexdata' => $indexdata,
                'request_indexdata' => $requestIndexData,
                'validate' => $validateList,
                'is_not_null' => $shouldHaveNotEmpty,
                'has_default_for_sql' => true,
                'default_for_sql' => $tableDefaultValue,
            ];
        }

        return [
            'default_value' => '',
            'indexdata' => $indexdata,
            'request_indexdata' => $requestIndexData,
            'validate' => $validateList,
            'is_not_null' => $shouldHaveNotEmpty,
            'has_default_for_sql' => false,
            'default_for_sql' => null,
        ];
    }

    private static function normalizeIndexValue($indexData){
        $indexData = strtolower(trim((string)$indexData));
        return in_array($indexData,['index','unique'],true) ? $indexData : '';
    }

    private static function parseValidateList($validate){
        if(is_array($validate)){
            $validateList = $validate;
        }else{
            $validateList = explode(',',str_replace('，',',',(string)$validate));
        }
        return array_values(array_unique(array_filter(array_map('trim',$validateList),function($rule){
            return $rule !== '';
        })));
    }

    private static function hasNotEmptyRule($validate){
        return in_array('notempty',self::parseValidateList($validate),true);
    }

    private static function hasDefaultInput($data){
        return array_key_exists('default_value',$data) && $data['default_value'] !== '' && $data['default_value'] !== null;
    }

    private static function normalizeDefaultInputValue($data){
        if((int)($data['type'] ?? 0) === 13){
            return '0';
        }
        return (string)$data['default_value'];
    }

    private static function columnHasDefault($columnInfo){
        if(!array_key_exists('Default',$columnInfo)){
            return false;
        }
        return $columnInfo['Default'] !== null || $columnInfo['Default'] === '';
    }

    private static function buildDefaultSql($hasDefault,$defaultValue,$isNotNull){
        if($hasDefault){
            return 'DEFAULT '.self::formatDefaultValue($defaultValue);
        }
        return $isNotNull ? '' : 'DEFAULT NULL';
    }

    private static function formatDefaultValue($defaultValue){
        if($defaultValue === null){
            return 'NULL';
        }
        $defaultValue = (string)$defaultValue;
        $upper = strtoupper(trim($defaultValue));
        if($upper === 'NULL'){
            return 'NULL';
        }
        if(in_array($upper,['CURRENT_TIMESTAMP','CURRENT_TIMESTAMP()'],true)){
            return $upper;
        }
        $defaultValue = str_replace(['\\','\''],['\\\\','\\\''],$defaultValue);
        return "'{$defaultValue}'";
    }

    private static function assertNoNullValues($tablename,$field,$connect){
        if(empty($field)){
            return;
        }
        $field = addslashes($field);
        $hasNull = Db::connect($connect)->query("SELECT 1 FROM `{$tablename}` WHERE `{$field}` IS NULL LIMIT 1");
        if(!empty($hasNull)){
            throw new ValidateException("字段[{$field}]存在空值，不能设置为不为空");
        }
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
