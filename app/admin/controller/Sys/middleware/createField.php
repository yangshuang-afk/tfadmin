<?php
namespace app\admin\controller\Sys\middleware;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Sys\model\Field;
use think\exception\ValidateException;
use app\admin\controller\Admin;
use think\facade\Db;


class createField extends Admin
{
    
    public function handle($request, \Closure $next)
    {
        $data = $request->param();
        
        if(isset($data['other_config']['shuxing']) && in_array('tabs',$data['other_config']['shuxing'])){
            $info = Field::where('menu_id',$data['menu_id'])->where('other_config','like','%\"tabs%')->findOrEmpty();
            if(!$info->isEmpty()){
                throw new ValidateException('当前菜单已经设置选项卡字段'.$info['field']);
            }
        }
        
        $this->validate($data,\app\admin\controller\Sys\validate\Field::class);
        
        $menuInfo = Menu::find($data['menu_id']);
        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
        $prefix = config('database.connections.'.$connect.'.prefix');
        
        if(config('database.connections.'.$connect.'.type') <> 'mysql'){
            return $next($request);
        }
        
        if($data['create_table_field']){
            if($menuInfo['page_type'] == 1){
                $indexdata = self::normalizeIndexValue($data['indexdata'] ?? '');
                $validateList = self::syncValidateNotEmpty($data['validate'] ?? [],$indexdata !== '');
                $data['validate'] = $validateList;
                $request->withPost($data);
                $isNotNull = $indexdata !== '' || self::hasNotEmptyRule($validateList);
                $nullSql = $isNotNull ? 'NOT NULL' : 'NULL';
                $defaultSql = '';
                if((!empty($data['default_value']))){
                    if($data['type'] == 13){
                        $data['default_value'] = '0';
                    }
                    $defaultSql = "DEFAULT '".$data['default_value']."'";
                }elseif(!$isNotNull){
                    $defaultSql = 'DEFAULT NULL';
                }
                
                if(in_array($data['datatype'],['datetime','longtext'])){
                    $data['length'] = ' null';
                }else{
                    $data['length'] = "({$data['length']})";
                }
                
                $comment = self::getDescription($data);
                
                $sql="ALTER TABLE ".$prefix."{$menuInfo['table_name']} ADD {$data['field']} {$data['datatype']}{$data['length']} COMMENT '{$comment}' {$nullSql} {$defaultSql}";
                
                Db::connect($connect)->execute($sql);
                
                if($indexdata){
                    Db::connect($connect)->execute("ALTER TABLE ".$prefix."{$menuInfo['table_name']} ADD ".strtoupper($indexdata)." (  `".$data['field']."` )");
                }
            }
            if($menuInfo['page_type'] == 2 && $data['type'] == 30){
                $indexdata = self::normalizeIndexValue($data['indexdata'] ?? '');
                $validateList = self::syncValidateNotEmpty($data['validate'] ?? [],$indexdata !== '');
                $data['validate'] = $validateList;
                $request->withPost($data);
                $isNotNull = $indexdata !== '' || self::hasNotEmptyRule($validateList);
                $nullSql = $isNotNull ? 'NOT NULL' : 'NULL';
                $defaultSql = '';
                if((!empty($data['default_value']))){
                    if($data['type'] == 13){
                        $data['default_value'] = '0';
                    }
                    $defaultSql = "DEFAULT '".$data['default_value']."'";
                }elseif(!$isNotNull){
                    $defaultSql = 'DEFAULT NULL';
                }
                Db::connect($connect)->execute("ALTER TABLE `".$prefix.$menuInfo['table_name']."` ADD {$data['field']} VARCHAR( 50 ) {$nullSql} {$defaultSql}");
                if($indexdata){
                    Db::connect($connect)->execute("ALTER TABLE ".$prefix."{$menuInfo['table_name']} ADD ".strtoupper($indexdata)." (  `".$data['field']."` )");
                }
            }
        }
        
        return $next($request);
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

    private static function syncValidateNotEmpty($validate,$forceNotEmpty){
        $validateList = self::parseValidateList($validate);
        $validateList = array_values(array_filter($validateList,function($rule){
            return $rule !== 'notempty';
        }));
        if($forceNotEmpty || self::hasNotEmptyRule($validate)){
            $validateList[] = 'notempty';
        }
        return $validateList;
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
