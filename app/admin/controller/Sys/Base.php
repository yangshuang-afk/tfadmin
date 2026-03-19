<?php

namespace app\admin\controller\Sys;

use app\admin\model\Baseconfig as BaseconfigModel;
use app\admin\service\Elasticsearch as EsService;
use think\exception\ValidateException;
use app\admin\controller\Sys\Build;
use app\admin\controller\Sys\model\Application;
use app\admin\controller\Sys\model\Field;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Sys\model\Action;
use app\admin\controller\Admin;
use think\facade\Db;
use think\facade\Log;

class Base extends Admin
{
    const VERSION = '20240825';

    private string $url = 'http://tfadmin.tiefen.net';
    private string $groupTableSuffix = "_tf_approval_group";
    private string $flowTableSuffix = "_tf_approval_flow";

    /**
     * Elasticsearch服务实例
     * @var EsService|null
     */
    private $esService = null;


    public function initialize() {
        parent::initialize();
        config(['view_path' => app_path()], 'view');
    }

    //应用列表
    public function applicationList() {
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
    public function createApplication() {
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
    public function updateApplication() {
        $data = $this->request->post();
        try {
            Application::update($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }

    //获取应用
    public function getApplicationInfo() {
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
    public function secrect() {
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
    function getSecrectInfo() {
        $res = db('secrect')->column('data', 'name');
        $data['status'] = 200;
        $data['data'] = $res;
        return json($data);
    }

    //获取主键ID
    public function getPk() {
        $data = $this->request->post('tablename');
        try {
            $res = Db::name($data)->getPk();
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200, 'data' => $res]);
    }

    //生成应用
    public function buildApplication() {
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
        $res = $this->curlRequest($this->url . '/produce/createApp/buildCode', 'POST', $info);

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
    public function deleteApplication() {
        $data = $this->request->post();
        try {
            Application::destroy($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }

    private function getTpl($appid, $menu) {
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
    function menu() {
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
    public function createMenu() {
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
    public function updateMenu() {
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
    public function updateMenuExt() {
        $data = $this->request->post();
        // es验证
        if (isset($data['enable_es']) && !empty($data['enable_es'])) {
            if (empty(config('my.esdb_hostname'))
                || empty(config('my.esdb_username'))
                || empty(config('my.esdb_password'))) {
                throw new ValidateException("请在 控制台-配置管理-系统配置 中配置es相关信息");
            }
        }

        try {
            $res = Menu::update($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }


    //获取菜单信息
    public function getMenuInfo() {
        $data = $this->request->post('menu_id');
        try {
            $res = menu::find($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200, 'data' => $res]);
    }

    //删除菜单
    public function deleteMenu() {
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
    public function copyMenu() {
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
    public function fieldList() {
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

            $res = Field::where([
                'menu_id' => $menu_id,
                'field_show' => 1
            ])
                ->order('sortid asc')
                ->paginate(['list_rows' => $limit, 'page' => $page])
                ->toArray();
            if (!empty($res['data']) && is_array($res['data'])) {
                foreach ($res['data'] as &$fieldRow) {
                    $indexValue = strtolower(trim((string)($fieldRow['indexdata'] ?? '')));
                    $fieldRow['indexdata'] = in_array($indexValue, ['index', 'unique'], true) ? $indexValue : '';
                }
                unset($fieldRow);
            }

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
    public function createField() {
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
    public function batchCreateField() {
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
    protected function alterTableField($tableName, $field) {
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
    public function updateField() {
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
    public function updateFieldExt() {
        $data = $this->request->post();
        try {
            $res = Field::update($data);
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }


    //字段同步到其他应用
    public function copyField() {
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
    public function getFieldInfo() {
        $data = $this->request->post();
        try {
            $res = Field::where($data)->find()->toArray();
        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        $res['validate'] = explode(',', $res['validate']);
        $res['item_config'] = json_decode($res['item_config'], true);
        $res['other_config'] = json_decode($res['other_config'], true);
        $indexValue = strtolower(trim((string)($res['indexdata'] ?? '')));
        $res['indexdata'] = in_array($indexValue, ['index', 'unique'], true) ? $indexValue : '';
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
    public function deleteField() {
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
    public function actionList() {
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

            $res = Action::where([
                'menu_id' => $menu_id,
                'action_show' => 1
            ])
                ->order('sortid asc')
                ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ]);
            $data['data'] = $res;
            $data['status'] = 200;
            $data['actionList'] = Config::actionList();
            $data['menu_title'] = Menu::where('menu_id', $menu_id)->value('title');
            $data['app_name'] = Application::where('app_id', $appid)->value('application_name');
            return json($data);
        }
    }

    //获取提交字段
    public function getPostField() {
        $menu_id = $this->request->post('menu_id');
        $menuInfo = Menu::find($menu_id);
        // 审批流相关数据
        $flowMenu_id = 0;
        $groupFields = [];
        $flowTableList = [];

        $list = [];
        $fieldlist = Field::field('type,field,title,post_status')->where('menu_id', $menu_id)->order('sortid asc')->select()->toArray();
        foreach ($fieldlist as $k => $v) {
            if ($v['post_status'] == 1) {
                array_push($list, $v);
            }
        }

        $pk = Db::connect($menuInfo['connect'])->name($menuInfo['table_name'])->getPk();

        $model_fields = array_merge([['field' => $pk, 'title' => '编号']], $list);

        $tableList = Menu::where('table_name', '<>', '')
            ->where('app_id', $menuInfo['app_id'])
            ->field('controller_name')
            ->select()
            ->toArray();
        $actionList = Action::where('menu_id', $menu_id)->select();
        $with_join = [];

        foreach ($actionList as $v) {
            if ($v['with_join'] && in_array($v['type'], [2, 3])) {
                foreach (json_decode($v['with_join'], true) as $n) {
                    $n['fields'] = $this->getExtendFields($n);
                    foreach ($n['fields'] as $m) {
                        array_push($with_join, $m);
                    }
                }
            }
            if ($v['type'] == 57) {
                $v_other_config = json_decode($v['other_config'], true);
                $flowMenu_id = $v_other_config['flow_table'];
            }
        }

        // 存在审批流 查字段，查流
        $flowMenu_all = Db::name('field')->where('type', 42)->column("menu_id");
        $flowTableList = Menu::whereIn('menu_id', $flowMenu_all)
            ->where('app_id', $menuInfo['app_id'])
            ->select()
            ->toArray();

        if ($flowMenu_id) {
            $field_sql = Db::name('field')->where(['menu_id' => $flowMenu_id, 'type' => 2])->order('id desc')->value('sql');
            $groupFields = $this->query($field_sql, 'mysql');
        } else if (!empty($flowMenu_all[0])) {
            $field_sql = Db::name('field')->where(['menu_id' => $flowMenu_all[0], 'type' => 2])->order('id desc')->value('sql');
            if ($field_sql) $groupFields = $this->query($field_sql, 'mysql');
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
        return json([
            'status' => 200,
            'dbtype' => $dbtype,
            'data' => $list,
            'jump_field' => $fieldlist,
            'model_fields' => $model_fields,
            'search_field' => $fieldlist,
            'tab_fields' => $tab_fields,
            'tableList' => $tableList,
            'sms_list' => Config::sms_list(),
            'flowTableList' => $flowTableList,
            'groupFields' => $groupFields,
        ]);
    }

    //创建方法
    public function createAction() {
        $data = $this->request->post();

        $this->validate($data, \app\admin\controller\Sys\validate\Action::class);

        $with_join_feilds = [];
        if ($data['with_join']) {
            foreach ($data['with_join'] as $k => $v) {
                if ($v['_table_fields']) unset($data['with_join'][$k]['_table_fields']);
                $menuInfo = Menu::field('connect,table_name')->where('controller_name', $v['relative_table'])->find();
                $data['with_join'][$k]['table_name'] = $menuInfo['table_name'];
                $data['with_join'][$k]['connect'] = $menuInfo['connect'];

                $with_menu = Menu::where('controller_name', $v['relative_table'])->find();
                foreach ($v['fields'] as $with_join_field) {
                    $filed_detail = Field::where([
                        'menu_id' => $with_menu['menu_id'],
                        'field' => $with_join_field,
                    ])
                        ->find();
                    // 确保转换为数组
                    if ($filed_detail instanceof \think\Model) {
                        $field_data = $filed_detail->toArray();
                    } else {
                        $field_data = (array)$filed_detail;
                    }
                    unset($field_data['id']);
                    $field_data['menu_id'] = $data['menu_id'];
                    $field_data['sortid'] = 99999;
                    $field_data['post_status'] = 0;
                    $field_data['create_table_field'] = 0;
                    $field_data['belong_table'] = $with_menu['table_name'];
                    $field_data['field'] = lcfirst(str_replace('_', '', ucwords($v['fk'], '_'))) . "__" . $field_data['field'];
                    $with_join_feilds[] = $field_data;
                }
            }
        }

        // 处理超级页面数据
        if ($data['type'] == 55) { // 55是超级页面类型
            $data['q_template'] = $data['q_template'] ?? '';
            $data['h_php'] = $data['h_php'] ?? '';
        }


        /**
         * 审批事件验证
         */
        if ($data['type'] == 57) {
            if (!isset($data['other_config']['flow_join'])
                || empty($data['other_config']['flow_join'])) {
                throw new ValidateException("请选择关联字段");
            }
            if (!isset($data['other_config']['flow_table'])
                || empty($data['other_config']['flow_table'])) {
                throw new ValidateException("请选择审核关联表");
            }
            if (!isset($data['other_config']['flow_filed'])
                || empty($data['other_config']['flow_filed'])) {
                throw new ValidateException("请选择对应字段");
            }
            if (!isset($data['other_config']['flow_group_field'])
                || empty($data['other_config']['flow_group_field'])) {
                throw new ValidateException("请选择关联流程组");
            }
        }

        // 审批流
        if ($data['type'] == 60) {
            if (!isset($data['other_config']['flow_group'])
                || empty($data['other_config']['flow_group'])) {
                throw new ValidateException("请选择关联字段");
            }
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
            $data['dialog_size'] = '85%';
        }

        if (in_array($data['type'], [3, 4])) {
            $data['list_button_status'] = 1;
        }

        try {
            $count = Action::where('menu_id', $data['menu_id'])
                ->where('action_name', $data['action_name'])
                ->count();
            if ($count > 0) {
                throw new ValidateException ('方法名已经存在');
            }
            $res = Action::create($data);

            if ($res->id) {
                Action::update(['id' => $res->id, 'sortid' => $res->id]);


                $actionInfo = db("action")
                    ->where('id', $res->id)
                    ->find();
                // 数据列表同步到详情
                if ($actionInfo['type'] == 1) {
                    if (!empty($with_join_feilds)) {
                        (new Field)->saveAll($with_join_feilds);
                    }

                    $detail_ids = Action::where([
                        'type' => 5,
                        'menu_id' => $actionInfo['menu_id']
                    ])
                        ->whereNotNull('with_join')
                        ->where('with_join', '<>', '')
                        ->column('id');
                    if (!empty($detail_ids)) {
                        Action::whereIn('id', $detail_ids)->update(['with_join' => $data['with_join']]);
                    }
                }


                if ($data['type'] == 20) {
                    $menuInfo = db("menu")
                        ->where('menu_id', $data['menu_id'])
                        ->find();
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

                /**
                 * 审批事件
                 */
                if ($data['type'] == 57) {
                    $data['id'] = $res->id;
                    $this->createFlowEvenTable($data);
                }

                /**
                 * 审批流
                 */
                if ($data['type'] == 60) {
                    $data['id'] = $res->id;
                    $this->createFlowDataTable($data);
                }
            }


        } catch (\Exception $e) {
            abort(501, $e->getMessage());
        }
        return json(['status' => 200]);
    }

    //快速创建方法
    public function quckCreateAction() {
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
    public function updateAction() {
        $data = $this->request->post();
        $this->validate($data, \app\admin\controller\Sys\validate\Action::class);

        $with_join_feilds = [];
        if (!empty($data['with_join'])) {
            foreach ($data['with_join'] as $k => $v) {
                if ($v['_table_fields']) unset($data['with_join'][$k]['_table_fields']);
                $menuInfo = Menu::field('connect,table_name')->where('controller_name', $v['relative_table'])->find();
                $data['with_join'][$k]['table_name'] = $menuInfo['table_name'];
                $data['with_join'][$k]['connect'] = $menuInfo['connect'];
                $with_menu = Menu::where('controller_name', $v['relative_table'])->find();
                foreach ($v['fields'] as $with_join_field) {
                    $filed_detail = Field::where([
                        'menu_id' => $with_menu['menu_id'],
                        'field' => $with_join_field,
                    ])
                        ->find();
                    // 确保转换为数组
                    if ($filed_detail instanceof \think\Model) {
                        $field_data = $filed_detail->toArray();
                    } else {
                        $field_data = (array)$filed_detail;
                    }
                    unset($field_data['id']);
                    $field_data['search_type'] = $with_join_field['search_type'];
                    $field_data['list_show'] = $with_join_field['list_show'];
                    $field_data['menu_id'] = $data['menu_id'];
                    $field_data['sortid'] = 99999;
                    $field_data['post_status'] = 0;
                    $field_data['create_table_field'] = 0;
                    $field_data['belong_table'] = $with_menu['table_name'];
                    $field_data['field'] = lcfirst(str_replace('_', '', ucwords($v['fk'], '_'))) . "__" . $field_data['field'];
                    $with_join_feilds[] = $field_data;
                }
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

        /**
         * 审批事件验证
         */
        if ($data['type'] == 57) {
            if (!isset($data['other_config']['flow_join'])
                || empty($data['other_config']['flow_join'])) {
                throw new ValidateException("请选择关联字段");
            }
            if (!isset($data['other_config']['flow_table'])
                || empty($data['other_config']['flow_table'])) {
                throw new ValidateException("请选择审核关联表");
            }
            if (!isset($data['other_config']['flow_filed'])
                || empty($data['other_config']['flow_filed'])) {
                throw new ValidateException("请选择对应字段");
            }
            if (!isset($data['other_config']['flow_group_field'])
                || empty($data['other_config']['flow_group_field'])) {
                throw new ValidateException("请选择关联流程组");
            }
        }

        // 审批流
        if ($data['type'] == 60) {
            if (!isset($data['other_config']['flow_group'])
                || empty($data['other_config']['flow_group'])) {
                throw new ValidateException("请选择关联字段");
            }
        }

        $data['list_filter'] = getItemData($data['list_filter']);
        $data['tab_config'] = getItemData($data['tab_config']);
        $data['with_join'] = getItemData($data['with_join']);
        $data['fields'] = $data['fields'] ? implode(',', $data['fields']) : '';
        $data['other_config'] = json_encode($data['other_config']);

        try {
            $actionInfo = db("action")->where('id', $data['id'])->find();
            $old_with_joins = json_decode($actionInfo['with_join'], true);

            $delete_field_ids = [];
            foreach ($old_with_joins as $old_with_join) {
                foreach ($old_with_join['fields'] as $del_field) {
                    if(!is_array($del_field)){
                        continue;
                    }
                    $with_menu = Menu::where('controller_name', $old_with_join['relative_table'])->find();
                    $delete_field_id = Field::where(
                        [
                            'field' => lcfirst(str_replace('_', '', ucwords($with_menu['table_name'], '_'))) . "__" . $del_field['field'],
                            'menu_id' => $actionInfo['menu_id'],
                            'create_table_field' => 0,
                        ]
                    )
                        ->value('id');
                    if ($delete_field_id) $delete_field_ids[] = $delete_field_id;
                }
            }

            $res = Action::update($data);

            // 数据列表同步到详情
            if ($actionInfo['type'] == 1) {
                // 删除原虚拟字段
                if (!empty($delete_field_ids)) (new Field)->whereIn('id', $delete_field_ids)->delete();
                // 创建字段
                if (!empty($with_join_feilds)) (new Field)->saveAll($with_join_feilds);
                $detail_ids = Action::where(
                    [
                        'type' => 5,
                        'menu_id' => $actionInfo['menu_id'],
                    ])
                    ->column('id');
                if (!empty($detail_ids)) {
                    Action::whereIn('id', $detail_ids)->update(['with_join' => $data['with_join']]);
                }
            }

            if ($data['type'] == 20) {
                $menuInfo = db("menu")
                    ->where('menu_id', $actionInfo['menu_id'])
                    ->find();
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
    public function updateActionExt() {
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
    public function getActionInfo() {
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
    public function deleteAction() {
        $data = $this->request->post();
        $list = Action::where($data)->field('action_name')->select()->toArray();
        $info = db("action")->where($data)->select()->toArray();
        $rootPath = app()->getRootPath();
        $menu = Menu::find($data['menu_id']);
        $application = Application::find($menu['app_id']);
        $subtable = null;

        /**
         * 删除关联审核流
         */
        $fieldDeleteList = [];
        $flow = Action::where($data)->find();
        if (in_array($flow['type'], [57, 60])) {
            $flow_list = Action::where("action_pid", $flow['id'])
                ->field('action_name')
                ->select()
                ->toArray();
            if ($flow_list) $list = array_merge($list, $flow_list);
            $flow_actions = Action::where("action_pid", $flow['id'])
                ->select()
                ->toArray();
            if ($flow_actions) $info = array_merge($info, $flow_actions);
        }

        // 先收集要删除的文件，但不实际删除
        $filesToDelete = [];

        foreach ($list as $key => $v) {
            if ($menu['controller_name'] && $v['action_name']) {
                $filePath = $rootPath . '/public/components/' . $application['app_dir'] . '/' . strtolower($menu['controller_name']) . '/' . $v['action_name'] . '.js';
                if (file_exists($filePath)) {
                    $filesToDelete[] = $filePath;
                }
            }

            if ($info[$key]['type'] == 55) {
                $filePath = $rootPath . "app/" . $application['app_dir'] . "/view/" . getViewName($menu['controller_name']) . "/" . $v['action_name'] . ".html";
                if (file_exists($filePath)) {
                    $filesToDelete[] = $filePath;
                }
            }
        }

        try {
            // 开始事务
            Db::startTrans();

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

            /**
             * 删除关联审核流
             */
            if ($flow['type'] == 57) {
                $connect = $menu['connect'] ? $menu['connect'] : config('database.default');
                $prefix = config('database.connections.' . $connect . '.prefix');

                // 子表
                $subtable = Menu::where([
                    'pid' => $menu['menu_id'],
                    'flow_subtable' => 1
                ])
                    ->order('menu_id desc')
                    ->find();

                // 收集子表相关文件
                if ($subtable) {
                    $subtableFiles = [
                        $rootPath . "app/" . $application['app_dir'] . "/controller/" . $subtable['controller_name'] . ".php",
                        $rootPath . "app/" . $application['app_dir'] . "/model/" . $subtable['controller_name'] . ".php",
                        $rootPath . "app/" . $application['app_dir'] . "/hook/" . $subtable['controller_name'] . ".php",
                        $rootPath . "app/" . $application['app_dir'] . "/validate/" . $subtable['controller_name'] . ".php"
                    ];

                    foreach ($subtableFiles as $file) {
                        if (file_exists($file)) {
                            $filesToDelete[] = $file;
                        }
                    }

                    // 删除子表相关数据
                    Db::connect($connect)->name('menu')->where('menu_id', $subtable['menu_id'])->delete();
                    Db::connect($connect)->name('field')->where('menu_id', $subtable['menu_id'])->delete();
                    Db::connect($connect)->name('action')->where('menu_id', $subtable['menu_id'])->delete();
                }

                // 要删除的审批字段列表（与创建时保持一致）
                $mainTableName = $menu['table_name'];
                $approvalFields = [
                    "{$mainTableName}_apply_user_id_tfadmin",
                    "{$mainTableName}_apply_now_tfadmin",
                    "{$mainTableName}_apply_next_tfadmin",
                    "{$mainTableName}_apply_user_tfadmin",
                    "{$mainTableName}_apply_progress_tfadmin",
                    "{$mainTableName}_status_tfadmin",
                    "{$mainTableName}_remark_tfadmin"
                ];

                // 删除主表中的审批字段
                foreach ($approvalFields as $fieldName) {
                    // 检查字段是否存在
                    $checkSql = "SHOW COLUMNS FROM `{$prefix}{$mainTableName}` LIKE '{$fieldName}'";
                    $result = Db::connect($connect)->query($checkSql);

                    // 如果字段存在，则删除
                    if (!empty($result)) {
                        $dropColumnSql = "ALTER TABLE `{$prefix}{$mainTableName}` DROP COLUMN `{$fieldName}`";
                        Db::connect($connect)->execute($dropColumnSql);
                        Db::name('field')->where('field', $fieldName)->delete();
                    }
                }

                if ($subtable) {
                    $dropSql = "DROP TABLE IF EXISTS `" . $prefix . $subtable['table_name'] . "`";
                    Db::connect($connect)->execute($dropSql);
                }
            }

            if ($flow['type'] == 60) {
                $connect = $menu['connect'] ? $menu['connect'] : config('database.default');
                $prefix = config('database.connections.' . $connect . '.prefix');

                // 子表
                $subtables = Menu::where([
                    'pid' => $menu['menu_id'],
                    'flow_subtable' => 1
                ])
                    ->order('menu_id desc')
                    ->select()
                    ->toArray();

                foreach ($subtables as $subtable) {
                    if ($subtable) {
                        $subtableFiles = [
                            $rootPath . "app/" . $application['app_dir'] . "/controller/" . $subtable['controller_name'] . ".php",
                            $rootPath . "app/" . $application['app_dir'] . "/model/" . $subtable['controller_name'] . ".php",
                            $rootPath . "app/" . $application['app_dir'] . "/hook/" . $subtable['controller_name'] . ".php",
                            $rootPath . "app/" . $application['app_dir'] . "/validate/" . $subtable['controller_name'] . ".php"
                        ];

                        foreach ($subtableFiles as $file) {
                            if (file_exists($file)) {
                                $filesToDelete[] = $file;
                            }
                        }

                        // 删除子表相关数据
                        Db::connect($connect)->name('menu')->where('menu_id', $subtable['menu_id'])->delete();
                        Db::connect($connect)->name('field')->where('menu_id', $subtable['menu_id'])->delete();
                        Db::connect($connect)->name('action')->where('menu_id', $subtable['menu_id'])->delete();

                        $dropSql = "DROP TABLE IF EXISTS `" . $prefix . $subtable['table_name'] . "`";
                        Db::connect($connect)->execute($dropSql);
                    }
                }

                // 删除主表中的审批字段（与57类型相同）
                $mainTableName = $menu['table_name'];
                $approvalFields = [
                    "{$mainTableName}_apply_user_id_tfadmin",
                    "{$mainTableName}_apply_now_tfadmin",
                    "{$mainTableName}_apply_next_tfadmin",
                    "{$mainTableName}_apply_user_tfadmin",
                    "{$mainTableName}_apply_progress_tfadmin",
                    "{$mainTableName}_status_tfadmin",
                    "{$mainTableName}_remark_tfadmin"
                ];

                foreach ($approvalFields as $fieldName) {
                    $checkSql = "SHOW COLUMNS FROM `{$prefix}{$mainTableName}` LIKE '{$fieldName}'";
                    $result = Db::connect($connect)->query($checkSql);

                    if (!empty($result)) {
                        $dropColumnSql = "ALTER TABLE `{$prefix}{$mainTableName}` DROP COLUMN `{$fieldName}`";
                        Db::connect($connect)->execute($dropColumnSql);
                    }
                }
            }

            // 提交事务
            Db::commit();

            // 事务成功后删除文件
            foreach ($filesToDelete as $file) {
                @unlink($file);
            }

            // 事务成功后删除文件夹
            if ($flow['type'] == 57 && isset($subtable) && $subtable) {
                $viewFolder = $rootPath . "app/" . $application['app_dir'] . "/view/" . getViewName($subtable['controller_name']);
                $componentFolder = $rootPath . '/public/components/' . $application['app_dir'] . '/' . strtolower($subtable['controller_name']);

                $this->deleteFolder($viewFolder);
                $this->deleteFolder($componentFolder);
            }

            if ($flow['type'] == 60 && isset($subtables)) {
                foreach ($subtables as $subtable) {
                    if ($subtable) {
                        $viewFolder = $rootPath . "app/" . $application['app_dir'] . "/view/" . getViewName($subtable['controller_name']);
                        $componentFolder = $rootPath . '/public/components/' . $application['app_dir'] . '/' . strtolower($subtable['controller_name']);

                        $this->deleteFolder($viewFolder);
                        $this->deleteFolder($componentFolder);
                    }
                }
            }

        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            abort(501, $e->getMessage());
        }

        return json(['status' => 200]);
    }

    //根据流程表名获取字段列表
    public function getFlowTableFields() {
        $menu_id = $this->request->post('menu_id');
        if (!$menu_id) {
            $this->error('请选择关联的流程表');
        }

        $menuInfo = Menu::where('menu_id', $menu_id)->find();

        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
        $dbtype = config('database.connections.' . $connect . '.type');
        if ($dbtype == 'mongo') {
            $list = db("field")->field("field as Field,title as Comment")->where('menu_id', $menuInfo['menu_id'])->order('sortid asc')->select();
        } else {
            $list = Db::connect($menuInfo['connect'])->query('show full columns from ' . config('database.connections.' . $menuInfo['connect'] . '.prefix') . $menuInfo['table_name']);
        }

        // 获取类型为33的关联字段
        $field = Db::name('field')->where(['menu_id' => $menu_id, 'type' => 33])->select()->toArray();

        // 从field中提取所有field字段值
        $fieldNames = array_column($field, 'field');

        // 从list中筛选出在fieldNames中存在的字段
        $filteredList = [];
        foreach ($list as $item) {
            if (in_array($item['Field'], $fieldNames)) {
                $filteredList[] = $item;
            }
        }
        return json(['status' => 200, 'filedList' => $filteredList]);
    }

    //根据流程表名获取流程组
    public function getFlowTableGroup() {
        $menu_id = $this->request->post('menu_id');
        if (!$menu_id) {
            $this->error('请选择关联的流程表');
        }
        // 获取类型为2的关联字段
        $field_sql = Db::name('field')->where(['menu_id' => $menu_id, 'type' => 2])->order('id desc')->value('sql');
        $group = $this->query($field_sql, 'mysql');

        return json(['status' => 200, 'group' => $group]);
    }

    //拖动排序
    public function updateFieldSort() {
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
    public function updateActionSort() {
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
    public function configList() {
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
    public function getTables() {
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
    public function getTablesByMenuId() {
        $menu_id = $this->request->post('menu_id');
        if (!$menu_id) {
            $this->error('菜单ID不能为空');
        }
        $app_id = Menu::where('menu_id', $menu_id)->value('app_id');
        $tableList = Menu::where('app_id', $app_id)->where('table_name', '<>', '')->field('table_name,title')->select();
        return json(['status' => 200, 'data' => $tableList]);
    }

    //数据库table列表
    private function getTableList($connect) {
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
    public function getTableFields() {
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
    private function getMenu($app_id) {
        $field = 'menu_id,pid,title,controller_name,create_code,create_table,table_name,status,sortid,enable_es';
        $list = Menu::field($field)
            ->where(['app_id' => $app_id])
            ->where('menu_show', 1)
            ->order('sortid asc')
            ->select()
            ->toArray();
        return _generateListTree($list, 0, ['menu_id', 'pid']);
    }

    //获取上传配置列表
    public function getUploadList() {
        $appid = $this->request->post('app_id');
        $app_type = Application::where('app_id', $appid)->value('app_type');
        $list = Db::name('upload_config')->field('id,title')->select()->toArray();
        return json(['status' => 200, 'data' => $list, 'app_type' => $app_type]);
    }

    //生成
    public function create() {
        $menu_id = $this->request->post('menu_id');
        $type = $this->request->post('type');
        if ($this->createCode($menu_id, $type)) {
            return json(['status' => 200]);
        }
    }

    //生成
    private function createCode($menu_id, $type=2, $tf_flow_group = []) {
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
        $application = $this->changeApplication($application);

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
        // 有没有审批事件
        $action_flow = Action::where(['menu_id' => $menu_id, 'type' => 57])->find();
        $data['has_flow'] = !empty($action_flow);
        if (!empty($action_flow)) {
            $action_flow['other_config'] = json_decode($action_flow['other_config'], true);
            $data['flow_action'] = $action_flow;
            $data['flow_menu'] = Menu::where(['menu_id' => $action_flow['other_config']['flow_table']])
                ->order('menu_id desc')
                ->find();
            $data['flow_field'] = Field::where('menu_id', $data['flow_menu']['menu_id'])->select()->toArray();
            $data['group_field'] = Db::name('field')
                ->where(['menu_id' => $data['flow_menu']['menu_id'], 'type' => 2])
                ->whereNotNull('sql')
                ->order('id desc')
                ->value('field');


        }
        $data['tf_flow_group'] = $tf_flow_group;

        // 有没有审批流
        $approval_flow = Action::where(['menu_id' => $menu_id, 'type' => 60])->find();

        $data['sign'] = md5(md5(json_encode($data, JSON_UNESCAPED_UNICODE) . $secrect['secrect']));
        $data['domain'] = $_SERVER['HTTP_HOST'];
        $data['base_config'] = Db::name('base_config')->column('data', 'name');
        $data['menuInfoPid'] = Db::name('menu')->where('menu_id', $data['menuInfo']['pid'])->find();
        $res = $this->curlRequest($this->url . '/produce/CreateCode/buildCode', 'POST', $data);
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

        if ($res['status'] == 411) {
            throw new ValidateException($res['msg']);
        }

        if (!is_array($res['model'])) {
            halt($ret);
        }

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
    public function createByTable() {
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
                    $typeStr = strtolower($v['Type']);
                    $fieldInfo[$k]['datatype'] = preg_split("/\(.*\)+/", $typeStr)[0];
                    preg_match("/\((.*)\)/", $typeStr, $match);
                    $fieldInfo[$k]['length'] = $match[1] ?? '';
                    // 反推真实字段默认值，保证 cd_field 与真实表结构一致
                    $fieldInfo[$k]['default_value'] = array_key_exists('Default', $v) ? $v['Default'] : null;
                    if (($v['Key'] ?? '') === 'UNI') {
                        $fieldInfo[$k]['indexdata'] = 'unique';
                    } elseif (($v['Key'] ?? '') === 'MUL') {
                        $fieldInfo[$k]['indexdata'] = 'index';
                    } else {
                        $fieldInfo[$k]['indexdata'] = '';
                    }
                    // 反推真实字段空/非空约束，保持字段验证配置一致
                    $fieldInfo[$k]['validate'] = strtoupper((string)($v['Null'] ?? 'YES')) === 'NO' ? 'notempty' : '';
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
    private function getExtend($actionList) {
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

    private function getExtendFields($val) {
        $extend_fields = array_column($val['fields'], 'field');
        $menuInfo = Menu::field('menu_id,table_name')->where('controller_name', $val['relative_table'])->find();
        $fieldList = Field::where('menu_id', $menuInfo['menu_id'])->order('sortid asc')->select()->toArray();
        foreach ($fieldList as $k => $v) {
            $fieldList[$k]['belong_table'] = $val['relative_table'];
            $fieldList[$k]['table_name'] = $menuInfo['table_name'];
            if (!in_array($v['field'], $extend_fields)) {
                unset($fieldList[$k]);
            }
        }
        $fieldList = array_values($fieldList);
        return $fieldList;
    }

    //检测cms模型字段
    public function checkCmsField() {
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
    public function setControllerName($controller_name) {
        if (strpos($controller_name, '/') > 0) {
            $arr = explode('/', $controller_name);
            $controller_name = ucfirst($arr[0]) . '/' . ucfirst($arr[1]);
        } else {
            $controller_name = ucfirst($controller_name);
        }

        return str_replace('_', '', $controller_name);
    }

    //获取应用名 以及数据表名称
    public function getAppInfo() {
        $controller_name = $this->request->post('controller_name');
        $data['table_name'] = $this->getTableName($controller_name);
        $data['pk'] = $data['table_name'] ? $data['table_name'] . '_id' : '';
        $data['app_name'] = app('http')->getName();
        $data['status'] = 200;
        return json($data);
    }

    //获取应用名 以及数据表名称
    public function getAppType() {
        $appid = $this->request->post('app_id');
        $data['status'] = 200;
        $data['data'] = Application::where('app_id', $appid)->value('app_type');
        return json($data);
    }

    //获取应用名 以及数据表名称
    public function getDbType() {
        $dbname = $this->request->post('dbname');
        $dbtype = config('database.connections.' . $dbname . '.type');
        $data['status'] = 200;
        $data['data'] = $dbtype;
        return json($data);
    }

    private function getTableName($controller_name) {
        if ($controller_name && strpos($controller_name, '/') > 0) {
            $controller_name = explode('/', $controller_name)[1];
        }
        return $controller_name;
    }

    //获取秘钥信息
    private function getSecrect() {
        $info = Db::name('secrect')->select()->column('data', 'name');
        return $info;
    }

    public static function getFieldStatus($tablename, $field, $connect) {
        $list = Db::connect($connect)->query('show columns from ' . $tablename);
        foreach ($list as $v) {
            $arr[] = $v['Field'];
        }
        if (in_array($field, $arr)) {
            return true;
        }
    }

    //获取默认钩子方法路径
    public function getHookPath() {
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
    private function go_curl($url, $type, $data = false, &$err_msg = null, $timeout = 20, $cert_info = array()) {
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
    function getRouteName($controller_name) {
        if ($controller_name && strpos($controller_name, '/') > 0) {
            $controller_name = str_replace('/', '_', $controller_name);
        }
        return $controller_name;
    }

    // 更新备注内容杨爽
    public function updateActionRemark() {
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
    public function getActionRemark() {
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
    public function getRemarkVersions() {
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
    public function saveRemarkVersion() {
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
    public function updateFullRemark() {
        $data = $this->request->post();

        Db::name('action')
            ->where('id', $data['id'])
            ->update([
                'remark' => $data['codeContent'],
                'remark_desc' => $data['description']
            ]);

        return json(['status' => 200]);
    }

    public static function curlRequest($url, $method = 'GET', $data = [], $headers = []) {
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

    /**************************************************************************/
    /**
     * 创建审批流子表和相关配置
     */
    private function createFlowEvenTable($data, $create_action = "add") {
        // 开启事务
        Db::startTrans();
        try {
            $menu_other_config = json_decode($data['other_config'], true);
            $flow_table_id = $menu_other_config['flow_table'];
            $flow_table = db("menu")->where('menu_id', $flow_table_id)->find();
            $menuInfo = db("menu")->where('menu_id', $data['menu_id'])->find();
            $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
            $prefix = config('database.connections.' . $connect . '.prefix');
            $application = Application::where('app_id', $menuInfo['app_id'])->find()->toArray();
            $application = $this->changeApplication($application);

            if (config('database.connections.' . $connect . '.type') <> 'mysql') {
                Db::rollback();
                return false;
            }

            $approvalRecords_table = strtolower(trim($menuInfo['table_name'])) . "_subtable";
            $approvalRecords_pk = $approvalRecords_table . "_id";
            $table_name = $approvalRecords_table;
            $pk = strtolower(trim($approvalRecords_pk));

            // 1. 创建子表
            $this->createSubtable($connect, $prefix, $table_name, $pk, $flow_table, $menuInfo, $data, $menu_other_config, $application);

            // 2. 插入菜单记录
            $controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $table_name)));
            $newMenuId = $this->insertMenuRecord($connect, $data, $menuInfo, $controllerName, $pk, $table_name, $menu_other_config);

            // 3. 插入字段数据
            $this->insertFieldData($connect, $newMenuId, $prefix, $pk, $data, $menuInfo, $flow_table, $menu_other_config, $application);

            // 4. 插入方法数据
            $this->insertActionData($connect, $newMenuId, $data, $menu_other_config);

            // 5. 审批记录,审核数据
            $this->insertApprovalActions($connect, $data, $menuInfo, $controllerName, $pk, $application);
            // 6. 代码生成
            if (!$this->createCode($newMenuId, 2)) {
                // 回滚事务
                Db::rollback();
                throw new ValidateException("审批流创建失败");
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new \Exception('审批流创建失败：' . $e->getMessage());
        }
    }

    /**
     * 5. 插入审批流特殊动作
     */
    private function insertApprovalActions($connect, $data, $menuInfo, $controllerName, $pk, $application = []) {
        $mainTableName = $menuInfo['table_name'];
        $menuInfoPk = $menuInfo['pk'];
        // 删除已存在的相关动作
        Action::where('action_pid', $data['id'])->delete();

        // 审批记录动作
        $approvalRecordAction = [
            'menu_id' => $data['menu_id'],  // 主表菜单ID
            'name' => '审批记录',
            'action_name' => 'dialogUrlRecord',
            'type' => 59,
            'icon' => 'fas fa-clone',
            'pagesize' => '20',
            'group_button_status' => 1,
            'list_button_status' => 0,
            'button_color' => 'warning',
            'fields' => $menuInfo['pk'],
            'sortid' => $data['id'],
            'orderby' => '',
            'tree_config' => '',
            'jump' => "/admin/{$controllerName}/index",  // 注意：这里需要动态生成子表路径
            'server_create_status' => 1,
            'vue_create_status' => 1,
            'cache_time' => null,
            'api_auth' => null,
            'img_auth' => null,
            'sms_auth' => null,
            'list_filter' => '',
            'tab_config' => '',
            'sql' => '',
            'dialog_size' => '85%',
            'status_val' => '',
            'validate' => null,
            'select_type' => 1,
            'table_height' => '',
            'left_tree_sql' => null,
            'with_join' => '',
            'other_config' => json_encode([
                'export_type' => '',
                'hook' => [],
                'excel' => '',
                'left_tree_show' => '',
                'tree_show' => 1,
                'after_hook' => '',
                'befor_hook' => '',
                'printer_status' => 2,
                'list_button_style' => 1,
                'guige' => [],
                'detail_search_field' => []
            ]),
            'dialog_type' => '2',
            'version' => null,
            'remark' => null,
            'remark_desc' => null,
            'q_template' => '',
            'h_php' => '',
            'action_pid' => $data['id'],
            'action_show' => 0,
        ];

        // 审核数据动作
        $auditDataAction = [
            'menu_id' => $data['menu_id'],  // 主表菜单ID
            'name' => '审核数据',
            'action_name' => 'batupdateRecord',
            'type' => 58,
            'icon' => 'fas fa-chess',
            'pagesize' => '20',
            'group_button_status' => 0,
            'list_button_status' => 1,
            'button_color' => 'primary',
            'fields' => "{$mainTableName}_status_tfadmin,{$mainTableName}_remark_tfadmin",
            'sortid' => $data['id'] + 1,
            'orderby' => '',
            'tree_config' => '',
            'jump' => '',
            'server_create_status' => 1,
            'vue_create_status' => 1,
            'cache_time' => null,
            'api_auth' => null,
            'img_auth' => null,
            'sms_auth' => null,
            'list_filter' => '',
            'tab_config' => '',
            'sql' => '',
            'dialog_size' => '85%',
            'status_val' => '',
            'validate' => null,
            'select_type' => 1,
            'table_height' => '',
            'left_tree_sql' => null,
            'with_join' => '',
            'other_config' => json_encode([
                'export_type' => '',
                'hook' => [],
                'excel' => '',
                'left_tree_show' => '',
                'tree_show' => 1,
                'after_hook' => "app/{$application['app_dir']}/hook/{$menuInfo['controller_name']}@afterBatupdateRecord",
                'befor_hook' => "app/{$application['app_dir']}/hook/{$menuInfo['controller_name']}@beforBatupdateRecord",
                'printer_status' => 2,
                'list_button_style' => 1,
                'guige' => [],
                'detail_search_field' => [],
                'show_list_button' => "{$menuInfoPk} && (scope.row.{$menuInfo['table_name']}_status_tfadmin == 1 || scope.row.{$menuInfo['table_name']}_status_tfadmin == 2) && scope.row.{$menuInfo['table_name']}_apply_next_tfadmin && Object.keys(scope.row.{$menuInfo['table_name']}_apply_next_tfadmin)[0] == '{:session(\"{$application['app_dir']}.{$application['pk']}\")}'",
            ]),
            'dialog_type' => null,
            'version' => null,
            'remark' => null,
            'remark_desc' => null,
            'q_template' => '',
            'h_php' => '',
            'action_pid' => $data['id'],
            'action_show' => 0,
        ];

        $updates = Action::where(['menu_id' => $data['menu_id'], 'type' => 3])->select()->toArray();
        foreach ($updates as $update_item) {
            $other_config = empty($update_item) ? [] : json_decode($update_item['other_config'], true);
            $other_config['show_list_button'] = "{$menuInfo['table_name']}_status_tfadmin != 1";
            Action::where('id', $update_item['id'])->update(['other_config' => json_encode($other_config)]);
        }
        // 插入动作
        Action::create($auditDataAction);
        Action::create($approvalRecordAction);
    }

    /**
     * 1. 创建子表
     */
    private function createSubtable($connect, $prefix, $table_name, $pk, $flow_table, $menuInfo, $data, $menu_other_config, $application) {
        // 检查并删除已存在的表
        $dropSql = "DROP TABLE IF EXISTS `" . $prefix . $table_name . "`";
        Db::connect($connect)->execute($dropSql);

        // 创建新表
        $createSql = "CREATE TABLE `" . $prefix . $table_name . "` ( ";
        $createSql .= "
    `{$pk}` int NOT NULL AUTO_INCREMENT,
    `{$menu_other_config['flow_filed']}` int DEFAULT NULL COMMENT '" . addslashes($flow_table['title']) . "',
    `user_id` int DEFAULT NULL COMMENT '审核人员',
    `{$menuInfo['pk']}` int DEFAULT NULL COMMENT '" . addslashes($data['title']) . "',
    `{$menuInfo['table_name']}_status_tfadmin` smallint DEFAULT 2 COMMENT '审核状态 , 通过-1 ; 驳回-0 ; 待审核-2 ;',
    `{$menuInfo['table_name']}_remark_tfadmin` text COMMENT '审核备注',
    `create_at` int DEFAULT NULL COMMENT '审核日期',
    PRIMARY KEY (`{$pk}`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='" . addslashes($data['title']) . "审核记录子表';";

        Db::connect($connect)->execute($createSql);

        // ============ 向主表添加审批字段 ============
        $mainTableName = $menuInfo['table_name'];

        // 要添加的审批字段
        $approvalFields = [
            [
                'name' => "{$mainTableName}_apply_user_id_tfadmin",
                'type' => "text",
                'comment' => '审批人员',
                'default' => "DEFAULT NULL"
            ],
            [
                'name' => "{$mainTableName}_apply_now_tfadmin",
                'type' => "int",
                'comment' => '上次审核',
                'default' => "DEFAULT NULL"
            ],
            [
                'name' => "{$mainTableName}_apply_next_tfadmin",
                'type' => "int",
                'comment' => '当前审核',
                'default' => "DEFAULT NULL"
            ],
            [
                'name' => "{$mainTableName}_apply_user_tfadmin",
                'type' => "int",
                'comment' => '当前录入',
                'default' => "DEFAULT NULL"
            ],
            [
                'name' => "{$mainTableName}_apply_progress_tfadmin",
                'type' => "smallint",
                'comment' => '审核进度',
                'default' => "DEFAULT 0"
            ],
            [
                'name' => "{$mainTableName}_status_tfadmin",
                'type' => "smallint",
                'comment' => '审核状态 0->驳回 1->通过 2->待审核',
                'default' => "DEFAULT 2"
            ],
            [
                'name' => "{$mainTableName}_remark_tfadmin",
                'type' => "text",
                'comment' => '审核备注',
                'default' => "DEFAULT NULL"
            ],
        ];

        // 检查并添加字段
        foreach ($approvalFields as $field) {
            $fieldName = $field['name'];

            // 检查字段是否已存在
            $checkSql = "SHOW COLUMNS FROM `{$prefix}{$mainTableName}` LIKE '{$fieldName}'";
            $result = Db::connect($connect)->query($checkSql);

            // 如果字段不存在，则添加
            if (empty($result)) {
                $addColumnSql = "ALTER TABLE `{$prefix}{$mainTableName}`
                        ADD COLUMN `{$fieldName}` {$field['type']} {$field['default']} COMMENT '{$field['comment']}'";
                Db::connect($connect)->execute($addColumnSql);
            }
        }


        $fieldList = $this->addFlowField([], $menuInfo, $menuInfo['menu_id'], $application, $prefix);
        Db::name('field')->insertAll($fieldList);
    }

    /**
     * 2. 插入菜单记录
     */
    private function insertMenuRecord($connect, $data, $menuInfo, $controllerName, $pk, $table_name, $menu_other_config) {
        // 检查是否已存在相同 controller_name 的记录
        $existingMenu = Db::connect($connect)->name('menu')->where('controller_name', $controllerName)->find();
        if ($existingMenu) {
            // 如果已存在，先删除旧记录和相关字段
            Db::connect($connect)->name('menu')->where('menu_id', $existingMenu['menu_id'])->delete();
            Db::connect($connect)->name('field')->where('menu_id', $existingMenu['menu_id'])->delete();
            Db::connect($connect)->name('action')->where('menu_id', $existingMenu['menu_id'])->delete();
        }

        // 插入菜单记录
        $menuData = [
            'pid' => $data['menu_id'],
            'controller_name' => $controllerName,
            'title' => '审核记录',
            'pk' => $pk,
            'table_name' => $table_name,
            'create_code' => 1,
            'status' => 0,
            'sortid' => $data['menu_id'],
            'create_table' => 1,
            'url' => '',
            'icon' => null,
            'tab_config' => null,
            'app_id' => $menuInfo['app_id'],
            'is_post' => 0,
            'upload_config_id' => 0,
            'connect' => $connect,
            'page_type' => 1,
            'home_show' => 0,
            'menu_pic' => '',
            'notice' => '',
            'prompt' => 0,
            'prompt_session' => '',
            'flow_subtable' => 1,
            'menu_show' => 0,
        ];

        // 使用参数化查询插入菜单记录
        $newMenuId = Db::connect($connect)->name('menu')->insertGetId($menuData);

        if (!$newMenuId) {
            throw new \Exception('插入菜单记录失败');
        }

        return $newMenuId;
    }

    /**
     * 3. 插入字段数据
     */
    private function insertFieldData($connect, $newMenuId, $prefix, $pk, $data, $menuInfo, $flow_table, $menu_other_config, $application) {
        // 定义固定的字段配置模板
        $fixedFieldConfigs = $this->getFixedFieldConfigs($prefix, $pk, $menuInfo, $application);

        // 定义动态字段配置
        $dynamicFieldConfigs = $this->getDynamicFieldConfigs($prefix, $data, $menuInfo, $flow_table, $menu_other_config);

        // 合并所有字段配置
        $allFieldConfigs = array_merge($fixedFieldConfigs, $dynamicFieldConfigs);

        // 批量插入字段数据
        $fieldInsertData = [];
        foreach ($allFieldConfigs as $field) {
            // 确保 title 不为空
            $title = trim($field['title']);
            if (empty($title)) {
                $title = '未命名字段';
            }

            $fieldInsertData[] = [
                'menu_id' => $newMenuId,
                'title' => $title,
                'field' => $field['field'],
                'type' => $field['type'],
                'list_show' => $field['list_show'] ?? null,
                'search_type' => $field['search_type'] ?? null,
                'post_status' => $field['post_status'] ?? null,
                'create_table_field' => $field['create_table_field'] ?? null,
                'validate' => null,
                'rule' => null,
                'sortid' => $field['sortid'],
                'sql' => $field['sql'] ?? '',
                'default_value' => $field['default_value'] ?? '',
                'datatype' => $field['datatype'] ?? '',
                'length' => $field['length'] ?? '',
                'indexdata' => $field['indexdata'] ?? null,
                'show_condition' => $field['show_condition'] ?? null,
                'item_config' => $field['item_config'] ?? '',
                'width' => $field['width'] ?? '',
                'datetime_config' => $field['datetime_config'] ?? '',
                'other_config' => $field['other_config'] ?? '',
                'belong_table' => $field['belong_table'] ?? '',
                'icon' => $field['icon'] ?? null,
                'key_placeholder' => $field['key_placeholder'] ?? '',
                'value_placeholder' => $field['value_placeholder'] ?? '值占位文本',
                'tx_tiaojian' => 0,
                'tx_zhi' => '',
                'tx_color' => '',
                'improve_tiaojian' => 0,
                'improve_zhi' => '',
                'improve_color' => null,
                'list_background_config' => '[]',
                'tx_config' => '[]'
            ];
        }

        // 使用批量插入
        $result = Db::connect($connect)->name('field')->insertAll($fieldInsertData);

        if (!$result) {
            throw new \Exception('插入字段记录失败');
        }
    }

    /**
     * 获取固定字段配置
     */
    private function getFixedFieldConfigs($prefix, $pk, $menuInfo, $application) {
        $username = explode('|', $application['login_fields'])[0];
        return [
            // 编号字段
            [
                'title' => '编号',
                'field' => $pk,
                'type' => 1,
                'list_show' => 2,
                'search_type' => null,
                'post_status' => null,
                'create_table_field' => 1,
                'sortid' => 1,
                'sql' => null,
                'default_value' => null,
                'datatype' => 'int',
                'length' => '11',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => null,
                'width' => '70',
                'datetime_config' => null,
                'other_config' => null,
                'belong_table' => null,
                'icon' => null,
                'key_placeholder' => null,
                'value_placeholder' => '值占位文本'
            ],
            // 审核人员字段
            [
                'title' => '审核人员',
                'field' => 'user_id',
                'type' => 2,
                'list_show' => 2,
                'search_type' => 1,
                'post_status' => 1,
                'create_table_field' => 1,
                'sortid' => 4,
                'sql' => "select {$application['pk']},{$username} from {$prefix}{$application['login_table']}",
                'default_value' => '',
                'datatype' => 'int',
                'length' => '11',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => ['tooltip', 'fanzhuan'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => ''
            ],
            // 审核状态字段
            [
                'title' => '审核状态',
                'field' => "{$menuInfo['table_name']}_status_tfadmin",
                'type' => 4,
                'list_show' => 2,
                'search_type' => 1,
                'post_status' => 1,
                'create_table_field' => 1,
                'sortid' => 5,
                'sql' => '',
                'default_value' => '',
                'datatype' => 'smallint',
                'length' => '6',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => json_encode([
                    ['key' => '通过', 'val' => '1', 'label_color' => 'primary'],
                    ['key' => '驳回', 'val' => '0', 'label_color' => 'danger']
                ]),
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => ['tooltip'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => ''
            ],
            // 审核备注字段
            [
                'title' => '审核备注',
                'field' => "{$menuInfo['table_name']}_remark_tfadmin",
                'type' => 8,
                'list_show' => 2,
                'search_type' => 0,
                'post_status' => 1,
                'create_table_field' => 1,
                'sortid' => 6,
                'sql' => '',
                'default_value' => '',
                'datatype' => 'text',
                'length' => '0',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => ['tooltip'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => ''
            ],
            // 审核日期字段
            [
                'title' => '审核日期',
                'field' => 'create_at',
                'type' => 11,
                'list_show' => 2,
                'search_type' => 0,
                'post_status' => 1,
                'create_table_field' => 1,
                'sortid' => 7,
                'sql' => '',
                'default_value' => '',
                'datatype' => 'int',
                'length' => '11',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => 'datetime',
                'other_config' => json_encode([
                    'shuxing' => ['tooltip'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => ''
            ]
        ];
    }

    /**
     * 获取动态字段配置
     */
    private function getDynamicFieldConfigs($prefix, $data, $menuInfo, $flow_table, $menu_other_config) {
        return [
            // 主表关联字段
            [
                'title' => $menuInfo['title'] ?: '主表信息',
                'field' => $menuInfo['pk'],
                'type' => 2,
                'list_show' => 2,
                'search_type' => 1,
                'post_status' => 1,
                'create_table_field' => 1,
                'sortid' => 2,
                'sql' => "select {$menuInfo['pk']},{$menuInfo['pk']} from {$prefix}{$menuInfo['table_name']}",
                'default_value' => '',
                'datatype' => 'int',
                'length' => '11',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => ['tooltip'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => ''
            ],
            // 流程表关联字段
            [
                'title' => $flow_table['title'] ?: '流程表',
                'field' => $flow_table['pk'],
                'type' => 2,
                'list_show' => 0,
                'search_type' => 0,
                'post_status' => 1,
                'create_table_field' => 1,
                'sortid' => 3,
                'sql' => "",
                'default_value' => '',
                'datatype' => 'int',
                'length' => '11',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => ['tooltip'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => ''
            ]
        ];
    }

    /**
     * 4. 插入动作数据
     */
    private function insertActionData($connect, $newMenuId, $data, $menu_other_config) {
        // 固定动作配置模板
        $actionConfigs = [
            // 数据列表
            [
                'menu_id' => $newMenuId,
                'name' => '数据列表',
                'action_name' => 'index',
                'type' => 1,
                'icon' => null,
                'pagesize' => '20',
                'group_button_status' => 0,
                'list_button_status' => null,
                'button_color' => null,
                'fields' => null,
                'sortid' => 1,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => null,
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null
            ],
            // 查看详情
            [
                'menu_id' => $newMenuId,
                'name' => '查看详情',
                'action_name' => 'detail',
                'type' => 5,
                'icon' => 'el-icon-view',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => null,
                'button_color' => 'info',
                'fields' => null,
                'sortid' => 6,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => '85%',
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null
            ]
        ];

        // 批量插入动作数据
        $result = Db::connect($connect)->name('action')->insertAll($actionConfigs);

        if (!$result) {
            throw new \Exception('插入动作记录失败');
        }
    }

    /**
     * @param $dir
     * @return bool
     * @desc      删除文件夹及文件夹内文件
     * @author    JiaWei
     * @email     975162853@qq.com
     * @date      2025/12/11
     * @time      14:18
     */
    function deleteFolder($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        // 获取目录下所有文件和子目录
        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                // 递归删除子目录
                $this->deleteFolder($path);
            } else {
                // 删除文件
                unlink($path);
            }
        }

        // 删除空目录
        return rmdir($dir);
    }

    /**
     * @param array $fieldList
     * @param       $menuInfo
     * @param       $menu_id
     * @param       $application
     * @param       $prefix
     * @return array
     * @desc      隐藏字段处理
     * @author    JiaWei
     * @email     975162853@qq.com
     * @date      2025/12/12
     * @time      09:58
     */
    private function addFlowField(array $fieldList, $menuInfo, $menu_id, $application, $prefix) {
        $username = explode('|', $application['login_fields'])[0];
        // 审核状态字段
        $fieldList[] = [
            "menu_id" => $menu_id,
            "title" => "审批状态",
            "field" => "{$menuInfo['table_name']}_status_tfadmin", // 根据表名动态生成
            "type" => 4,
            "list_show" => 2,
            "search_type" => 1,
            "post_status" => 0, // 不能直接修改
            "flow_status" => 1, // 不能直接修改
            "create_table_field" => 1,
            "validate" => "notempty",
            "rule" => null,
            "sortid" => 9993,
            "sql" => "",
            "default_value" => "2", // 默认值设为2
            "datatype" => "smallint",
            "length" => "6",
            "indexdata" => null,
            "show_condition" => null,
            "item_config" => json_encode([
                ["key" => "驳回", "val" => "0", "label_color" => "danger"],
                ["key" => "通过", "val" => "1", "label_color" => "primary"],
                // ["key" => "待审核", "val" => "2", "label_color" => "warning"]
            ]),
            "width" => null,
            "datetime_config" => null,
            "other_config" => json_encode([
                "address_type" => "1",
                "now_time" => false,
                "placeholder" => "",
                "rand_config" => "",
                "filetype" => "",
                "liandong_field" => "",
                "shuxing" => ["tooltip"],
                "jdt" => "changtiao",
                "remote_research_field" => "",
                "rename_status" => "",
                "default_tabs_value" => "",
                "application_id" => "",
                "crop" => "",
                "time_search_tempate" => true,
                "guige" => [[]],
                "maxrows" => 4,
                "inputRemark" => "系统自动管理的审批状态",
                "list_feild" => "",
                "rangetime_type" => "date",
                "previewImage" => 0,
                "search_all" => 0
            ]),
            "belong_table" => "",
            "icon" => null,
            "key_placeholder" => "",
            "value_placeholder" => "",
            "tx_tiaojian" => 0,
            "tx_zhi" => "",
            "tx_color" => "",
            "improve_tiaojian" => 0,
            "improve_zhi" => "",
            "improve_color" => null,
            "list_background_config" => "[]",
            "tx_config" => "[]",
            "field_show" => 0,
        ];

        // 审核备注字段
        $fieldList[] = [
            "menu_id" => $menu_id,
            "title" => "审批备注",
            "field" => "{$menuInfo['table_name']}_remark_tfadmin", // 根据表名动态生成
            "type" => 8,
            "list_show" => 2,
            "search_type" => 2,
            "post_status" => 0, // 不能直接修改
            "flow_status" => 1, // 不能直接修改
            "create_table_field" => 1,
            "validate" => "",
            "rule" => null,
            "sortid" => 9994,
            "sql" => "",
            "default_value" => "",
            "datatype" => "tinytext",
            "length" => "0",
            "indexdata" => null,
            "show_condition" => null,
            "item_config" => "",
            "width" => null,
            "datetime_config" => null,
            "other_config" => json_encode([
                "address_type" => "1",
                "now_time" => false,
                "placeholder" => "系统自动记录的审批备注",
                "rand_config" => "",
                "filetype" => "",
                "liandong_field" => "",
                "shuxing" => ["tooltip"],
                "jdt" => "changtiao",
                "remote_research_field" => "",
                "rename_status" => "",
                "default_tabs_value" => "",
                "application_id" => "",
                "crop" => "",
                "time_search_tempate" => true,
                "guige" => [[]],
                "maxrows" => 4,
                "inputRemark" => "系统自动记录的审批备注",
                "list_feild" => "",
                "rangetime_type" => "date",
                "previewImage" => 0,
                "search_all" => 0
            ]),
            "belong_table" => "",
            "icon" => null,
            "key_placeholder" => "",
            "value_placeholder" => "",
            "tx_tiaojian" => 0,
            "tx_zhi" => "",
            "tx_color" => "",
            "improve_tiaojian" => 0,
            "improve_zhi" => "",
            "improve_color" => null,
            "list_background_config" => "[]",
            "tx_config" => "[]",
            "field_show" => 0,
        ];
        // 当前审核人字段
        $fieldList[] = [
            "menu_id" => $menu_id,
            "title" => "上次审核",
            "field" => "{$menuInfo['table_name']}_apply_now_tfadmin", // 根据表名动态生成
            "type" => 2,
            "list_show" => 2,
            "search_type" => 1,
            "post_status" => 0, // 不能直接修改
            "create_table_field" => 1,
            "validate" => "",
            "rule" => null,
            "sortid" => 9991,
            "sql" => "select {$application['pk']},{$username} from {$prefix}{$application['login_table']}",
            "default_value" => "",
            "datatype" => "int",
            "length" => "11",
            "indexdata" => null,
            "show_condition" => null,
            "item_config" => "",
            "width" => null,
            "datetime_config" => null,
            "other_config" => json_encode([
                "address_type" => "1",
                "now_time" => false,
                "placeholder" => "",
                "rand_config" => "",
                "filetype" => "",
                "liandong_field" => "",
                "shuxing" => ["tooltip", "fanzhuan"],
                "jdt" => "changtiao",
                "remote_research_field" => "",
                "rename_status" => "",
                "default_tabs_value" => "",
                "application_id" => "",
                "crop" => "",
                "time_search_tempate" => true,
                "guige" => [[]],
                "maxrows" => 4,
                "inputRemark" => "",
                "list_feild" => "",
                "rangetime_type" => "date",
                "previewImage" => 0,
                "search_all" => 0
            ]),
            "belong_table" => "",
            "icon" => null,
            "key_placeholder" => "",
            "value_placeholder" => "",
            "tx_tiaojian" => 0,
            "tx_zhi" => "",
            "tx_color" => "",
            "improve_tiaojian" => 0,
            "improve_zhi" => "",
            "improve_color" => null,
            "list_background_config" => "[]",
            "tx_config" => "[]",
            "field_show" => 0,
        ];

        // 下一个审核人字段
        $fieldList[] = [
            "menu_id" => $menu_id,
            "title" => "当前审核",
            "field" => "{$menuInfo['table_name']}_apply_next_tfadmin", // 根据表名动态生成
            "type" => 2,
            "list_show" => 2,
            "search_type" => 1,
            "post_status" => 0, // 不能直接修改
            "create_table_field" => 1,
            "validate" => "",
            "rule" => null,
            "sortid" => 9992,
            "sql" => "select {$application['pk']},{$username} from {$prefix}{$application['login_table']}",
            "default_value" => "",
            "datatype" => "int",
            "length" => "11",
            "indexdata" => null,
            "show_condition" => null,
            "item_config" => "",
            "width" => null,
            "datetime_config" => null,
            "other_config" => json_encode([
                "address_type" => "1",
                "now_time" => false,
                "placeholder" => "",
                "rand_config" => "",
                "filetype" => "",
                "liandong_field" => "",
                "shuxing" => ["tooltip", "fanzhuan"],
                "jdt" => "changtiao",
                "remote_research_field" => "",
                "rename_status" => "",
                "default_tabs_value" => "",
                "application_id" => "",
                "crop" => "",
                "time_search_tempate" => true,
                "guige" => [[]],
                "maxrows" => 4,
                "inputRemark" => "",
                "list_feild" => "",
                "rangetime_type" => "date",
                "previewImage" => 0,
                "search_all" => 0
            ]),
            "belong_table" => "",
            "icon" => null,
            "key_placeholder" => "",
            "value_placeholder" => "",
            "tx_tiaojian" => 0,
            "tx_zhi" => "",
            "tx_color" => "",
            "improve_tiaojian" => 0,
            "improve_zhi" => "",
            "improve_color" => null,
            "list_background_config" => "[]",
            "tx_config" => json_encode([[
                "tx_tiaojian" => 3,
                "tx_zhi" => "{:session('{$application['app_dir']}.{$application['pk']}')}",
                "tx_color" => "#f56c6c",
            ]], 256),
            "field_show" => 0,
        ];
        return $fieldList;
    }

    /**
     * 创建审批流数据表（审批组和审批流）
     */
    private function createFlowDataTable($data, $create_action = "add") {

        // 开启事务
        Db::startTrans();
        try {
            $menu_other_config = json_decode($data['other_config'], true);
            $flow_group_field = $menu_other_config['flow_group'];
            $menuInfo = db("menu")->where('menu_id', $data['menu_id'])->find();
            $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');
            $prefix = config('database.connections.' . $connect . '.prefix');
            $application = Application::where('app_id', $menuInfo['app_id'])->find()->toArray();
            $application = $this->changeApplication($application);

            if (config('database.connections.' . $connect . '.type') <> 'mysql') {
                Db::rollback();
                return false;
            }

            // 1. 创建审批组表和审批流表
            $create_res = $this->createApprovalTables($connect, $prefix, $menuInfo, $data, $flow_group_field);

            // 2. 插入审批组菜单记录
            $groupMenuId = $this->insertGroupMenuRecord($connect, $data, $menuInfo);

            // 3. 插入审批流菜单记录
            $flowMenuId = $this->insertFlowMenuRecord($connect, $data, $menuInfo);

            // 4. 插入审批组字段数据
            $this->insertGroupFieldData($connect, $groupMenuId, $prefix, $menuInfo);

            // 5. 插入审批流字段数据
            $this->insertFlowFieldData($connect, $flowMenuId, $prefix, $menuInfo, $groupMenuId, $flow_group_field, $application);

            // 6. 插入审批组动作数据
            $this->insertGroupActionData($connect, $groupMenuId);

            // 7. 插入审批流动数据
            $this->insertFlowActionData($connect, $flowMenuId, $menuInfo);

            // 8. 在主表中添加审批流相关动作
            $this->insertMainTableFlowActions($connect, $data, $menuInfo, $groupMenuId, $application);

            // 9. 代码生成
            if (
                !$this->createCode($groupMenuId, 2)
                || !$this->createCode($flowMenuId, 2, [
                    'flow_group_field' => $flow_group_field,
                    'group_table_id' => $create_res['group_table'] . "_id"
                ])
            ) {
                // 回滚事务
                Db::rollback();
                throw new ValidateException("审批流数据表创建失败");
            }

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new \Exception('审批流数据表创建失败：' . $e->getMessage());
        }
    }

    /**
     * 8. 在主表中添加审批流相关动作
     */
    private function insertMainTableFlowActions($connect, $data, $menuInfo, $groupMenuId, $application) {
        $tableName = strtolower(trim($menuInfo['table_name']));
        $app_dir = $application['app_dir'];

        // 获取生成的审批组和审批流表名
        $groupTableName = $tableName . $this->groupTableSuffix;
        $flowTableName = $tableName . $this->flowTableSuffix;

        // 生成控制器名称
        $groupControllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $groupTableName)));
        $flowControllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $flowTableName)));

        // 动作配置
        $flowActions = [
            // 添加流动动作
            [
                'menu_id' => $data['menu_id'], // 主表菜单ID
                'name' => '添加审批流',
                'action_name' => 'dialogUrlTfAddFlow',
                'type' => 16,
                'icon' => 'fas fa-pallet',
                'pagesize' => '20',
                'group_button_status' => 1,
                'list_button_status' => null,
                'button_color' => 'warning',
                'fields' => '',
                'sortid' => $data['id'] + 100, // 使用data的id作为基础，加上偏移量
                'orderby' => '',
                'tree_config' => '',
                'jump' => "/{$app_dir}/{$groupControllerName}/index",
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => '',
                'tab_config' => '',
                'sql' => '',
                'dialog_size' => '85%',
                'status_val' => '',
                'validate' => null,
                'select_type' => 1,
                'table_height' => '',
                'left_tree_sql' => null,
                'with_join' => '',
                'other_config' => json_encode([
                    'export_type' => '',
                    'hook' => [],
                    'excel' => '',
                    'left_tree_show' => '',
                    'tree_show' => 1,
                    'after_hook' => '',
                    'befor_hook' => '',
                    'printer_status' => 2,
                    'list_button_style' => 1,
                    'guige' => [],
                    'detail_search_field' => []
                ]),
                'dialog_type' => '1',
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => '<div class="super-page">
  <h1>自定义页面</h1>
</div>',
                'h_php' => 'public function ygluntan() {
   if (!$this->request->isPost()){
     return view(\'ygluntan\');
   }
}',
                'action_pid' => $data['id'], // 使用data的id作为action_pid
                'action_show' => 0
            ],
            // 设置流动动作
            [
                'menu_id' => $data['menu_id'], // 主表菜单ID
                'name' => '设置审批流',
                'action_name' => 'dialogUrlTfSeetingFlow',
                'type' => 16,
                'icon' => 'fas fa-network-wired',
                'pagesize' => '20',
                'group_button_status' => 0,
                'list_button_status' => 1,
                'button_color' => 'amethyst',
                'fields' => $menuInfo['pk'], // 使用主表主键字段
                'sortid' => $data['id'] + 101, // 使用data的id作为基础，加上偏移量
                'orderby' => '',
                'tree_config' => '',
                'jump' => "/{$app_dir}/{$flowControllerName}/index",
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => '',
                'tab_config' => '',
                'sql' => '',
                'dialog_size' => '85%',
                'status_val' => '',
                'validate' => null,
                'select_type' => 1,
                'table_height' => '',
                'left_tree_sql' => null,
                'with_join' => '',
                'other_config' => json_encode([
                    'export_type' => '',
                    'hook' => [],
                    'excel' => '',
                    'left_tree_show' => '',
                    'tree_show' => 1,
                    'after_hook' => '',
                    'befor_hook' => '',
                    'printer_status' => 2,
                    'list_button_style' => 1,
                    'guige' => [],
                    'detail_search_field' => []
                ]),
                'dialog_type' => '1',
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => '<div class="super-page">
  <h1>自定义页面</h1>
</div>',
                'h_php' => 'public function ygluntan() {
   if (!$this->request->isPost()){
     return view(\'ygluntan\');
   }
}',
                'action_pid' => $data['id'], // 使用data的id作为action_pid
                'action_show' => 0
            ]
        ];

        // 插入动作
        $result = Db::connect($connect)->name('action')->insertAll($flowActions);

        if (!$result) {
            throw new \Exception('插入主表审批流动记录失败');
        }
    }

    /**
     * 1. 创建审批组表和审批流表
     */
    private function createApprovalTables($connect, $prefix, $menuInfo, $data, $flow_group_field) {
        $tableName = strtolower(trim($menuInfo['table_name']));
        $pk = $flow_group_field;

        // 1.1 创建审批组表
        $groupTableName = $tableName . $this->groupTableSuffix;
        $groupPk = $groupTableName . "_id";

        $dropGroupSql = "DROP TABLE IF EXISTS `" . $prefix . $groupTableName . "`";
        Db::connect($connect)->execute($dropGroupSql);

        $createGroupSql = "CREATE TABLE `" . $prefix . $groupTableName . "` (
        `{$groupPk}` int NOT NULL AUTO_INCREMENT COMMENT '编号',
        `{$groupTableName}_name` varchar(250) DEFAULT NULL COMMENT '审批流名称',
        PRIMARY KEY (`{$groupPk}`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='" . addslashes($menuInfo['title']) . "-审批组';";

        Db::connect($connect)->execute($createGroupSql);

        // 1.2 创建审批流表
        $flowTableName = $tableName . $this->flowTableSuffix;
        $flowPk = $flowTableName . "_id";

        $dropFlowSql = "DROP TABLE IF EXISTS `" . $prefix . $flowTableName . "`";
        Db::connect($connect)->execute($dropFlowSql);

        $createFlowSql = "CREATE TABLE `" . $prefix . $flowTableName . "` (
        `{$flowPk}` int NOT NULL AUTO_INCREMENT COMMENT '编号',
        `{$pk}` varchar(250) DEFAULT NULL COMMENT '关联字段-" . addslashes($menuInfo['title']) . "',
        `{$groupTableName}_id` int DEFAULT NULL COMMENT '审批流名称',
        `{$flowTableName}` longtext COMMENT '审批流设置',
        PRIMARY KEY (`{$flowPk}`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='" . addslashes($menuInfo['title']) . "-审批流';";

        Db::connect($connect)->execute($createFlowSql);

        return [
            'group_table' => $groupTableName,
            'group_pk' => $groupPk,
            'flow_table' => $flowTableName,
            'flow_pk' => $flowPk
        ];
    }

    /**
     * 2. 插入审批组菜单记录
     */
    private function insertGroupMenuRecord($connect, $data, $menuInfo) {
        $tableName = strtolower(trim($menuInfo['table_name']));
        $groupTableName = $tableName . $this->groupTableSuffix;
        $groupPk = $groupTableName . "_id";
        $controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $groupTableName)));

        // 检查是否已存在相同 controller_name 的记录
        $existingMenu = Db::connect($connect)->name('menu')->where('controller_name', $controllerName)->find();
        if ($existingMenu) {
            Db::connect($connect)->name('menu')->where('menu_id', $existingMenu['menu_id'])->delete();
            Db::connect($connect)->name('field')->where('menu_id', $existingMenu['menu_id'])->delete();
            Db::connect($connect)->name('action')->where('menu_id', $existingMenu['menu_id'])->delete();
        }

        // 插入菜单记录
        $menuData = [
            'pid' => $data['menu_id'],
            'controller_name' => $controllerName,
            'title' => $menuInfo['title'] . '审批组',
            'pk' => $groupPk,
            'table_name' => $groupTableName,
            'create_code' => 1,
            'status' => 0,
            'sortid' => $data['menu_id'],
            'create_table' => 1,
            'url' => '',
            'icon' => null,
            'tab_config' => null,
            'app_id' => $menuInfo['app_id'],
            'is_post' => 0,
            'upload_config_id' => 0,
            'connect' => $connect,
            'page_type' => 1,
            'home_show' => 0,
            'menu_pic' => '',
            'notice' => '',
            'prompt' => 0,
            'prompt_session' => '',
            'flow_subtable' => 1,
            'menu_show' => 0,
        ];

        $newMenuId = Db::connect($connect)->name('menu')->insertGetId($menuData);

        if (!$newMenuId) {
            throw new \Exception('插入审批组菜单记录失败');
        }

        return $newMenuId;
    }

    /**
     * 3. 插入审批流菜单记录
     */
    private function insertFlowMenuRecord($connect, $data, $menuInfo) {
        $tableName = strtolower(trim($menuInfo['table_name']));
        $flowTableName = $tableName . $this->flowTableSuffix;
        $flowPk = $flowTableName . "_id";
        $controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $flowTableName)));

        // 检查是否已存在相同 controller_name 的记录
        $existingMenu = Db::connect($connect)->name('menu')->where('controller_name', $controllerName)->find();
        if ($existingMenu) {
            Db::connect($connect)->name('menu')->where('menu_id', $existingMenu['menu_id'])->delete();
            Db::connect($connect)->name('field')->where('menu_id', $existingMenu['menu_id'])->delete();
            Db::connect($connect)->name('action')->where('menu_id', $existingMenu['menu_id'])->delete();
        }

        // 插入菜单记录
        $menuData = [
            'pid' => $data['menu_id'],
            'controller_name' => $controllerName,
            'title' => $menuInfo['title'] . '审批流',
            'pk' => $flowPk,
            'table_name' => $flowTableName,
            'create_code' => 1,
            'status' => 0,
            'sortid' => $data['menu_id'] + 1,
            'create_table' => 1,
            'url' => '',
            'icon' => null,
            'tab_config' => null,
            'app_id' => $menuInfo['app_id'],
            'is_post' => 0,
            'upload_config_id' => 0,
            'connect' => $connect,
            'page_type' => 1,
            'home_show' => 0,
            'menu_pic' => '',
            'notice' => '',
            'prompt' => 0,
            'prompt_session' => '',
            'flow_subtable' => 1,
            'menu_show' => 0,
        ];

        $newMenuId = Db::connect($connect)->name('menu')->insertGetId($menuData);

        if (!$newMenuId) {
            throw new \Exception('插入审批流菜单记录失败');
        }

        return $newMenuId;
    }

    /**
     * 4. 插入审批组字段数据 - 根据实际数据修正
     */
    private function insertGroupFieldData($connect, $groupMenuId, $prefix, $menuInfo) {
        $tableName = strtolower(trim($menuInfo['table_name']));
        $groupTableName = $tableName . $this->groupTableSuffix;
        $groupPk = $groupTableName . "_id";

        $fieldConfigs = [
            // 编号字段
            [
                'menu_id' => $groupMenuId,
                'title' => '编号',
                'field' => $groupPk,
                'type' => 1,
                'list_show' => 2,
                'search_type' => null,
                'post_status' => null,
                'create_table_field' => 1,
                'validate' => null,
                'rule' => null,
                'sortid' => 1,
                'sql' => null,
                'default_value' => null,
                'datatype' => 'int',
                'length' => '11',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => null,
                'width' => '70',
                'datetime_config' => null,
                'other_config' => null,
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => '值占位文本',
                'tx_tiaojian' => 0,
                'tx_zhi' => '',
                'tx_color' => '',
                'improve_tiaojian' => 0,
                'improve_zhi' => '',
                'improve_color' => null,
                'list_background_config' => '[]',
                'tx_config' => '[]',
                'field_show' => 1
            ],
            // 流程组名字段
            [
                'menu_id' => $groupMenuId,
                'title' => '审批流名称',
                'field' => $groupTableName . '_name',
                'type' => 1,
                'list_show' => 2,
                'search_type' => 2,
                'post_status' => 1,
                'create_table_field' => 1,
                'validate' => ',notempty',
                'rule' => null,
                'sortid' => 2,
                'sql' => null,
                'default_value' => '',
                'datatype' => 'varchar',
                'length' => '250',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => ['required'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '请输入审批流名称',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => '值占位文本',
                'tx_tiaojian' => 0,
                'tx_zhi' => '',
                'tx_color' => '',
                'improve_tiaojian' => 0,
                'improve_zhi' => '',
                'improve_color' => null,
                'list_background_config' => '[]',
                'tx_config' => '[]',
                'field_show' => 1
            ]
        ];

        $result = Db::connect($connect)->name('field')->insertAll($fieldConfigs);

        if (!$result) {
            throw new \Exception('插入审批组字段记录失败');
        }
    }

    /**
     * 5. 插入审批流字段数据 - 根据实际数据修正
     */
    private function insertFlowFieldData($connect, $flowMenuId, $prefix, $menuInfo, $groupMenuId, $flow_group_field, $application) {
        $username = explode('|', $application['login_fields'])[0];
        $tableName = strtolower(trim($menuInfo['table_name']));
        $flowTableName = $tableName . $this->flowTableSuffix;
        $flowPk = $flowTableName . "_id";
        $groupTableName = $tableName . $this->groupTableSuffix;
        $groupPk = $groupTableName . "_id";
        $mainPk = $flow_group_field;

        $fieldConfigs = [
            // 编号字段
            [
                'menu_id' => $flowMenuId,
                'title' => '编号',
                'field' => $flowPk,
                'type' => 1,
                'list_show' => 2,
                'search_type' => null,
                'post_status' => null,
                'create_table_field' => 1,
                'validate' => null,
                'rule' => null,
                'sortid' => 1,
                'sql' => null,
                'default_value' => null,
                'datatype' => 'int',
                'length' => '11',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => null,
                'width' => '70',
                'datetime_config' => null,
                'other_config' => null,
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => '值占位文本',
                'tx_tiaojian' => 0,
                'tx_zhi' => '',
                'tx_color' => '',
                'improve_tiaojian' => 0,
                'improve_zhi' => '',
                'improve_color' => null,
                'list_background_config' => '[]',
                'tx_config' => '[]',
                'field_show' => 1
            ],
            // 关联信息字段
            [
                'menu_id' => $flowMenuId,
                'title' => '关联信息',
                'field' => $mainPk,
                'type' => 33,
                'list_show' => 0,
                'search_type' => 1,
                'post_status' => 1,
                'create_table_field' => 1,
                'validate' => '',
                'rule' => null,
                'sortid' => 2,
                'sql' => '',
                'default_value' => '',
                'datatype' => 'varchar',
                'length' => '250',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => ['required'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '请选择' . $menuInfo['title'],
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => '值占位文本',
                'tx_tiaojian' => 0,
                'tx_zhi' => '',
                'tx_color' => '',
                'improve_tiaojian' => 0,
                'improve_zhi' => '',
                'improve_color' => null,
                'list_background_config' => '[]',
                'tx_config' => '[]',
                'field_show' => 1
            ],
            // 审批组字段
            [
                'menu_id' => $flowMenuId,
                'title' => '审批流名称',
                'field' => $groupTableName . '_id',
                'type' => 2,
                'list_show' => 2,
                'search_type' => 1,
                'post_status' => 1,
                'create_table_field' => 1,
                'validate' => ',notempty',
                'rule' => null,
                'sortid' => 3,
                'sql' => "select {$groupPk},{$groupTableName}_name from {$prefix}{$groupTableName}",
                'default_value' => '',
                'datatype' => 'int',
                'length' => '11',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => ['required', 'fanzhuan'],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '请选择审批流',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => '值占位文本',
                'tx_tiaojian' => 0,
                'tx_zhi' => '',
                'tx_color' => '',
                'improve_tiaojian' => 0,
                'improve_zhi' => '',
                'improve_color' => null,
                'list_background_config' => '[]',
                'tx_config' => '[]',
                'field_show' => 1
            ],
            // 审批流字段
            [
                'menu_id' => $flowMenuId,
                'title' => '审批流设置',
                'field' => $flowTableName,
                'type' => 42,
                'list_show' => 2,
                'search_type' => 3,
                'post_status' => 1,
                'create_table_field' => 1,
                'validate' => ',notempty',
                'rule' => null,
                'sortid' => 4,
                'sql' => "select {$application['pk']},{$username} from {$prefix}{$application['login_table']}",
                'default_value' => '',
                'datatype' => 'longtext',
                'length' => '0',
                'indexdata' => null,
                'show_condition' => null,
                'item_config' => '',
                'width' => null,
                'datetime_config' => null,
                'other_config' => json_encode([
                    'shuxing' => [],
                    'guige' => [],
                    'address_type' => 1,
                    'placeholder' => '请设置审批流程',
                    'liandong_field' => ''
                ]),
                'belong_table' => '',
                'icon' => null,
                'key_placeholder' => '',
                'value_placeholder' => '值占位文本',
                'tx_tiaojian' => 0,
                'tx_zhi' => '',
                'tx_color' => '',
                'improve_tiaojian' => 0,
                'improve_zhi' => '',
                'improve_color' => null,
                'list_background_config' => '[]',
                'tx_config' => '[]',
                'field_show' => 1
            ]
        ];

        $result = Db::connect($connect)->name('field')->insertAll($fieldConfigs);

        if (!$result) {
            throw new \Exception('插入审批流字段记录失败');
        }
    }

    /**
     * 6. 插入审批组动作数据
     */
    private function insertGroupActionData($connect, $groupMenuId) {
        $actionConfigs = [
            // 数据列表
            [
                'menu_id' => $groupMenuId,
                'name' => '数据列表',
                'action_name' => 'index',
                'type' => 1,
                'icon' => null,
                'pagesize' => '20',
                'group_button_status' => 0,
                'list_button_status' => null,
                'button_color' => null,
                'fields' => null,
                'sortid' => 1,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => null,
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 修改排序开关 - 根据实际数据添加
            [
                'menu_id' => $groupMenuId,
                'name' => '修改排序开关',
                'action_name' => 'updateExt',
                'type' => 12,
                'icon' => null,
                'pagesize' => '',
                'group_button_status' => 0,
                'list_button_status' => null,
                'button_color' => null,
                'fields' => null,
                'sortid' => 2,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => null,
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 添加数据 - 修正对话框大小为85%
            [
                'menu_id' => $groupMenuId,
                'name' => '添加数据',
                'action_name' => 'add',
                'type' => 2,
                'icon' => 'el-icon-plus',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => null,
                'button_color' => 'success',
                'fields' => null,
                'sortid' => 3,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => '85%',
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 修改数据 - 修正对话框大小为85%，list_button_status为1
            [
                'menu_id' => $groupMenuId,
                'name' => '修改数据',
                'action_name' => 'update',
                'type' => 3,
                'icon' => 'el-icon-edit',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => 1,
                'button_color' => 'primary',
                'fields' => null,
                'sortid' => 4,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => '85%',
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 删除数据
            [
                'menu_id' => $groupMenuId,
                'name' => '删除数据',
                'action_name' => 'delete',
                'type' => 4,
                'icon' => 'el-icon-delete',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => 1,
                'button_color' => 'danger',
                'fields' => null,
                'sortid' => 5,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => null,
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 查看详情 - 修正对话框大小为85%
            [
                'menu_id' => $groupMenuId,
                'name' => '查看详情',
                'action_name' => 'detail',
                'type' => 5,
                'icon' => 'el-icon-view',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => null,
                'button_color' => 'info',
                'fields' => null,
                'sortid' => 6,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => '85%',
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ]
        ];

        $result = Db::connect($connect)->name('action')->insertAll($actionConfigs);

        if (!$result) {
            throw new \Exception('插入审批组动作记录失败');
        }
    }

    /**
     * 7. 插入审批流动数据
     */
    private function insertFlowActionData($connect, $flowMenuId, $menuInfo) {
        $tableName = strtolower(trim($menuInfo['table_name']));
        $actionConfigs = [
            // 数据列表
            [
                'menu_id' => $flowMenuId,
                'name' => '数据列表',
                'action_name' => 'index',
                'type' => 1,
                'icon' => null,
                'pagesize' => '20',
                'group_button_status' => 0,
                'list_button_status' => null,
                'button_color' => null,
                'fields' => null,
                'sortid' => 1,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => null,
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 修改排序开关 - 根据实际数据添加
            [
                'menu_id' => $flowMenuId,
                'name' => '修改排序开关',
                'action_name' => 'updateExt',
                'type' => 12,
                'icon' => null,
                'pagesize' => '',
                'group_button_status' => 0,
                'list_button_status' => null,
                'button_color' => null,
                'fields' => null,
                'sortid' => 2,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => null,
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 添加数据 - 修正对话框大小为85%
            [
                'menu_id' => $flowMenuId,
                'name' => '添加数据',
                'action_name' => 'add',
                'type' => 2,
                'icon' => 'el-icon-plus',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => null,
                'button_color' => 'success',
                'fields' => null,
                'sortid' => 3,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => '85%',
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => json_encode([
                    'export_type' => '',
                    'hook' => [],
                    'excel' => '',
                    'left_tree_show' => '',
                    'tree_show' => 1,
                    'after_hook' => '',
                    'befor_hook' => '',
                    'printer_status' => 2,
                    'list_button_style' => 1,
                    'guige' => [],
                    'detail_search_field' => [],
                    'custom_form' => 1,
                    'custom_form_config' => json_encode([
                        'type' => 'approval_flow',
                        'config' => [
                            'main_table' => $menuInfo['table_name'],
                            'main_pk' => $menuInfo['pk'],
                            'group_table' => $tableName . $this->groupTableSuffix,
                        ]
                    ])
                ]),
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 修改数据 - 修正对话框大小为85%，list_button_status为1
            [
                'menu_id' => $flowMenuId,
                'name' => '修改数据',
                'action_name' => 'update',
                'type' => 3,
                'icon' => 'el-icon-edit',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => 1,
                'button_color' => 'primary',
                'fields' => null,
                'sortid' => 4,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => '85%',
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => json_encode([
                    'export_type' => '',
                    'hook' => [],
                    'excel' => '',
                    'left_tree_show' => '',
                    'tree_show' => 1,
                    'after_hook' => '',
                    'befor_hook' => '',
                    'printer_status' => 2,
                    'list_button_style' => 1,
                    'guige' => [],
                    'detail_search_field' => [],
                    'custom_form' => 1,
                    'custom_form_config' => json_encode([
                        'type' => 'approval_flow',
                        'config' => [
                            'main_table' => $menuInfo['table_name'],
                            'main_pk' => $menuInfo['pk'],
                            'group_table' => $tableName . $this->groupTableSuffix,
                        ]
                    ])
                ]),
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 删除数据
            [
                'menu_id' => $flowMenuId,
                'name' => '删除数据',
                'action_name' => 'delete',
                'type' => 4,
                'icon' => 'el-icon-delete',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => 1,
                'button_color' => 'danger',
                'fields' => null,
                'sortid' => 5,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => null,
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => null,
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ],
            // 查看详情 - 修正对话框大小为85%
            [
                'menu_id' => $flowMenuId,
                'name' => '查看详情',
                'action_name' => 'detail',
                'type' => 5,
                'icon' => 'el-icon-view',
                'pagesize' => '',
                'group_button_status' => 1,
                'list_button_status' => null,
                'button_color' => 'info',
                'fields' => null,
                'sortid' => 6,
                'orderby' => null,
                'tree_config' => null,
                'jump' => null,
                'server_create_status' => 1,
                'vue_create_status' => 1,
                'cache_time' => null,
                'api_auth' => null,
                'img_auth' => null,
                'sms_auth' => null,
                'list_filter' => null,
                'tab_config' => null,
                'sql' => null,
                'dialog_size' => '85%',
                'status_val' => null,
                'validate' => null,
                'select_type' => 1,
                'table_height' => null,
                'left_tree_sql' => null,
                'with_join' => null,
                'other_config' => json_encode([
                    'export_type' => '',
                    'hook' => [],
                    'excel' => '',
                    'left_tree_show' => '',
                    'tree_show' => 1,
                    'after_hook' => '',
                    'befor_hook' => '',
                    'printer_status' => 2,
                    'list_button_style' => 1,
                    'guige' => [],
                    'detail_search_field' => [],
                    'custom_form' => 1,
                    'custom_form_config' => json_encode([
                        'type' => 'approval_flow',
                        'config' => [
                            'main_table' => $menuInfo['table_name'],
                            'main_pk' => $menuInfo['pk'],
                            'group_table' => $tableName . $this->groupTableSuffix,
                        ]
                    ])
                ]),
                'dialog_type' => null,
                'version' => null,
                'remark' => null,
                'remark_desc' => null,
                'q_template' => null,
                'h_php' => null,
                'action_pid' => null,
                'action_show' => 1
            ]
        ];

        $result = Db::connect($connect)->name('action')->insertAll($actionConfigs);

        if (!$result) {
            throw new \Exception('插入审批流动记录失败');
        }
    }

    /**
     * @param $application
     * @return mixed
     * @desc      修复admin参数
     * @author    JiaWei
     * @email     975162853@qq.com
     * @date      2025/12/19
     * @time      11:30
     */
    private function changeApplication($application) {
        if ($application['app_dir'] == 'admin') {
            $application['pk'] = 'user_id';
            $application['login_table'] = 'admin_user';
            $application['login_fields'] = 'name|pwd';
        }
        return $application;
    }
    /**************************************************************************/

    /**************************************************************************/
    /**
     * @return \think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @desc      同步更新
     * @author    JiaWei
     * @email     975162853@qq.com
     * @date      2026/1/10
     * @time      13:43
     */
    public function esSynchronization() {
        $param = $this->request->param();
        $menu = Menu::find($param['id']);
        $connect = $menu['connect'] ?: config('database.default');
        $prefix = config('database.connections.' . $connect . '.prefix');
        $esIndex = $prefix . $menu['table_name'];
        $tabel_pk = $menu['pk'];
        if ($this->initEsService($esIndex)) {
            $this->esService->deleteIndex();
            return $this->esService->runEsScriptWithShellExec($esIndex, $tabel_pk);
        }
    }

    /**
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @desc      查看es同步日志
     * @author    JiaWei
     * @email     975162853@qq.com
     * @date      2026/1/10
     * @time      13:43
     */
    public function esLog() {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $menu = Menu::find($param['id']);
            $connect = $menu['connect'] ?: config('database.default');
            $prefix = config('database.connections.' . $connect . '.prefix');
            $esIndex = $prefix . $menu['table_name'];
            if ($this->initEsService($esIndex)) {
                return $this->esService->viewLog($esIndex);
            }
        }

        return view('controller/Sys/view/es_log');
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @desc      检查是否开启es
     * @author    JiaWei
     * @email     975162853@qq.com
     * @date      2026/1/10
     * @time      13:43
     */
    public function esCheck() {
        $param = $this->request->param();
        if (!isset($param['id']) || empty($param['id'])) {
            throw new ValidateException("参数错误");
        }
        $menu = Menu::find($param['id']);
        if ($menu['enable_es'] != 1) {
            throw new ValidateException("未启用启ES,无法同步");
        }
        return json(['status' => 200]);
    }

    /**
     * 初始化ES服务
     * @return bool
     */
    private function initEsService($esIndex) {
        // es验证
        if (empty(config('my.esdb_hostname'))
            || empty(config('my.esdb_username'))
            || empty(config('my.esdb_password'))) {
            throw new ValidateException("请在 控制台-配置管理-系统配置 中配置es相关信息");
        }
        if ($this->esService === null) {
            try {
                $this->esService = new EsService($esIndex);
                return true;
            } catch (\Exception $e) {
                Log::error('ES服务初始化失败: ' . $e->getMessage());
                $this->esService = false;
                return false;
            }
        }

        return $this->esService !== false;
    }
    /**************************************************************************/
}
