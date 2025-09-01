<?php

namespace app\admin\controller\Sys;

use app\admin\model\Baseconfig as BaseconfigModel;
use think\exception\ValidateException;
use app\admin\controller\Sys\Build;
use app\admin\controller\Sys\model\Application;
use app\admin\controller\Sys\model\Field;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Sys\model\Action;
use app\admin\controller\Admin;
use think\facade\Db;

class Base extends Admin
{
    const VERSION = '20240825';
    
    public function initialize()
    {
        parent::initialize();
        config(['view_path' => app_path()], 'view');
    }
    
    //应用列表
    public function applicationList()
    {
        if (!$this->request->isPost()) {
            return view('controller/Sys/view/application');
        } else {
            $limit = $this->request->post('limit', 20, 'intval');
            $page = $this->request->post('page', 1, 'intval');
            
            $res = Application::order('app_id asc')->paginate(['list_rows' => $limit, 'page' => $page]);
            $data['data'] = $res;
            $data['status'] = 200;
            return json($data);
        }
    }
    
    //创建应用
    public function createApplication()
    {
        $data = $this->request->post();
        try {
            $res = Application::create($data);
            if ($data['app_type'] == 1) {
                Menu::create(['app_id' => $res->app_id, 'title' => '控制台', 'sortid' => 1, 'create_code' => 0, 'icon' => 'el-icon-platform-eleme', 'url' => '/' . $data['app_dir'] . '/Index/main.html']);
            }
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    //修改应用
    public function updateApplication()
    {
        $data = $this->request->post();
        try {
            Application::update($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    //获取应用
    public function getApplicationInfo()
    {
        $data = $this->request->post('app_id');
        try {
            $res = Application::find($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200, 'data' => $res]);
    }
    
    /*
     * @Description  秘钥管理
     */
    public function secrect()
    {
        if (!$this->request->isPost()) {
            return view('controller/Sys/view/secrect');
        } else {
            $data = $this->request->post();
            $info = db('secrect')->column('data', 'name');
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $info)) {
                    db('secrect')->field('data')->where(['name' => $key])->update(['data' => $value]);
                } else {
                    db('secrect')->create(['name' => $key, 'data' => $value]);
                }
            }
            return json(['status' => 200, 'msg' => '操作成功']);
        }
    }
    
    
    /*
     * @Description  修改信息之前查询信息的 勿要删除
     */
    function getSecrectInfo()
    {
        $res = db('secrect')->column('data', 'name');
        $data['status'] = 200;
        $data['data'] = $res;
        return json($data);
    }
    
    //获取主键ID
    public function getPk()
    {
        $data = $this->request->post('tablename');
        try {
            $res = Db::name($data)->getPk();
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200, 'data' => $res]);
    }
    
    //生成应用
    public function buildApplication()
    {
        $data = $this->request->post('app_id');
        $type = $this->request->post('type');
        
        $info = Application::find($data);
        
        if (!$info['status']) {
            throw new ValidateException('该应用禁止生成');
        }
        
        $rootPath = app()->getRootPath();
        
        $secrect = $this->getSecrect();
        
        if (empty($secrect['appid']) || empty($secrect['secrect'])) {
            $this->error('请在工具管理-秘钥管理中配置appid及secrect');
        }
        
        $menu_id = Db::name("menu")->where("table_name", $info["login_table"])->value("menu_id");
        
        $info['secrect'] = $secrect;
        $info['timestmp'] = time();
        $info['version'] = '2.0';
        $info["access_field"] = Db::name("field")->where("menu_id", $menu_id)->where("type", 38)->value("field");
        
        $role_access = Db::name("field")->where("menu_id", $menu_id)->where("field", 'access')->find();
        
        if ($role_access) {
            $roleTableName = '';
            if (preg_match('/from\s+([^\s,)(;]+)/i', $role_access['sql'], $matches)) {
                $roleTableName = $matches[1];
            }
            $roleTableName = str_replace(config('database.connections.mysql.prefix'), '', $roleTableName);
            $role_menu_id = Db::name("menu")->where("table_name", $roleTableName)->value("menu_id");
            if ($menu_id != $role_menu_id) {
                $info["access_field"] = Db::name("field")->where("menu_id", $role_menu_id)->where("type", 38)->value("field");
                $info["access_pk"] = Db::name("menu")->where("menu_id", $role_menu_id)->value("pk");
                $info["access_table"] = $roleTableName;
            }
        }
        
        $info['sign'] = md5(md5(json_encode($info, JSON_UNESCAPED_UNICODE) . $secrect['secrect']));
        
        $info['domain'] = $_SERVER['HTTP_HOST'];
        $res = $this->curlRequest('http://tfadmin.tiefen.net/produce/createApp/buildCode', 'POST', $info);
        
        $res = json_decode($res, true);
        
        if ($res['status'] == 411) {
            throw new ValidateException($res['msg']);
        }
        
        foreach ($res as $k => $v) {
            if (strpos($k, 'index.html') > 0 && file_get_contents($rootPath . $k) && file_get_contents($rootPath . $k) <> '欢迎使用tfAdmin') {
                filePutContents(file_get_contents($rootPath . $k), $rootPath . $k, $type = 2);
            } else {
                filePutContents($v, $rootPath . $k, 2);
            }
        }
        
        if ($info['app_type'] == 3) {
            $list = Db::query('show tables');
            foreach ($list as $k => $v) {
                $array[] = $v['Tables_in_' . config('database.connections.mysql.database')];
            }
            if (!in_array(config('database.connections.mysql.prefix') . 'catagory', $array)) {
                $file = $rootPath . 'app/admin/controller/Cms/cms.sql';
                $gz = fopen($file, 'r');
                for ($i = 0; $i < 1000; $i++) {
                    $sql .= str_replace('cd_', config('database.connections.mysql.prefix'), fgets($gz));
                    if (preg_match('/.*;$/', trim($sql))) {
                        if (false !== Db::query($sql)) {
                            $start += strlen($sql);
                        } else {
                            return false;
                        }
                        $sql = '';
                    }
                }
            }
        }
        
        return json(['status' => 200]);
        
    }
    
    //删除应用
    public function deleteApplication()
    {
        $data = $this->request->post();
        try {
            Application::destroy($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    private function getTpl($appid, $menu)
    {
        $info = Application::find($appid);
        switch ($info['app_type']) {
            case 1:
                $tpl = $menu . '/admin';
                break;
            
            case 2:
                $tpl = $menu . '/api';
                break;
            
            case 3:
                $tpl = $menu . '/cms';
                break;
            
        }
        return $tpl;
    }
    
    //菜单列表
    function menu()
    {
        if (!$this->request->isPost()) {
            $appid = $this->request->get('appid', 1, 'intval');
            $tpl = $this->getTpl($appid, 'menu');
            $this->view->assign('appid', $appid);
            return view('controller/Sys/view/' . $tpl);
        } else {
            $app_id = $this->request->post('app_id', 1, 'intval');
            foreach (config('database.connections') as $k => $v) {
                $connects[] = $k;
            }
            $data['status'] = 200;
            $data['list'] = $this->getMenu($app_id, 0);
            $data['defaultConnect'] = config('database.default');
            $data['connects'] = $connects;
            $data['tableList'] = $this->getTableList(config('database.default'));
            $data['app_list'] = Application::field('app_id,app_type,application_name')->select()->toArray();
            
            foreach ($data['app_list'] as $k => $v) {
                $data['app_list'][$k]['url'] = (string)url('admin/Menu/index', ['app_id' => $v['app_id'], 'app_type' => $v['app_type']]);
            }
            
            $data['page_type_list'] = Config::page_type_list();
            return json($data);
        }
    }
    
    //创建菜单
    public function createMenu()
    {
        $data = $this->request->post();
        $data['controller_name'] = $this->setControllerName($data['controller_name']);
        $res = Menu::create($data);
        if ($res->menu_id && $data['table_name'] && $data['pk'] && $data['create_code']) {
            foreach ((Config::actionList()) as $key => $val) {
                $val['menu_id'] = $res->menu_id;
                if ($val['default_create'] && $data['page_type'] == 1 && !in_array($val['type'], [10, 11])) {
                    Action::create($val);
                }
            }
            foreach ((Config::defaultFields()) as $key => $val) {
                $val['menu_id'] = $res->menu_id;
                $val['field'] = $data['pk'];
                if (config('database.connections.' . $data['connect'] . '.type') == 'mongo') {
                    $val['width'] = 220;
                    $val['datatype'] = 'string';
                    $val['length'] = '';
                }
                if ($val['primary'] && $data['page_type'] == 1) {
                    Field::create($val);
                }
            }
            if ($data['page_type'] == 2) {
                Action::create(['name' => $data['title'], 'menu_id' => $res->menu_id, 'action_name' => 'index', 'type' => 14]);
            }
        }
        Menu::update(['menu_id' => $res->menu_id, 'sortid' => $res->menu_id]);
        return json(['status' => 200]);
    }
    
    //更新菜单
    public function updateMenu()
    {
        $data = $this->request->post();
        $data['controller_name'] = $this->setControllerName($data['controller_name']);
        
        if (!isset($data['pid'])) {
            $data['pid'] = '0';
        }
        
        try {
            $res = Menu::update($data);
            if ($res) {
                if ($data['page_type'] == 2) {
                    Action::where('type', '<>', 14)->where('menu_id', $data['menu_id'])->delete();
                    $configAction = Action::where('type', 14)->where('menu_id', $data['menu_id'])->count();
                    if (!$configAction) {
                        Action::create(['name' => $data['title'], 'menu_id' => $data['menu_id'], 'action_name' => 'index', 'type' => 14]);
                    }
                }
            }
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    //方法列表直接修改操作
    public function updateMenuExt()
    {
        $data = $this->request->post();
        try {
            $res = Menu::update($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    
    //获取菜单信息
    public function getMenuInfo()
    {
        $data = $this->request->post('menu_id');
        try {
            $res = menu::find($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200, 'data' => $res]);
    }
    
    //删除菜单
    public function deleteMenu()
    {
        $data = $this->request->post();
        try {
            $res = Menu::destroy($data);
            if ($res) {
                Field::where($data)->delete();
                Action::where($data)->delete();
            }
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    
    //复制菜单
    public function copyMenu()
    {
        $data = $this->request->post();
        if (empty($data['appid']) || empty($data['menu_id'])) {
            $this->error('参数错误');
        }
        
        $menuInfo = Menu::where('menu_id', $data['menu_id'])->find()->toArray();
        
        $application = Application::find($data['appid']);
        
        $menuInfo['create_table'] = 0;
        $menuInfo['pid'] = 0;
        $menuInfo['app_id'] = $data['appid'];
        unset($menuInfo['menu_id']);
        
        try {
            $res = Menu::create($menuInfo);
            $fieldList = Field::where(['menu_id' => $data['menu_id']])->select()->toArray();
            if ($fieldList) {
                foreach ($fieldList as $key => $val) {
                    unset($val['id']);
                    $val['create_table_field'] = 0;
                    if (in_array($val['list_show'], [0, 1]) && $application['app_type'] == 2) {
                        $val['list_show'] = 0;
                    }
                    if (in_array($val['list_show'], [2, 3, 4]) && $application['app_type'] == 2) {
                        $val['list_show'] = 1;
                    }
                    $val['menu_id'] = $res->menu_id;
                    Field::create($val);
                }
            }
            
            $actionList = Action::where(['menu_id' => $data['menu_id']])->select()->toArray();
            if ($actionList) {
                foreach ($actionList as $key => $val) {
                    if (in_array($val['type'], [1, 2, 3, 4, 5, 6, 7, 8, 9, 14, 20])) {
                        unset($val['id']);
                        $val['menu_id'] = $res->menu_id;
                        
                        $tmp = json_decode($val['other_config'], true);
                        if (isset($tmp['befor_hook'])) {
                            unset($tmp['befor_hook']);
                        }
                        if (isset($tmp['after_hook'])) {
                            unset($tmp['after_hook']);
                        }
                        $val['other_config'] = json_encode($tmp);
                        
                        Action::create($val);
                    }
                }
            }
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        
        return json(['status' => 200]);
        
    }
    
    //菜单字段列表
    public function fieldList()
    {
        if (!$this->request->isPost()) {
            $appid = $this->request->get('appid', 1, 'intval');
            $menu_id = $this->request->get('menu_id', '', 'intval');
            $ai = $this->request->get('ai', '', 'intval');
            
            $tpl = $this->getTpl($appid, 'field');
            $this->view->assign('appid', $appid);
            $this->view->assign('menu_id', $menu_id);
            if ($ai == 1) {
                $baseConfig = BaseconfigModel::column('data', 'name');
                if (empty($baseConfig['deepseekkey'])) {
                    throw new ValidateException('请在站长配置里填写deepseekkey');
                }
                return view('controller/Sys/view/field/aiadmin');
            } else {
                return view('controller/Sys/view/' . $tpl);
            }
            
        } else {
            $limit = $this->request->post('limit', 20, 'intval');
            $page = $this->request->post('page', 1, 'intval');
            $menu_id = $this->request->post('menu_id', '', 'intval');
            $appid = $this->request->post('appid', '', 'intval');
            
            $res = Field::where(['menu_id' => $menu_id])->order('sortid asc')->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
            $data['status'] = 200;
            $data['data'] = $res;
            $data['typeField'] = Config::fieldList();
            $data['itemList'] = Config::itemList();
            $data['menu_title'] = Menu::where('menu_id', $menu_id)->value('title');
            $data['app_name'] = Application::where('app_id', $appid)->value('application_name');
            $data['app_list'] = Application::field('app_id,app_type,application_name')->where('app_id', '<>', $appid)->select()->toArray();
            $baseConfig = BaseconfigModel::column('data', 'name');
            if (isset($baseConfig['deepseekkey']) && !empty($baseConfig['deepseekkey'])) {
                $data['deepseekkey'] = $baseConfig['deepseekkey'];
            }
            return json($data);
        }
        
    }
    
    //创建字段
    public function createField()
    {
        $data = $this->request->post();
        
        $this->validate($data, \app\admin\controller\Sys\validate\Field::class);
        
        $data['item_config'] = getItemData($data['item_config']);
        $data['validate'] = implode(',', $data['validate']);
        
        foreach (Config::fieldList() as $v) {
            if ($v['type'] == $data['type'] && empty($data['belong_table'])) {
                $search_status = $v['search'];
            }
        }
        
        $data['search_type'] = $search_status;
        
        if (isset($data['other_config']['shuxing']) && in_array('tabs', $data['other_config']['shuxing'])) {
            $data['search_type'] = 0;
        }
        
        if (isset($data['other_config']['key_placeholder'])) {
            $data['key_placeholder'] = $data['other_config']['key_placeholder'];
            unset($data['other_config']['key_placeholder']); // 避免重复存储
        }
        if (isset($data['other_config']['value_placeholder'])) {
            $data['value_placeholder'] = $data['other_config']['value_placeholder'];
            unset($data['other_config']['value_placeholder']); // 避免重复存储
        }
        
        $data['other_config'] = json_encode($data['other_config']);
        
        $data['tx_config'] = json_encode($data['tx_config'], 320);
        $data['list_background_config'] = json_encode($data['list_background_config'], 320);
        
        // 同目录字段 tx_config
        $menu_tx_configs = Db::name('field')
            ->where('menu_id', $data['menu_id'])
            ->where('tx_config', '<>', '')
            ->whereNotNull('tx_config')
            ->column('tx_config');
        // 值有效？ 0不显示 1显示
        $prompt = 0;
        foreach (array_merge($menu_tx_configs, [$data['tx_config']]) as $menu_tx_config) {
            if (!empty(json_decode($menu_tx_config, true))) {
                $prompt = 1;
                break;
            }
        }
        try {
            // 显示状态更新
            Db::name('menu')->where(['menu_id' => $data['menu_id']])->update([
                'prompt' => $prompt,
            ]);
            
            $res = Field::create($data);
            if ($res->id) {
                Field::update(['id' => $res->id, 'sortid' => $res->id]);
            }
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    
    /**
     * 批量创建字段（包含MySQL表结构修改）
     * @return \think\response\Json
     */
    public function batchCreateField()
    {
        $fields = $this->request->post('fields/a', []);
        $menuId = $this->request->param('menu_id');
        $tableName = $this->request->param('table_name'); // 直接从请求获取表名
        
        
        $data = $this->request->post();
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        Db::startTrans();
        try {
            foreach ($data['fields'] as $field) {
                $res = Field::create($field);
                if ($res->id) {
                    Field::update(['id' => $res->id, 'sortid' => $res->id]);
                }
                $successCount++;
            }
            Db::commit();
            return json([
                'status' => 200,
                'msg' => '批量创建完成',
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'status' => 500,
                'msg' => '批量创建失败',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 修改数据表字段结构
     */
    protected function alterTableField($tableName, $field)
    {
        // 检查表是否存在
        if (!Db::query("SHOW TABLES LIKE '{$tableName}'")) {
            throw new \Exception("数据表{$tableName}不存在");
        }
        
        // 检查字段是否已存在
        $exists = Db::query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$field['field']}'");
        if ($exists) {
            throw new \Exception("字段{$field['field']}已存在");
        }
        
        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$field['field']}` ";
        
        // 根据类型生成SQL
        switch ($field['datatype'] ?? 'VARCHAR') {
            case 'INT':
            case 'TINYINT':
                $length = $field['length'] ?? ($field['datatype'] === 'INT' ? '11' : '4');
                $sql .= "{$field['datatype']}({$length})";
                break;
            case 'VARCHAR':
            case 'CHAR':
                $length = $field['length'] ?? '255';
                $sql .= "{$field['datatype']}({$length})";
                break;
            case 'DECIMAL':
                $length = $field['length'] ?? '10,2';
                $sql .= "DECIMAL({$length})";
                break;
            default:
                $sql .= "{$field['datatype']}";
        }
        
        $sql .= " COMMENT '{$field['title']}'";
        
        if (!empty($field['default_value'])) {
            $sql .= " DEFAULT '{$field['default_value']}'";
        }
        
        Db::execute($sql);
    }
    
    //更新字段
    public function updateField()
    {
        $data = $this->request->post();
        if (isset($data['other_config']['shuxing']) && in_array('tabs', $data['other_config']['shuxing'])) {
            $info = Field::where('menu_id', $data['menu_id'])->where('other_config', 'like', '%\"tabs%')->where('id', '<>', $data['id'])->findOrEmpty();
            if (!$info->isEmpty()) {
                throw new ValidateException('当前菜单已经设置选项卡字段' . $info['field']);
            }
            $data['search_type'] = 0;
        }
        
        
        // 处理独立传递的 key_placeholder 和 value_placeholder（新增逻辑）
        if (isset($data['other_config']['key_placeholder'])) {
            $data['key_placeholder'] = $data['other_config']['key_placeholder'];
            unset($data['other_config']['key_placeholder']); // 避免重复存储
        }
        if (isset($data['other_config']['value_placeholder'])) {
            $data['value_placeholder'] = $data['other_config']['value_placeholder'];
            unset($data['other_config']['value_placeholder']); // 避免重复存储
        }
        $prompt = -1;
        
        if ($data['field_type']) {
            $param['id'] = $data['id'];
            $param['field'] = $data['field'];
            $param['other_config'] = $data['other_config'] ? json_encode($data['other_config']) : "{}";
        } else {
            $this->validate($data, \app\admin\controller\Sys\validate\Field::class);
            
            $data['item_config'] = getItemData($data['item_config']);
            $data['other_config'] = json_encode($data['other_config']);
            $data['validate'] = implode(',', $data['validate']);
            $data['tx_config'] = json_encode($data['tx_config'], 320);
            $data['list_background_config'] = json_encode($data['list_background_config'], 320);
            
            foreach (Config::fieldList() as $v) {
                if ($v['type'] == $data['type'] && empty($data['belong_table'])) {
                    $search_status = $v['search'];
                }
            }
            
            // 同目录下其他字段 查询 判断是否需要提醒
            $menu_tx_configs = Db::name('field')
                ->where('menu_id', $data['menu_id'])
                ->whereNotIn('id', [$data['id']])
                ->where('tx_config', '<>', '')
                ->whereNotNull('tx_config')
                ->column('tx_config');
            // 0不提醒 1提醒
            $prompt = 0;
            foreach (array_merge($menu_tx_configs, [$data['tx_config']]) as $menu_tx_config) {
                if (!empty(json_decode($menu_tx_config, true))) {
                    $prompt = 1;
                    break;
                }
            }
            
            $param = $data;
        }
        
        try {
            if ($prompt > -1) {
                // 更新显示状态
                Db::name('menu')->where(['menu_id' => $data['menu_id']])->update([
                    'prompt' => $prompt,
                ]);
            }
            
            Field::update($param);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    //方法列表直接修改操作
    public function updateFieldExt()
    {
        $data = $this->request->post();
        try {
            $res = Field::update($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    
    //字段同步到其他应用
    public function copyField()
    {
        $appid = $this->request->post('appid');
        $field = $this->request->post('field_id');
        $menu_id = $this->request->post('menu_id');
        
        if (empty($appid)) {
            throw new ValidateException('应用ID不能为空');
        }
        
        if (empty($field)) {
            throw new ValidateException('请选择需要同步的字段');
        }
        
        if (empty($menu_id)) {
            throw new ValidateException('菜单ID不能为空');
        }
        
        $menuInfo = Menu::findOrEmpty($menu_id);
        
        $target_menu_id = Menu::where('app_id', $appid)->where('table_name', $menuInfo['table_name'])->value('menu_id');
        
        if (!$target_menu_id) {
            throw new ValidateException('目标菜单不存在');
        }
        
        foreach ($field as $v) {
            $fieldInfo = Field::where('id', $v)->value('field');
            $targetFieldInfo = Field::where('field', $fieldInfo)->where('menu_id', $target_menu_id)->find();
            if (!$targetFieldInfo) {
                $info = Field::find($v)->toArray();
                $info['create_table_field'] = 0;
                $info['menu_id'] = $target_menu_id;
                
                unset($info['id']);
                
                $res = Field::insertGetId($info);
                if ($res) {
                    Field::update(['id' => $res, 'sortid' => $res]);
                }
            }
        }
        
        return json(['status' => 200]);
    }
    
    //获取字段信息
    public function getFieldInfo()
    {
        $data = $this->request->post();
        try {
            $res = Field::where($data)->find()->toArray();
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        $res['validate'] = explode(',', $res['validate']);
        $res['item_config'] = json_decode($res['item_config'], true);
        $res['other_config'] = json_decode($res['other_config'], true);
        if (!isset($res['other_config']['shuxing'])) {
            if (!is_array($res['other_config'])) {
                $res['other_config'] = [];
            }
            $res['other_config']['shuxing'] = [];
        }
        $res['other_config'] = json_encode($res['other_config']);
        return json(['status' => 200, 'data' => $res]);
    }
    
    //删除字段
    public function deleteField()
    {
        $data = $this->request->post();
        $menuInfo = Menu::find($data['menu_id']);
        $pk = Db::connect($menuInfo['connect'])->name($menuInfo['table_name'])->getPk();
        $fieldList = Field::field('id,field')->where($data)->select()->toArray();
        $ids = [];
        foreach ($fieldList as $v) {
            if ($pk <> $v['field']) {
                array_push($ids, $v['id']);
            } else {
                $pk_status = true;
            }
        }
        try {
            Field::where('id', 'in', $ids)->delete();
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200, 'pk_status' => $pk_status]);
    }
    
    //方法列表
    public function actionList()
    {
        if (!$this->request->isPost()) {
            $appid = $this->request->get('appid', 1, 'intval');
            $menu_id = $this->request->get('menu_id', '', 'intval');
            $tpl = $this->getTpl($appid, 'action');
            $this->view->assign('appid', $appid);
            $this->view->assign('menu_id', $menu_id);
            return view('controller/Sys/view/' . $tpl);
        } else {
            $limit = $this->request->post('limit', 20, 'intval');
            $page = $this->request->post('page', 1, 'intval');
            $menu_id = $this->request->post('menu_id', '', 'intval');
            $appid = $this->request->post('app_id');
            
            $res = Action::where(['menu_id' => $menu_id])->order('sortid asc')->paginate(['list_rows' => $limit, 'page' => $page]);
            $data['data'] = $res;
            $data['status'] = 200;
            $data['actionList'] = Config::actionList();
            $data['menu_title'] = Menu::where('menu_id', $menu_id)->value('title');
            $data['app_name'] = Application::where('app_id', $appid)->value('application_name');
            return json($data);
        }
    }
    
    //获取提交字段
    public function getPostField()
    {
        $menu_id = $this->request->post('menu_id');
        
        $menuInfo = Menu::find($menu_id);
        
        $list = [];
        $fieldlist = Field::field('type,field,title,post_status')->where('menu_id', $menu_id)->order('sortid asc')->select()->toArray();
        foreach ($fieldlist as $k => $v) {
            if ($v['post_status'] == 1) {
                array_push($list, $v);
            }
        }
        
        $pk = Db::connect($menuInfo['connect'])->name($menuInfo['table_name'])->getPk();
        
        $model_fields = array_merge([['field' => $pk, 'title' => '编号']], $list);
        
        $tableList = Menu::where('table_name', '<>', '')->where('app_id', $menuInfo['app_id'])->field('controller_name')->select()->toArray();
        
        $with_join = [];
        $actionList = Action::where('menu_id', $menu_id)->select();
        foreach ($actionList as $v) {
            if ($v['with_join'] && in_array($v['type'], [2, 3])) {
                foreach (json_decode($v['with_join'], true) as $n) {
                    $n['fields'] = $this->getExtendFields($n);
                    foreach ($n['fields'] as $m) {
                        array_push($with_join, $m);
                    }
                }
            }
        }
        
        $newWith = [];
        foreach ($with_join as $key => $v) {
            if (isset($newWith[$v['field']]) == false) {
                $newWith[$v['field']] = $v;
            }
        }
        
        foreach ($newWith as $k => $v) {
            unset($newWith[$k]['belong_table']);
            unset($newWith[$k]['table_name']);
        }
        
        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
        $dbtype = config('database.connections.' . $connect . '.type');
        
        $tab_fields = array_merge($list, $newWith);
        return json(['status' => 200, 'dbtype' => $dbtype, 'data' => $list, 'jump_field' => $fieldlist, 'model_fields' => $model_fields, 'search_field' => $fieldlist, 'tab_fields' => $tab_fields, 'tableList' => $tableList, 'sms_list' => Config::sms_list()]);
    }
    
    //创建方法
    public function createAction()
    {
        $data = $this->request->post();
        
        $this->validate($data, \app\admin\controller\Sys\validate\Action::class);
        
        if ($data['with_join']) {
            foreach ($data['with_join'] as $k => $v) {
                $menuInfo = Menu::field('connect,table_name')->where('controller_name', $v['relative_table'])->find();
                $data['with_join'][$k]['table_name'] = $menuInfo['table_name'];
                $data['with_join'][$k]['connect'] = $menuInfo['connect'];
            }
        }
        // 处理超级页面数据
        if ($data['type'] == 55) { // 55是超级页面类型
            $data['q_template'] = $data['q_template'] ?? '';
            $data['h_php'] = $data['h_php'] ?? '';
        }
        
        $data['list_filter'] = getItemData($data['list_filter']);
        $data['tab_config'] = getItemData($data['tab_config']);
        $data['with_join'] = getItemData($data['with_join']);
        $data['other_config'] = json_encode($data['other_config']);
        
        $data['fields'] = implode(',', $data['fields']);
        
        
        if (in_array($data['type'], [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 15, 16, 17, 18, 19, 20, 21])) {
            $data['group_button_status'] = 1;
        }
        
        if (in_array($data['type'], [2, 3, 19, 21])) {
            $data['dialog_size'] = '1000px';
        }
        
        if (in_array($data['type'], [3, 4])) {
            $data['list_button_status'] = 1;
        }
        
        try {
            $count = Action::where('menu_id', $data['menu_id'])->where('action_name', $data['action_name'])->count();
            if ($count > 0) {
                throw new ValidateException ('方法名已经存在');
            }
            $res = Action::create($data);
            
            if ($res->id) {
                Action::update(['id' => $res->id, 'sortid' => $res->id]);
                
                if ($data['type'] == 20) {
                    $menuInfo = db("menu")->where('menu_id', $data['menu_id'])->find();
                    $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
                    
                    $fieldlist = Db::connect($menuInfo['connect'])->query('show full columns from ' . config('database.connections.' . $menuInfo['connect'] . '.prefix') . $menuInfo['table_name']);
                    foreach ($fieldlist as $v) {
                        $arr[] = $v['Field'];
                    }
                    $delete_field = !is_null(config('my.delete_field')) ? config('my.delete_field') : 'delete_time';
                    if (!in_array($delete_field, $arr)) {
                        $sql = "ALTER TABLE " . config('database.connections.' . $connect . '.prefix') . "{$menuInfo['table_name']} ADD {$delete_field} int(10) COMMENT '软删除标记' DEFAULT null";
                        Db::connect($connect)->execute($sql);
                    }
                }
            }
            
            
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    //快速创建方法
    public function quckCreateAction()
    {
        $data = $this->request->post('actions');
        $menu_id = $this->request->post('menu_id');
        foreach ($data as $key => $val) {
            foreach ((Config::actionList()) as $k => $v) {
                if ($val == $v['type']) {
                    $v['menu_id'] = $menu_id;
                    if (!in_array($v['action_name'], Action::where('menu_id', $menu_id)->column('action_name'))) {
                        Action::create($v);
                    } else {
                        $exits_status = true;
                    }
                }
            }
        }
        return json(['status' => 200, 'exits_status' => true]);
    }
    
    //更新方法
    public function updateAction()
    {
        $data = $this->request->post();
        $this->validate($data, \app\admin\controller\Sys\validate\Action::class);
        
        if ($data['with_join']) {
            foreach ($data['with_join'] as $k => $v) {
                $menuInfo = Menu::field('connect,table_name')->where('controller_name', $v['relative_table'])->find();
                $data['with_join'][$k]['table_name'] = $menuInfo['table_name'];
                $data['with_join'][$k]['connect'] = $menuInfo['connect'];
            }
        }
        $filterField = [];
        if (!empty($data['tree_config'])) {
            foreach ($data['list_filter'] as $v) {
                $filterField[] = $v['searchField'];
            }
            if ($data['other_config']['tree_load_type'] == 2) {
                if (!in_array($data['tree_config'], $filterField)) {
                    array_push($data['list_filter'], ['searchField' => $data['tree_config'], 'searchCondition' => '=', 'serachVal' => 0]);
                }
            } else {
                if (in_array($data['tree_config'], $filterField)) {
                    unset($data['list_filter'][0]);
                }
            }
        }
        
        $data['list_filter'] = getItemData($data['list_filter']);
        $data['tab_config'] = getItemData($data['tab_config']);
        $data['with_join'] = getItemData($data['with_join']);
        $data['fields'] = $data['fields'] ? implode(',', $data['fields']) : '';
        $data['other_config'] = json_encode($data['other_config']);
        
        try {
            $res = Action::update($data);
            if ($data['type'] == 20) {
                $actionInfo = db("action")->where('id', $data['id'])->find();
                $menuInfo = db("menu")->where('menu_id', $actionInfo['menu_id'])->find();
                $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
                $delete_field = !is_null(config('my.delete_field')) ? config('my.delete_field') : 'delete_time';
                $deleteFieldStatus = $this->getFieldStatus(config('database.connections.' . $connect . '.prefix') . $menuInfo['table_name'], $delete_field, $connect);
                if (!$deleteFieldStatus) {
                    $sql = "ALTER TABLE " . config('database.connections.' . $connect . '.prefix') . "{$menuInfo['table_name']} ADD {$delete_field} int(10) COMMENT '软删除标记' DEFAULT null";
                    Db::connect($connect)->execute($sql);
                }
            }
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    //方法列表直接修改操作
    public function updateActionExt()
    {
        $data = $this->request->post();
        
        try {
            $updateData = $data;
            
            if (isset($data['remark'])) {
                $updateData['remark'] = $data['remark'];
                // 版本记录
                Db::name('action_remarks')->insert([
                    'action_id' => $data['id'],
                    'content' => $data['remark'],
                    'description' => $data['remark_desc'],
                    'menu_id' => $data['menu_id'], // 添加这行
                    'create_time' => time()
                ]);
            }
            
            if (isset($data['remark_desc'])) {
                $updateData['remark_desc'] = $data['remark_desc'];
            }
            
            if (!empty($updateData)) {
                
                
                Action::where('id', $data['id'])->update($updateData);
                
            }
            
            
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    //获取方法信息
    public function getActionInfo()
    {
        $data = $this->request->post();
        try {
            $res = Action::where($data)->find()->toArray();
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        if ($res['list_filter']) {
            $res['list_filter'] = json_decode($res['list_filter'], true);
        }
        if ($res['tab_config']) {
            $res['tab_config'] = json_decode($res['tab_config'], true);
        }
        
        if ($res['with_join']) {
            $res['with_join'] = json_decode($res['with_join'], true);
        }
        
        $res['other_config'] = json_decode($res['other_config'], true);
        
        if (!isset($res['other_config']['hook'])) {
            if (!is_array($res['other_config'])) {
                $res['other_config'] = [];
            }
            $res['other_config']['hook'] = [];
        }
        
        if (!isset($res['other_config']['guige'])) {
            if (!is_array($res['other_config'])) {
                $res['other_config'] = [];
            }
            $res['other_config']['guige'] = [];
        }
        
        if (!isset($res['other_config']['detail_search_field'])) {
            if (!is_array($res['other_config'])) {
                $res['other_config'] = [];
            }
            $res['other_config']['detail_search_field'] = [];
        }
        
        if (!isset($res['other_config']['befor_hook'])) {
            $res['other_config']['befor_hook'] = '';
        }
        
        if (!isset($res['other_config']['after_hook'])) {
            $res['other_config']['after_hook'] = '';
        }
        
        $list = Field::where('menu_id', $data['menu_id'])->column('field');
        
        $fields = explode(',', $res['fields']);
        foreach ($fields as $key => $val) {
            if (!in_array($val, $list)) {
                unset($fields[$key]);
            }
        }
        $res['fields'] = array_values($fields);
        $res['other_config'] = json_encode($res['other_config']);
        
        return json(['status' => 200, 'data' => $res]);
    }
    
    
    //删除方法
    public function deleteAction()
    {
        $data = $this->request->post();
        
        $list = Action::where($data)->field('action_name')->select()->toArray();
        
        $rootPath = app()->getRootPath();
        
        $menu = Menu::find($data['menu_id']);
        $application = Application::find($menu['app_id']);
        
        
        $actions = Action::where($data)->select()->toArray();
        
        foreach ($list as $key => $v) {
            if ($menu['controller_name'] && $v['action_name']) {
                @unlink($rootPath . '/public/components/' . $application['app_dir'] . '/' . strtolower($menu['controller_name']) . '/' . $v['action_name'] . '.js');
            }
            
            if ($actions[$key]['type'] == 55) {
                @unlink($rootPath . "app/" . $application['app_dir'] . "/view/" . getViewName($menu['controller_name']) . "/" . $v['action_name'] . ".html");
            }
        }
        
        
        $info = db("action")->where($data)->select()->toArray();
        try {
            foreach ($info as $v) {
                $res = Action::where('id', $v['id'])->delete();
                if ($res && $v['type'] == 20) {
                    $delete_field = !is_null(config('my.delete_field')) ? config('my.delete_field') : 'delete_time';
                    $connect = $menu['connect'] ? $menu['connect'] : config('database.default');
                    if ($this->getFieldStatus(config('database.connections.' . $connect . '.prefix') . $menu['table_name'], $delete_field, $connect)) {
                        $sql = 'ALTER TABLE ' . config('database.connections.' . $connect . '.prefix') . $menu['table_name'] . ' DROP ' . $delete_field;
                        Db::connect($connect)->execute($sql);
                    }
                }
            }
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }
    
    //拖动排序
    public function updateFieldSort()
    {
        $postField = 'currentId,preId,nextId,currentSortId,preSortId,nextSortId,menu_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        
        if ($data['preSortId'] && $data['nextSortId'] && $data['currentSortId'] > $data['preSortId'] && $data['currentSortId'] < $data['nextSortId']) {
            $this->error('操作失败');
        }
        
        if (empty($data['preSortId']) && $data['nextSortId'] && $data['currentSortId'] < $data['nextSortId']) {
            $this->error('操作失败');
        }
        
        if ($data['preSortId'] && empty($data['nextSortId']) && $data['currentSortId'] > $data['preSortId']) {
            $this->error('操作失败');
        }
        
        if (!empty($data['preId'])) {
            $pre = Field::where('id', $data['preId'])->where('menu_id', $data['menu_id'])->value('sortid');
        }
        if (!empty($data['nextId'])) {
            $next = Field::where('id', $data['nextId'])->where('menu_id', $data['menu_id'])->value('sortid');
        }
        
        $current = Field::where('id', $data['currentId'])->where('menu_id', $data['menu_id'])->value('sortid');
        
        if ($current > $pre) {
            $sortid = $next;
        } else {
            $sortid = $pre;
        }
        
        if (empty($pre)) {
            $pre = $next - 1;
            $sortid = $next;
        }
        if (empty($next)) {
            $next = $pre + 1;
            $sortid = $pre;
        }
        try {
            if ($current > $pre) {
                Field::field('sortid')->where('sortid', 'between', [$pre + 1, $current - 1])->where('menu_id', $data['menu_id'])->inc('sortid', 1)->update();
            }
            if ($current < $pre) {
                Field::field('sortid')->where('sortid', 'between', [$current + 1, $next - 1])->where('menu_id', $data['menu_id'])->dec('sortid', 1)->update();
            }
            Field::field('sortid')->where('id', $data['currentId'])->where('menu_id', $data['menu_id'])->update(['sortid' => $sortid]);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200, 'pre' => $pre]);
    }
    
    //拖动排序
    public function updateActionSort()
    {
        $postField = 'currentId,preId,nextId,currentSortId,preSortId,nextSortId,menu_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        
        if ($data['preSortId'] && $data['nextSortId'] && $data['currentSortId'] > $data['preSortId'] && $data['currentSortId'] < $data['nextSortId']) {
            $this->error('操作失败');
        }
        
        if (empty($data['preSortId']) && $data['nextSortId'] && $data['currentSortId'] < $data['nextSortId']) {
            $this->error('操作失败');
        }
        
        if ($data['preSortId'] && empty($data['nextSortId']) && $data['currentSortId'] > $data['preSortId']) {
            $this->error('操作失败');
        }
        
        if (!empty($data['preId'])) {
            $pre = Action::where('id', $data['preId'])->where('menu_id', $data['menu_id'])->value('sortid');
        }
        if (!empty($data['nextId'])) {
            $next = Action::where('id', $data['nextId'])->where('menu_id', $data['menu_id'])->value('sortid');
        }
        
        $current = Action::where('id', $data['currentId'])->where('menu_id', $data['menu_id'])->value('sortid');
        
        if ($current > $pre) {
            $sortid = $next;
        } else {
            $sortid = $pre;
        }
        
        if (empty($pre)) {
            $pre = $next - 1;
            $sortid = $next;
        }
        if (empty($next)) {
            $next = $pre + 1;
            $sortid = $pre;
        }
        try {
            if ($current > $pre) {
                Action::where('sortid', 'between', [$pre + 1, $current - 1])->where('menu_id', $data['menu_id'])->inc('sortid', 1)->update();
            }
            if ($current < $pre) {
                Action::field('sortid')->where('sortid', 'between', [$current + 1, $next - 1])->where('menu_id', $data['menu_id'])->dec('sortid', 1)->update();
            }
            Action::field('sortid')->where('id', $data['currentId'])->where('menu_id', $data['menu_id'])->update(['sortid' => $sortid]);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200, 'pre' => $pre]);
    }
    
    //字段选项配置，验证规则配置
    public function configList()
    {
        $menu_id = $this->request->post('menu_id');
        
        $menuInfo = Menu::find($menu_id);
        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
        $dbtype = config('database.connections.' . $connect . '.type');
        
        $ruleList = Config::ruleList();
        if ($dbtype <> 'mongo') {
            $propertyField = Config::propertyField();
        } else {
            $propertyField = Config::propertyMongoField();
        }
        
        $applist = Application::where("app_type", 1)->where("app_id", ">", 1)->select();
        
        
        $my_actions = Action::where(['menu_id' => $menu_id])->order('sortid asc')->select()->toArray();
        return json(['status' => 200, 'ruleList' => $ruleList, 'propertyField' => $propertyField, 'dbtype' => $dbtype, 'applist' => $applist, 'my_actions' => $my_actions]);
    }
    
    
    //数据库table列表
    public function getTables()
    {
        $connects = [];
        foreach (config('database.connections') as $k => $v) {
            $connects[] = $k;
        }
        $connect = $this->request->post('connect', config('database.default'), 'strval');
        if (empty($connect)) {
            $connect = "mysql";
        }
        return json(['status' => 200, 'data' => $this->getTableList($connect), 'connects' => $connects]);
    }
    
    //用过菜单id获取所有数据表
    public function getTablesByMenuId()
    {
        $menu_id = $this->request->post('menu_id');
        if (!$menu_id) {
            $this->error('菜单ID不能为空');
        }
        $app_id = Menu::where('menu_id', $menu_id)->value('app_id');
        $tableList = Menu::where('app_id', $app_id)->where('table_name', '<>', '')->field('table_name,title')->select();
        return json(['status' => 200, 'data' => $tableList]);
    }
    
    //数据库table列表
    private function getTableList($connect)
    {
        $list = Db::connect($connect)->query('show tables');
        foreach ($list as $k => $v) {
            $tableList[] = str_replace(config('database.connections.' . $connect . '.prefix'), '', $v['Tables_in_' . config('database.connections.' . $connect . '.database')]);
        }
        $no_show_table = ['menu', 'application', 'admin_user', 'action', 'log', 'field'];
        foreach ($tableList as $key => $val) {
            if (in_array($val, $no_show_table)) {
                unset($tableList[$key]);
            }
        }
        return array_values($tableList);
    }
    
    //根据表名获取字段列表
    public function getTableFields()
    {
        $controller_name = $this->request->post('controller_name');
        if (!$controller_name) {
            $this->error('数据表不能为空');
        }
        
        $menuInfo = Menu::where('controller_name', $controller_name)->find();
        
        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
        $dbtype = config('database.connections.' . $connect . '.type');
        if ($dbtype == 'mongo') {
            $list = db("field")->field("field as Field,title as Comment")->where('menu_id', $menuInfo['menu_id'])->order('sortid asc')->select();
        } else {
            $list = Db::connect($menuInfo['connect'])->query('show full columns from ' . config('database.connections.' . $menuInfo['connect'] . '.prefix') . $menuInfo['table_name']);
        }
        
        return json(['status' => 200, 'filedList' => $list]);
    }
    
    
    //获取菜单列表
    private function getMenu($app_id)
    {
        $field = 'menu_id,pid,title,controller_name,create_code,create_table,table_name,status,sortid';
        $list = Menu::field($field)->where(['app_id' => $app_id])->order('sortid asc')->select()->toArray();
        return _generateListTree($list, 0, ['menu_id', 'pid']);
    }
    
    
    //获取上传配置列表
    public function getUploadList()
    {
        $appid = $this->request->post('app_id');
        $app_type = Application::where('app_id', $appid)->value('app_type');
        $list = Db::name('upload_config')->field('id,title')->select()->toArray();
        return json(['status' => 200, 'data' => $list, 'app_type' => $app_type]);
    }
    
    
    //生成
    public function create()
    {
        $menu_id = $this->request->post('menu_id');
        $type = $this->request->post('type');
        if ($this->createCode($menu_id, $type)) {
            return json(['status' => 200]);
        }
    }
    
    //生成
    private function createCode($menu_id, $type)
    {
        $menuInfo = Menu::find($menu_id)->toArray();
        
        if (!$menuInfo['create_code']) {
            $this->error('该菜单禁止生成');
        }
        
        $fieldList = Field::where('menu_id', $menu_id)->order('sortid asc')->select()->toArray();
        $actionList = Action::where('menu_id', $menu_id)->order('sortid asc')->select()->toArray();
        
        $application = Application::where('app_id', $menuInfo['app_id'])->find()->toArray();
        
        $rootPath = app()->getRootPath();
        
        if ($application['app_type'] == 2) {
            if (!is_dir($rootPath . '/app/' . $application['app_dir'])) {
                throw new ValidateException('请先生成应用', 422);
            }
        }
        
        $pk = Db::connect($menuInfo['connect'])->name($menuInfo['table_name'])->getPk();
        
        $data['fieldList'] = $fieldList;
        $data['actionList'] = $actionList;
        $data['application'] = $application;
        $data['pk'] = $pk;
        $data['menuInfo'] = $menuInfo;
        $data['actions'] = Config::actionList();
        $data['extend'] = $this->getExtend($actionList);
        $data['comment'] = config('my.comment');
        $data['dbtype'] = config('database.connections.' . $menuInfo['connect'] . '.type');
        $data['dbpre'] = config('database.connections.' . $menuInfo['connect'] . '.prefix');
        $data['framwork'] = 'tp8';
        $data['hookStatus'] = true;
        $data['version'] = self::VERSION;
        
        $secrect = $this->getSecrect();
        
        if (empty($secrect['appid']) || empty($secrect['secrect'])) {
            $this->error('请在工具管理-秘钥管理中配置appid及secrect');
        }
        
        $data['secrect'] = $secrect;
        $data['timestmp'] = time();
        
        $data['sign'] = md5(md5(json_encode($data, JSON_UNESCAPED_UNICODE) . $secrect['secrect']));
        
        $data['domain'] = $_SERVER['HTTP_HOST'];
        $data['base_config'] = Db::name('base_config')->column('data', 'name');
        $res = $this->curlRequest('http://tfadmin.tiefen.net/produce/CreateCode/buildCode', 'POST', $data);
        $res = str_replace("search_visible:true,", "search_visible:false,", $res);
        $res = str_replace("<el-table-column", "<el-table-column header-align='center'", $res);
        $ret = $res;
        $res = json_decode($res, true);
        
        $access53 = [];
        $access54 = [];
        foreach ($data['actionList'] as $actionListItem1) {
            if ($actionListItem1['type'] == 53) {
                $caozuo_field = $actionListItem1['fields'];
                $ex_caozuo_field = $actionListItem1['sql'];
                $access53[] = '"/' . $application['app_dir'] . '/' . $menuInfo['controller_name'] . '/' . $actionListItem1['action_name'] . '.html"';
            }
            if ($actionListItem1['type'] == 54) {
                $access54[] = '"/' . $application['app_dir'] . '/' . $menuInfo['controller_name'] . '/' . $actionListItem1['action_name'] . '.html"';
            }
        }
        
        $access = array_unique(array_merge($access53, $access54));
        $access = implode(',', $access);
        $replace = "if(!in_array(session('admin.role_id'),[1]) && empty(array_intersect(session('admin.access'),[" . $access . "]))){";
        if (!empty($access53)) {
            $replace_end = $replace;
            $access = $access53;
            $access = implode(',', $access);
            $replace = "if(!in_array(session('admin.role_id'),[1]) && !empty(array_intersect(session('admin.access'),[" . $access . "]))){\n\t\t\t\t\$query->whereRaw('";
            foreach ($data['fieldList'] as $fieldList) {
                if ($fieldList['type'] == 30) {
                    $replace .= "{$fieldList['field']} = '.session('admin.{$fieldList['field']}').' or ";
                }
            }
            $replace .= "FIND_IN_SET('.session('admin.{$ex_caozuo_field}').', {$caozuo_field})');\n\t\t\t}\n\t\t\t" . $replace_end;
        }
        $res['controller']['content'] = str_replace("if(!in_array(session('admin.role_id'),[1])){", $replace, $res['controller']['content']);
        
        $res['jscomponent'][2]['content'] = str_replace("ismobile()?'90px':'16%'", "ismobile()?'90px':'88px'", $res['jscomponent'][2]['content']);
        $res['jscomponent'][3]['content'] = str_replace("ismobile()?'90px':'16%'", "ismobile()?'90px':'88px'", $res['jscomponent'][3]['content']);

//        if ($type == 1) {
        if ($res['status'] == 411) {
            throw new ValidateException($res['msg']);
        }
        
        if (!is_array($res['model'])) {
            halt($ret);
        }
//        }
        
        $rootPath = app()->getRootPath();
        
        //删除路由
        $isToken = 0;
        foreach ($actionList as $v) {
            if ($v['api_auth']) {
                $isToken = 1;
            }
        }
        if (!$isToken) {
            @unlink($rootPath . '/app/' . $application['app_dir'] . '/route/' . $this->getRouteName(strtolower($menuInfo['controller_name'])) . '.php');
        }
        
        $handle = new Build();
        
        foreach ($res as $key => $val) {
            if ($key == 'view') {
                foreach ($val as $v) {
                    filePutContents($v['content'], $rootPath . '/' . $v['path'], 2);
                }
            } else if ($key == 'jscomponent') {
                foreach ($val as $k => $v) {
                    if (isset($v['super'])) {
                        filePutContents($v['content'], $rootPath . '/' . $v['path'], 2);
                    } else {
                        filePutContents($v['content'], $rootPath . '/public/components/' . $v['path'], 2);
                    }
                }
            } else if ($key == 'route') {
                filePutContents($val['content'], $rootPath . '/' . $val['path'], 2);
            } else {
                $handle->create($val['content'], $rootPath . '/' . $val['path']);
            }
        }
        return true;
    }
    
    //根据表生成
    public function createByTable()
    {
        $data = $this->request->post();
        
        $connect = $data['connect'];
        $prefix = config('database.connections.' . $connect . '.prefix');
        
        $pk = Db::connect($connect)->name($data['table_name'])->getPk();
        
        $list = Db::connect($connect)->query('show full columns from ' . $prefix . $data['table_name']);
        
        if ($pk) {
            $menuInfo = [
                'controller_name' => $this->setControllerName($data['table_name']),
                'title' => $data['table_name'],
                'pk' => $pk,
                'table_name' => $data['table_name'],
                'create_code' => 1,
                'status' => 1,
                'create_table' => 0,
                'app_id' => $data['app_id'],
                'connect' => $connect,
                'page_type' => 1
            ];
            
            try {
                Db::startTrans();
                
                $res = Menu::create($menuInfo);
                
                Menu::update(['menu_id' => $res->menu_id, 'sortid' => $res->menu_id]);
                
                $actionInfo = Config::actionList();
                foreach ($actionInfo as $key => $val) {
                    if ($val['default_create'] && !in_array($val['type'], [10, 11])) {
                        $actionInfo[$key]['menu_id'] = $res->menu_id;
                        $actionInfo[$key]['sortid'] = $key + 1;
                    } else {
                        unset($actionInfo[$key]);
                    }
                    if ($data['app_type'] == 2 && $val['type'] == 12) {
                        unset($actionInfo[$key]);
                    }
                }
                
                (new Action)->saveAll($actionInfo);
                
                $fieldInfo = [];
                foreach ($list as $k => $v) {
                    $fieldInfo[$k]['menu_id'] = $res->menu_id;
                    $fieldInfo[$k]['title'] = $v['Comment'] ? $v['Comment'] : $v['Field'];
                    $fieldInfo[$k]['field'] = $v['Field'];
                    $fieldInfo[$k]['type'] = 1;
                    $fieldInfo[$k]['list_type'] = 1;
                    $fieldInfo[$k]['list_show'] = 2;
                    $fieldInfo[$k]['search_type'] = 0;
                    $fieldInfo[$k]['post_status'] = 1;
                    $fieldInfo[$k]['create_table_field'] = 0;
                    $fieldInfo[$k]['sortid'] = $k + 1;
                    $fieldInfo[$k]['datatype'] = preg_split("/\(.*\)+/", $v['Type'])[0];
                    preg_match_all("/\((.*)\)/", $v['Type'], $all);
                    $fieldInfo[$k]['length'] = $all[1][0];
                    if ($v['Field'] == $pk) {
                        $fieldInfo[$k]['width'] = 70;
                        $fieldInfo[$k]['post_status'] = 0;
                    }
                }
                
                (new Field)->saveAll($fieldInfo);
                
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                throw new ValidateException ($e->getMessage());
            }
            if ($this->createCode($res->menu_id, $data['type'])) {
                return json(['status' => 200]);
            }
        } else {
            throw new ValidateException ('数据表主键不能为空');
        }
    }
    
    //获取关联表信息
    private function getExtend($actionList)
    {
        $with_join = [];
        foreach ($actionList as $v) {
            if ($v['with_join'] && in_array($v['type'], [2, 3, 5, 11])) {
                foreach (json_decode($v['with_join'], true) as $n) {
                    $n['action_id'] = $v['id'];
                    $n['action_type'] = $v['type'];
                    $n['fields'] = $this->getExtendFields($n);
                    array_push($with_join, $n);
                }
            }
        }
        
        return $with_join;
    }
    
    
    private function getExtendFields($val)
    {
        $menuInfo = Menu::field('menu_id,table_name')->where('controller_name', $val['relative_table'])->find();
        $fieldList = Field::where('menu_id', $menuInfo['menu_id'])->order('sortid asc')->select()->toArray();
        foreach ($fieldList as $k => $v) {
            $fieldList[$k]['belong_table'] = $val['relative_table'];
            $fieldList[$k]['table_name'] = $menuInfo['table_name'];
            if (!in_array($v['field'], $val['fields'])) {
                unset($fieldList[$k]);
            }
        }
        return $fieldList;
    }
    
    
    //检测cms模型字段
    public function checkCmsField()
    {
        $field = $this->request->post('field');
        $list = Db::query('show full columns from ' . config('database.connections.mysql.prefix') . 'content');
        foreach ($list as $v) {
            $arr[] = $v['Field'];
        }
        if (in_array($field, $arr)) {
            throw new ValidateException('主表该字段已存在，请更换字段');
        }
        
        return json(['status' => 200]);
    }
    
    //获取控制器名称
    public function setControllerName($controller_name)
    {
        if (strpos($controller_name, '/') > 0) {
            $arr = explode('/', $controller_name);
            $controller_name = ucfirst($arr[0]) . '/' . ucfirst($arr[1]);
        } else {
            $controller_name = ucfirst($controller_name);
        }
        
        return str_replace('_', '', $controller_name);
    }
    
    //获取应用名 以及数据表名称
    public function getAppInfo()
    {
        $controller_name = $this->request->post('controller_name');
        $data['table_name'] = $this->getTableName($controller_name);
        $data['pk'] = $data['table_name'] ? $data['table_name'] . '_id' : '';
        $data['app_name'] = app('http')->getName();
        $data['status'] = 200;
        return json($data);
    }
    
    
    //获取应用名 以及数据表名称
    public function getAppType()
    {
        $appid = $this->request->post('app_id');
        $data['status'] = 200;
        $data['data'] = Application::where('app_id', $appid)->value('app_type');
        return json($data);
    }
    
    //获取应用名 以及数据表名称
    public function getDbType()
    {
        $dbname = $this->request->post('dbname');
        $dbtype = config('database.connections.' . $dbname . '.type');
        $data['status'] = 200;
        $data['data'] = $dbtype;
        return json($data);
    }
    
    private function getTableName($controller_name)
    {
        if ($controller_name && strpos($controller_name, '/') > 0) {
            $controller_name = explode('/', $controller_name)[1];
        }
        return $controller_name;
    }
    
    
    //获取秘钥信息
    private function getSecrect()
    {
        $info = Db::name('secrect')->select()->column('data', 'name');
        return $info;
    }
    
    public static function getFieldStatus($tablename, $field, $connect)
    {
        $list = Db::connect($connect)->query('show columns from ' . $tablename);
        foreach ($list as $v) {
            $arr[] = $v['Field'];
        }
        if (in_array($field, $arr)) {
            return true;
        }
    }
    
    //获取默认钩子方法路径
    public function getHookPath()
    {
        $menu_id = $this->request->post('menu_id');
        $action_name = $this->request->post('actionName');
        $type = $this->request->post('type');
        
        $controllerInfo = Menu::where("menu_id", $menu_id)->field("app_id,controller_name")->find();
        $controller_name = $controllerInfo->controller_name;
        
        if ($type == 1) {
            $pre = 'befor';
        } else {
            $pre = 'after';
        }
        
        $appInfo = Application::where("app_id", $controllerInfo->app_id)->value("app_dir");
        $data = 'app/' . $appInfo . '/hook/' . $controller_name . '@' . $pre . ucfirst($action_name);
        return json(['staus' => 200, 'data' => $data]);
    }
    
    //curl请求方法
    private function go_curl($url, $type, $data = false, &$err_msg = null, $timeout = 20, $cert_info = array())
    {
        $type = strtoupper($type);
        if ($type == 'GET' && is_array($data)) {
            $data = http_build_query($data);
        }
        $option = array();
        if ($type == 'POST') {
            $option[CURLOPT_POST] = 1;
        }
        if ($data) {
            if ($type == 'POST') {
                $option[CURLOPT_POSTFIELDS] = $data;
            } elseif ($type == 'GET') {
                $url = strpos($url, '?') !== false ? $url . '&' . $data : $url . '?' . $data;
            }
        }
        $option[CURLOPT_URL] = $url;
        $option[CURLOPT_FOLLOWLOCATION] = TRUE;
        $option[CURLOPT_MAXREDIRS] = 4;
        $option[CURLOPT_RETURNTRANSFER] = TRUE;
        $option[CURLOPT_TIMEOUT] = $timeout;
        //设置证书信息
        if (!empty($cert_info) && !empty($cert_info['cert_file'])) {
            $option[CURLOPT_SSLCERT] = $cert_info['cert_file'];
            $option[CURLOPT_SSLCERTPASSWD] = $cert_info['cert_pass'];
            $option[CURLOPT_SSLCERTTYPE] = $cert_info['cert_type'];
        }
        //设置CA
        if (!empty($cert_info['ca_file'])) {
            // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
            $option[CURLOPT_SSL_VERIFYPEER] = 1;
            $option[CURLOPT_CAINFO] = $cert_info['ca_file'];
        } else {
            // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
            $option[CURLOPT_SSL_VERIFYPEER] = 0;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $option);
        $response = curl_exec($ch);
        $curl_no = curl_errno($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);
        // error_log
        if ($curl_no > 0) {
            if ($err_msg !== null) {
                $err_msg = '(' . $curl_no . ')' . $curl_err;
            }
        }
        return $response;
    }
    
    
    //多级控制器 获取控制其名称
    function getRouteName($controller_name)
    {
        if ($controller_name && strpos($controller_name, '/') > 0) {
            $controller_name = str_replace('/', '_', $controller_name);
        }
        return $controller_name;
    }
    
    
    // 更新备注内容杨爽
    public function updateActionRemark()
    {
        $id = $this->request->post('id/d');
        $remark = $this->request->post('remark/s', '', 'trim');
        $remark_desc = $this->request->post('$remark_desc/s', '', 'trim');
        
        if (!$id) {
            return json(['status' => 400, 'msg' => 'ID不能为空']);
        }
        
        try {
            $result = Action::where('id', $id)
                ->update(['remark' => $remark]);
            
            if ($result !== false) {
                return json(['status' => 200, 'msg' => '备注更新成功']);
            }
            return json(['status' => 500, 'msg' => '备注更新失败']);
        } catch (\Exception $e) {
            return json(['status' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    // 获取备注内容杨爽
    public function getActionRemark()
    {
        $id = $this->request->post('id/d');
        
        if (!$id) {
            return json(['status' => 400, 'msg' => 'ID不能为空']);
        }
        
        try {
            $remark = Action::where('id', $id)
                ->value('remark');
            
            return json([
                'status' => 200,
                'data' => $remark ?: ''
            ]);
        } catch (\Exception $e) {
            return json(['status' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    
    /**
     * 获取备注版本记录
     */
    public function getRemarkVersions()
    {
        $actionId = $this->request->post('actionId/d', 0);
        
        try {
            $versions = Db::name('action_remarks')
                ->where('action_id', $actionId)
                ->order('create_time DESC')
                ->select()
                ->toArray();
            
            foreach ($versions as &$version) {
                $version['version_desc'] = $version['description'] ?? '无描述'; // 确保有默认值
                $version['create_time'] = date('Y-m-d H:i:s', $version['create_time']);
            }
            
            return json([
                'status' => 200,
                'data' => $versions
            ]);
            
        } catch (\Exception $e) {
            return json([
                'status' => 500,
                'msg' => '获取版本记录失败: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 保存备注版本
     */
    public function saveRemarkVersion()
    {
        $data = $this->request->post();
        
        Db::transaction(function () use ($data) {
            // 更新主表备注
            Db::name('action')
                ->where('id', $data['actionId'])
                ->update([
                    'remark' => $data['content'],
                    'remark_desc' => $data['description']
                ]);
            
            // 插入版本记录
            Db::name('action_remarks_versions')->insert([
                'action_id' => $data['actionId'],
                'content' => $data['content'],
                'description' => $data['description'],
                'version_desc' => $data['versionDesc'],
                'create_time' => time()
            ]);
        });
        
        return json(['status' => 200]);
    }
    
    /**
     * 更新完整备注信息（含描述和代码）
     */
    public function updateFullRemark()
    {
        $data = $this->request->post();
        
        Db::name('action')
            ->where('id', $data['id'])
            ->update([
                'remark' => $data['codeContent'],
                'remark_desc' => $data['description']
            ]);
        
        return json(['status' => 200]);
    }
    
    public static function curlRequest($url, $method = 'GET', $data = [], $headers = [])
    {
        $ch = curl_init();
        // 在headers中添加
//        $headers[] = 'Transfer-Encoding: chunked';
        $headers[] = 'Content-Type: application/json';
        // 设置请求的URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF8');
        // 根据请求类型设置不同的选项
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ($method === 'GET' && !empty($data)) {
            // 如果是GET请求且带有参数，则将参数附加到URL上
            $urlWithParams = $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $urlWithParams);
        }
        
        // 设置是否返回响应结果而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // 设置自定义的请求头
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        // 执行curl请求并获取响应
        $response = curl_exec($ch);
        
        // 检查是否有错误发生
        if (curl_errno($ch)) {
            $response = null;
            // 关闭curl会话
            curl_close($ch);
            throw new Exception('Error:' . curl_error($ch));
        }
        
        // 关闭curl会话
        curl_close($ch);
        
        // dump($response);exit;
        // 尝试将响应解码为JSON（如果可能）
        $decodedResponse = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $response;
        } else {
            return $response;
        }
    }
}

