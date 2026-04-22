SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cd_action
-- ----------------------------
DROP TABLE IF EXISTS `cd_action`;
CREATE TABLE `cd_action`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '点击添加备注',
  `menu_id` int NOT NULL COMMENT '模块ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '动作名称',
  `action_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '动作名称',
  `type` tinyint NOT NULL COMMENT '点击添加备注',
  `icon` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'icon图标',
  `pagesize` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '每页显示数据条数',
  `group_button_status` tinyint NULL DEFAULT NULL COMMENT '按钮组显示状态',
  `list_button_status` tinyint NULL DEFAULT NULL COMMENT '按钮是否显示列表',
  `button_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '操作的字段',
  `sortid` int NULL DEFAULT 0 COMMENT '排序',
  `orderby` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '配置排序',
  `tree_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `jump` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '按钮跳转地址',
  `server_create_status` tinyint NULL DEFAULT 1,
  `vue_create_status` tinyint NULL DEFAULT 1 COMMENT '视图生成',
  `cache_time` mediumint NULL DEFAULT NULL COMMENT '缓存时间',
  `api_auth` tinyint NULL DEFAULT NULL COMMENT '接口是否鉴权',
  `img_auth` tinyint NULL DEFAULT NULL COMMENT '图片验证码鉴权',
  `sms_auth` tinyint NULL DEFAULT NULL COMMENT '短信验证',
  `list_filter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '过滤',
  `tab_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `sql` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `dialog_size` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status_val` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `validate` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '无备注',
  `select_type` tinyint NULL DEFAULT 1 COMMENT '选中方式 1多选 2单选',
  `table_height` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '表格高度',
  `left_tree_sql` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '侧栏生成的sql',
  `with_join` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '关联模型',
  `other_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '字段配置',
  `dialog_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '页面打开方式',
  `version` int NULL DEFAULT NULL COMMENT 'apipost接口文档版本号',
  `remark` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '方法备注',
  `remark_desc` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '功能描述',
  `q_template` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `h_php` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `action_pid` int NULL DEFAULT NULL COMMENT '联动方法id（例如：审批流关联方法）',
  `action_show` tinyint NULL DEFAULT 1 COMMENT '列表显示 0不显示 1显示',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `menu_id`(`menu_id` ASC) USING BTREE,
  INDEX `name`(`name` ASC) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `pagesize`(`pagesize` ASC) USING BTREE,
  INDEX `group_button_status`(`group_button_status` ASC) USING BTREE,
  INDEX `list_button_status`(`list_button_status` ASC) USING BTREE,
  INDEX `sortid`(`sortid` ASC) USING BTREE,
  INDEX `server_create_status`(`server_create_status` ASC) USING BTREE,
  INDEX `vue_create_status`(`vue_create_status` ASC) USING BTREE,
  INDEX `select_type`(`select_type` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4941 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_action
-- ----------------------------
INSERT INTO `cd_action` VALUES (3316, 4, '数据列表', 'index', 1, NULL, '20', 0, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3317, 4, '修改排序开关', 'updateExt', 12, NULL, '', 0, NULL, NULL, NULL, 2, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3318, 4, '添加', 'add', 2, 'el-icon-plus', '', 1, NULL, 'success', NULL, 3, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3319, 4, '修改', 'update', 3, 'el-icon-edit', '', 1, 1, 'primary', NULL, 4, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3320, 4, '删除', 'delete', 4, 'el-icon-delete', '', 1, 1, 'danger', NULL, 5, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3321, 4, '查看详情', 'detail', 5, 'el-icon-view', '', 1, NULL, 'info', NULL, 6, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3925, 6, '数据列表', 'index', 1, NULL, '20', 0, NULL, NULL, '', 1, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '', '', '', NULL, NULL, NULL, 1, NULL, NULL, '[{\"fields\":[\"name\"],\"fk\":\"role_id\",\"relative_table\":\"Role\",\"pk\":\"role_id\",\"table_name\":\"role\",\"connect\":\"mysql\"}]', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3926, 6, '修改排序开关', 'updateExt', 12, NULL, '', 0, NULL, NULL, NULL, 2, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3927, 6, '添加', 'add', 2, 'el-icon-plus', '', 1, NULL, 'success', NULL, 3, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3928, 6, '修改', 'update', 3, 'el-icon-edit', '', 1, 1, 'primary', NULL, 4, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3929, 6, '删除', 'delete', 4, 'el-icon-delete', '', 1, 1, 'danger', '', 5, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, 1, NULL, NULL, '', '{\"hook\":[],\"befor_hook\":\"app\\/admin\\/hook\\/Adminuser@beforDelete\",\"after_hook\":\"\"}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3930, 6, '查看详情', 'detail', 5, 'el-icon-view', '', 1, NULL, 'info', NULL, 6, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3931, 7, '数据列表', 'index', 1, NULL, '20', 0, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3932, 7, '修改排序开关', 'updateExt', 12, NULL, '', 0, NULL, NULL, NULL, 2, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3933, 7, '添加', 'add', 2, 'el-icon-plus', '', 1, NULL, 'success', NULL, 3, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3934, 7, '修改', 'update', 3, 'el-icon-edit', '', 1, 1, 'primary', NULL, 4, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3935, 7, '删除', 'delete', 4, 'el-icon-delete', '', 1, 1, 'danger', NULL, 5, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3937, 8, '数据列表', 'index', 1, NULL, '20', 0, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3941, 8, '删除', 'delete', 4, 'el-icon-delete', '', 1, 1, 'danger', NULL, 5, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3942, 8, '查看详情', 'detail', 5, 'el-icon-view', '', 1, NULL, 'info', 'application_name,username,url,ip,useragent,content,type,create_time,errmsg', 6, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '', '', '', '600px', NULL, NULL, 1, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3949, 8, '导出', 'dumpdata', 11, 'el-icon-download', '', 1, NULL, 'warning', NULL, 12, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3974, 6, '重置密码', 'resetPwd', 6, 'el-icon-lock', '20', 1, NULL, 'primary', 'pwd', 3974, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '', '', '', '', NULL, NULL, 1, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (3975, 3, '配置表单', 'index', 14, NULL, '20', NULL, NULL, NULL, '', 3975, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '', '[{\"tab_fields\":[\"site_title\",\"logo\",\"keyword\",\"descrip\",\"copyright\",\"tinymce\",\"jzd\",\"pop_status\",\"search_status\",\"watermark_status\",\"denglubaohu\"],\"tab_name\":\"基本信息\"},{\"tab_fields\":[\"filesize\",\"filetype\",\"water_status\",\"water_position\",\"water_alpha\",\"domain\",\"deespseekkey\",\"deepseekkey\",\"bdKey\",\"gdKey\",\"txKey\"],\"tab_name\":\"拓展信息\"}]', '', '', NULL, NULL, 1, NULL, NULL, '', '{\"hook\":[],\"guige\":[],\"detail_search_field\":[],\"befor_hook\":\"\",\"after_hook\":\"\"}', NULL, NULL, NULL, NULL, '<div class=\"super-page\">\n  <h1>自定义页面</h1>\n</div>', 'public function handle($request) {\n    // 处理请求\n    return [];\n}', NULL, 1);
INSERT INTO `cd_action` VALUES (4424, 19, '秘钥管理', 'index', 14, NULL, '', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (4500, 104, '数据列表', 'index', 1, NULL, '20', 0, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (4501, 104, '修改排序开关', 'updateExt', 12, NULL, '', 0, NULL, NULL, NULL, 2, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (4502, 104, '添加', 'add', 2, 'el-icon-plus', '', 1, NULL, 'success', NULL, 3, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (4503, 104, '修改', 'update', 3, 'el-icon-edit', '', 1, 1, 'primary', NULL, 4, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (4504, 104, '删除', 'delete', 4, 'el-icon-delete', '', 1, 1, 'danger', NULL, 5, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (4505, 104, '查看详情', 'detail', 5, 'el-icon-view', '', 1, NULL, 'info', NULL, 6, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '600px', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_action` VALUES (4678, 6, '修改角色', 'batupdate', 19, 'el-icon-edit', '20', 1, NULL, 'primary', 'role_id', 4678, '', '', '', 1, 1, NULL, NULL, NULL, NULL, '', '', '', '1000px', '', NULL, 1, '', NULL, '', '{\"export_type\":\"\",\"hook\":[],\"excel\":\"\",\"left_tree_show\":\"\",\"tree_show\":1,\"after_hook\":\"\",\"befor_hook\":\"\",\"printer_status\":2,\"list_button_style\":1}', NULL, NULL, NULL, NULL, '<div class=\"super-page\">\n  <h1>自定义页面</h1>\n</div>', 'public function ygluntan() {\n   if (!$this->request->isPost()){\n     return view(\'ygluntan\');\n   }\n}\n', NULL, 1);
INSERT INTO `cd_action` VALUES (4927, 2, '查看详情', 'detail', 5, 'el-icon-view', '20', 1, NULL, 'info', '', 4927, '', '', '', 1, 1, NULL, NULL, NULL, NULL, '', '', '', '', '', NULL, 1, '', NULL, '', '{\"export_type\":\"\",\"hook\":[],\"excel\":\"\",\"left_tree_show\":\"\",\"tree_show\":1,\"after_hook\":\"\",\"befor_hook\":\"\",\"printer_status\":2,\"list_button_style\":1}', NULL, NULL, NULL, NULL, '<div class=\"super-page\">\n  <h1>自定义页面</h1>\n</div>', 'public function ygluntan() {\n   if (!$this->request->isPost()){\n     return view(\'ygluntan\');\n   }\n}\n', NULL, 1);

-- ----------------------------
-- Table structure for cd_action_remarks
-- ----------------------------
DROP TABLE IF EXISTS `cd_action_remarks`;
CREATE TABLE `cd_action_remarks`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `action_id` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'action_id',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '代码内容',
  `description` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '功能描述',
  `create_time` int NULL DEFAULT NULL COMMENT '创建时间',
  `menu_id` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '所属菜单',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `action_id`(`action_id` ASC) USING BTREE,
  INDEX `menu_id`(`menu_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '版本记录' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_action_remarks
-- ----------------------------

-- ----------------------------
-- Table structure for cd_admin_user
-- ----------------------------
DROP TABLE IF EXISTS `cd_admin_user`;
CREATE TABLE `cd_admin_user`  (
  `user_id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户姓名',
  `user` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `pwd` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '密码',
  `role_id` int NULL DEFAULT NULL COMMENT '所属分组',
  `note` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `status` tinyint NULL DEFAULT NULL COMMENT '状态',
  `create_time` int NULL DEFAULT NULL COMMENT '创建时间',
  `session_token` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '唯一登录',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 341 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_admin_user
-- ----------------------------
INSERT INTO `cd_admin_user` VALUES (1, '铁粉', 'admin', '35bfa44f104ddfe466d1889daeff6e35', 1, '超级管理员', 1, 1548558919, 'e3321288b635c0e315903208d6a89ea7');

-- ----------------------------
-- Table structure for cd_application
-- ----------------------------
DROP TABLE IF EXISTS `cd_application`;
CREATE TABLE `cd_application`  (
  `app_id` int NOT NULL AUTO_INCREMENT,
  `application_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `app_dir` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` tinyint NULL DEFAULT NULL,
  `app_type` tinyint NULL DEFAULT NULL,
  `login_table` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `login_fields` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `domain` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `pk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录表主键',
  `connect` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '数据库连接',
  `project_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'apipost项目id',
  `register` tinyint(1) NULL DEFAULT NULL COMMENT '是否开启注册 1开启 0禁用',
  PRIMARY KEY (`app_id`) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `app_type`(`app_type` ASC) USING BTREE,
  INDEX `pk`(`pk` ASC) USING BTREE,
  INDEX `project_id`(`project_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 310 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_application
-- ----------------------------
INSERT INTO `cd_application` VALUES (1, '后台管理', 'admin', 1, 1, '', '', '', '', NULL, NULL, NULL);

-- ----------------------------
-- Table structure for cd_base_config
-- ----------------------------
DROP TABLE IF EXISTS `cd_base_config`;
CREATE TABLE `cd_base_config`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_base_config
-- ----------------------------
INSERT INTO `cd_base_config` VALUES (1, 'site_title', '开源泽润');
INSERT INTO `cd_base_config` VALUES (2, 'logo', '/uploads/admin/202508/688c25bf0b48e.png');
INSERT INTO `cd_base_config` VALUES (3, 'keyword', '');
INSERT INTO `cd_base_config` VALUES (4, 'descrip', '平衡之道|创新之源');
INSERT INTO `cd_base_config` VALUES (5, 'copyright', '泽润九福');
INSERT INTO `cd_base_config` VALUES (6, 'filesize', '100');
INSERT INTO `cd_base_config` VALUES (7, 'filetype', 'gif,png,jpg,jpeg,doc,docx,xls,xlsx,csv,pdf,rar,zip,txt,mp4,flv,wgt');
INSERT INTO `cd_base_config` VALUES (8, 'water_status', '0');
INSERT INTO `cd_base_config` VALUES (9, 'water_position', '5');
INSERT INTO `cd_base_config` VALUES (10, 'domain', '');
INSERT INTO `cd_base_config` VALUES (20, 'water_alpha', '90');
INSERT INTO `cd_base_config` VALUES (21, 'pop_status', '0');
INSERT INTO `cd_base_config` VALUES (22, 'search_status', '1');
INSERT INTO `cd_base_config` VALUES (23, 'watermark_status', '0');
INSERT INTO `cd_base_config` VALUES (25, 'bdKey', NULL);
INSERT INTO `cd_base_config` VALUES (26, 'gdKey', NULL);
INSERT INTO `cd_base_config` VALUES (27, 'txKey', NULL);
INSERT INTO `cd_base_config` VALUES (28, 'deepseekkey', NULL);

-- ----------------------------
-- Table structure for cd_field
-- ----------------------------
DROP TABLE IF EXISTS `cd_field`;
CREATE TABLE `cd_field`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_id` int NOT NULL COMMENT '模块ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字段名称',
  `field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` smallint NOT NULL COMMENT '表单类型1输入框 2下拉框 3单选框 4多选框 5上传图片 6编辑器 7时间',
  `list_show` tinyint NULL DEFAULT NULL COMMENT '列表显示',
  `search_type` tinyint NULL DEFAULT NULL COMMENT '1精确匹配 2模糊搜索',
  `post_status` tinyint NULL DEFAULT NULL COMMENT '是否前台录入',
  `create_table_field` tinyint NULL DEFAULT NULL,
  `validate` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '验证方式',
  `rule` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '验证规则',
  `sortid` mediumint NULL DEFAULT 0 COMMENT '排序号',
  `sql` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '字段配置数据源sql',
  `default_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `datatype` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '字段数据类型',
  `length` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '字段长度',
  `indexdata` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '索引',
  `show_condition` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `item_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `width` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '单元格宽度',
  `datetime_config` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '其他配置',
  `other_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `belong_table` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '虚拟字段所属表 用户多表关联',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '字体图标',
  `key_placeholder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '建站位文本',
  `value_placeholder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '值占位文本',
  `tx_tiaojian` smallint NULL DEFAULT NULL COMMENT '鎻愰啋鏉′欢',
  `tx_zhi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '体型值',
  `tx_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '提醒颜色',
  `improve_tiaojian` smallint NULL DEFAULT NULL COMMENT '完善条件',
  `improve_zhi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '完善条件校验值',
  `improve_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '完善条件提醒颜色',
  `tx_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '列表文字变色配置项',
  `list_background_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '列表背景变色配置项',
  `field_show` tinyint NULL DEFAULT 1 COMMENT '列表显示 0不显示 1显示',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `menu_id`(`menu_id` ASC) USING BTREE,
  INDEX `tx_tiaojian`(`tx_tiaojian` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5457 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_field
-- ----------------------------
INSERT INTO `cd_field` VALUES (3579, 6, '编号', 'user_id', 1, 2, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'int', '11', NULL, NULL, '', '70', NULL, 'null', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3580, 6, '用户姓名', 'name', 1, 2, 0, 1, 1, ',notempty', NULL, 3, NULL, NULL, 'varchar', '250', NULL, NULL, '', '90', NULL, '{\"shuxing\":[]}', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3581, 6, '登录账号', 'user', 1, 2, 1, 1, 0, ',notempty', NULL, 4, NULL, NULL, 'varchar', '250', NULL, NULL, '', '90', NULL, '{\"shuxing\":[]}', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3582, 6, '用户密码', 'pwd', 7, 0, 0, 1, 0, ',notempty', NULL, 6, NULL, NULL, 'varchar', '250', NULL, NULL, '', '90', NULL, '{\"shuxing\":[]}', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3583, 6, '所属角色', 'role_id', 2, 0, 1, 1, 0, ',notempty', NULL, 8, 'select role_id,name from pre_role', NULL, 'int', '11', NULL, NULL, '', '90', NULL, '{\"shuxing\":[],\"guige\":[[]],\"tx_tiaojian\":\"\",\"tx_zhi\":\"\"}', NULL, NULL, '', '值占位文本', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3584, 6, '用户备注', 'note', 1, 3, 0, 1, 0, NULL, NULL, 10, NULL, NULL, 'varchar', '250', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3585, 6, '账号状态', 'status', 4, 2, 1, 1, 0, ',notempty', NULL, 9, NULL, NULL, 'smallint', '6', NULL, NULL, '[{\"key\":\"正常\",\"val\":\"1\",\"label_color\":\"primary\"},{\"key\":\"禁用\",\"val\":\"0\",\"label_color\":\"danger\"}]', '90', NULL, '{\"shuxing\":[]}', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3586, 6, '创建时间', 'create_time', 11, 0, 0, 1, 0, '', NULL, 11, NULL, NULL, 'int', '11', NULL, NULL, '', '90', 'date', '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3588, 7, '角色名称', 'name', 1, 2, 1, 1, 0, ',notempty', NULL, 2, NULL, NULL, 'varchar', '36', NULL, NULL, '', '110', NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3589, 7, '状态', 'status', 6, 2, 1, 1, 0, '', NULL, 3, NULL, NULL, 'tinyint', '4', NULL, NULL, '[{\"key\":\"正常\",\"val\":\"1\",\"label_color\":\"primary\"},{\"key\":\"禁用\",\"val\":\"0\",\"label_color\":\"danger\"}]', '110', NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3591, 7, '描述', 'description', 1, 3, 0, 1, 0, NULL, NULL, 5, NULL, NULL, 'text', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3592, 8, '编号', 'id', 1, 2, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'int', '11', NULL, NULL, NULL, '70', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3593, 8, '应用名', 'application_name', 1, 2, 0, 1, 0, NULL, NULL, 2, NULL, NULL, 'varchar', '50', NULL, NULL, NULL, '100', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3594, 8, '用户名', 'username', 1, 2, 1, 1, 0, NULL, NULL, 3, NULL, NULL, 'varchar', '250', NULL, NULL, NULL, '100', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3595, 8, '请求url', 'url', 1, 3, 0, 1, 0, NULL, NULL, 4, NULL, NULL, 'varchar', '250', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3596, 8, '客户端ip', 'ip', 1, 2, 0, 1, 0, NULL, NULL, 5, NULL, NULL, 'varchar', '250', NULL, NULL, NULL, '200', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3597, 8, '浏览器信息', 'useragent', 8, 0, 0, 1, 0, NULL, NULL, 6, NULL, NULL, 'varchar', '250', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3598, 8, '请求内容', 'content', 8, 0, 0, 1, 0, NULL, NULL, 7, NULL, NULL, 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3599, 8, '异常信息', 'errmsg', 8, 0, 0, 1, 0, NULL, NULL, 8, NULL, NULL, 'varchar', '250', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3600, 8, '创建时间', 'create_time', 11, 2, 1, 1, 0, '', NULL, 9, NULL, NULL, 'int', '11', NULL, NULL, '', '200', 'datetime', '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3601, 8, '类型', 'type', 2, 2, 1, 1, 0, '', NULL, 10, NULL, NULL, 'smallint', '6', NULL, NULL, '[{\"key\":\"登录日志\",\"val\":\"1\",\"label_color\":\"info\"},{\"key\":\"操作日志\",\"val\":\"2\",\"label_color\":\"warning\"},{\"key\":\"异常日志\",\"val\":\"3\",\"label_color\":\"danger\"}]', '200', NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3603, 4, '编号', 'id', 1, 2, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'int', '11', NULL, NULL, NULL, '70', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3604, 4, '配置名称', 'title', 1, 2, 0, 1, 0, ',notempty', NULL, 2, NULL, NULL, 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"shuxing\":[]}', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3605, 4, '覆盖原图', 'upload_replace', 6, 2, 1, 1, 0, '', NULL, 3, NULL, NULL, 'tinyint', '4', NULL, NULL, '[{\"key\":\"开启\",\"val\":\"1\"},{\"key\":\"关闭\",\"val\":\"0\"}]', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3606, 4, '生成缩略图', 'thumb_status', 6, 2, 1, 1, 0, '', NULL, 4, NULL, NULL, 'tinyint', '4', NULL, NULL, '[{\"key\":\"开启\",\"val\":\"1\"},{\"key\":\"关闭\",\"val\":\"0\"}]', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3607, 4, '缩略图宽', 'thumb_width', 1, 2, 0, 1, 0, NULL, NULL, 5, NULL, NULL, 'varchar', '250', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3608, 4, '缩略图高', 'thumb_height', 1, 2, 0, 1, 0, NULL, NULL, 6, NULL, NULL, 'varchar', '250', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3609, 4, '缩放类型', 'thumb_type', 2, 2, 1, 1, 0, '', NULL, 7, NULL, NULL, 'smallint', '6', NULL, NULL, '[{\"key\":\"等比例缩放\",\"val\":\"1\"},{\"key\":\"缩放后填充\",\"val\":\"2\"},{\"key\":\"居中裁剪\",\"val\":\"3\"},{\"key\":\"左上角裁剪\",\"val\":\"4\"},{\"key\":\"右下角裁剪\",\"val\":\"5\"},{\"key\":\"固定尺寸缩放\",\"val\":\"6\"}]', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3611, 3, '编号', 'id', 1, 2, NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, 'int', '11', NULL, NULL, NULL, '70', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3612, 3, '站点名称', 'site_title', 1, 2, 1, 1, 1, ',notempty', NULL, 3612, NULL, NULL, 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"address_type\":\"1\",\"shuxing\":[]}', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3613, 3, '站点logo', 'logo', 13, 2, 0, 1, 1, '', NULL, 3613, NULL, NULL, 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"shuxing\":[],\"crop\":\"\"}', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3614, 3, '站点关键词', 'keyword', 18, 2, 1, 1, 1, '', NULL, 3614, NULL, NULL, 'varchar', '250', NULL, NULL, '', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3615, 3, '站点描述', 'descrip', 8, 2, 1, 1, 1, '', '', 3615, '', '', 'text', '0', '', '', '', '', '', '[]', '', NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3616, 3, '站点版权', 'copyright', 1, 2, 1, 1, 1, '', NULL, 3616, NULL, NULL, 'varchar', '250', NULL, NULL, '', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3617, 3, '上传配置', 'filesize', 1, 2, 1, 1, 1, '', NULL, 3617, NULL, '0', 'varchar', '250', NULL, NULL, '', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3618, 3, '文件类型', 'filetype', 1, 2, 1, 1, 1, '', NULL, 3618, NULL, NULL, 'varchar', '250', NULL, NULL, '', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3619, 3, '水印状态', 'water_status', 4, 2, 1, 1, 1, '', NULL, 3619, NULL, NULL, 'smallint', '6', NULL, NULL, '[{\"key\":\"正常\",\"val\":\"1\",\"label_color\":\"primary\"},{\"key\":\"禁用\",\"val\":\"0\",\"label_color\":\"danger\"}]', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3620, 3, '水印位置', 'water_position', 2, 2, 1, 1, 1, '', NULL, 3620, NULL, NULL, 'smallint', '6', NULL, NULL, '[{\"key\":\"左上角水印\",\"val\":\"1\"},{\"key\":\"上居中水印\",\"val\":\"2\"},{\"key\":\"右上角水印\",\"val\":\"3\"},{\"key\":\"左居中水印\",\"val\":\"4\"},{\"key\":\"居中水印\",\"val\":\"5\"},{\"key\":\"右居中水印\",\"val\":\"6\"},{\"key\":\"左下角水印\",\"val\":\"7\"},{\"key\":\"下居中水印\",\"val\":\"8\"},{\"key\":\"右下角水印\",\"val\":\"9\"}]', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3622, 3, '绑定域名', 'domain', 1, 2, 1, 1, 1, '', NULL, 3623, NULL, NULL, 'varchar', '250', NULL, NULL, '', NULL, NULL, '[]', NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3759, 6, '所属分组', 'name', 1, 2, NULL, 0, 0, '', NULL, 7, NULL, NULL, 'varchar', '250', NULL, NULL, '', '90', NULL, '{\"address_type\":\"1\"}', 'role', NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (3801, 3, '水印透明度', 'water_alpha', 19, 2, 0, 1, 1, '', NULL, 3622, NULL, NULL, 'smallint', '6', NULL, NULL, '', NULL, NULL, '{\"address_type\":\"1\"}', '', NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (4375, 7, '编号', 'role_id', 1, 2, NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, 'int', '11', NULL, NULL, NULL, '70', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (4902, 104, '编号', 'id', 1, 2, NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, 'int', '11', NULL, NULL, NULL, '70', NULL, NULL, NULL, NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (4903, 104, 'action_id', 'action_id', 1, 2, 0, 1, 1, '', '/^[0-9]*$/', 4903, '', '', 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"address_type\":\"1\",\"now_time\":false,\"placeholder\":\"\",\"rand_config\":\"\",\"filetype\":\"\",\"liandong_field\":\"\",\"shuxing\":[\"tooltip\"],\"jdt\":\"changtiao\",\"remote_research_field\":\"\",\"rename_status\":\"\",\"default_tabs_value\":\"\",\"application_id\":\"\",\"crop\":\"\",\"time_search_tempate\":true,\"guige\":[[]],\"maxrows\":4,\"inputRemark\":\"\",\"rangetime_type\":\"date\"}', '', NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (4904, 104, '代码内容', 'content', 8, 2, 0, 1, 1, 'notempty', NULL, 4904, '', '', 'longtext', '0', NULL, NULL, '', NULL, NULL, '{\"shuxing\":[\"tooltip\"],\"guige\":[[]]}', '', NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (4905, 104, '功能描述', 'description', 8, 2, 0, 1, 1, 'notempty', NULL, 4905, '', '', 'text', '0', NULL, NULL, '', NULL, NULL, '{\"shuxing\":[\"tooltip\"],\"guige\":[[]]}', '', NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (4906, 104, '创建时间', 'create_time', 11, 2, 0, 1, 1, '', NULL, 4906, '', '', 'int', '10', NULL, NULL, '', NULL, 'datetime', '{\"shuxing\":[\"tooltip\"],\"guige\":[[]]}', '', NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (4907, 104, '所属菜单', 'menu_id', 1, 2, 1, 1, 1, '', '/^[0-9]*$/', 4907, '', '', 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"shuxing\":[\"tooltip\"],\"guige\":[[]]}', '', NULL, NULL, '值占位文本', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (5206, 3, '弹窗关闭', 'pop_status', 4, 2, 1, 1, 1, '', NULL, 5206, '', '1', 'smallint', '6', NULL, NULL, '[{\"key\":\"点空白\",\"val\":\"1\",\"label_color\":\"success\"},{\"key\":\"点关闭\",\"val\":\"0\",\"label_color\":\"warning\"}]', NULL, NULL, '{\"shuxing\":[\"tooltip\"],\"guige\":[[]],\"tx_tiaojian\":\"\",\"tx_zhi\":\"\"}', '', NULL, '', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (5207, 3, '搜索开关', 'search_status', 4, 2, 1, 1, 1, '', NULL, 5207, '', '1', 'smallint', '6', NULL, NULL, '[{\"key\":\"默认展开\",\"val\":\"1\",\"label_color\":\"success\"},{\"key\":\"默认收起\",\"val\":\"0\",\"label_color\":\"warning\"}]', NULL, NULL, '{\"shuxing\":[\"tooltip\"],\"guige\":[[]],\"tx_tiaojian\":\"\",\"tx_zhi\":\"\"}', '', NULL, '', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (5208, 3, '水印开关', 'watermark_status', 4, 2, 1, 1, 1, '', NULL, 5208, '', '1', 'smallint', '6', NULL, NULL, '[{\"key\":\"默认打开\",\"val\":\"1\"},{\"key\":\"默认关闭\",\"val\":\"0\"}]', NULL, NULL, '{\"shuxing\":[\"tooltip\"],\"guige\":[[]],\"tx_tiaojian\":\"\",\"tx_zhi\":\"\"}', '', NULL, '', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (5243, 6, '唯一登录', 'session_token', 1, 0, 1, 1, 1, '', NULL, 5243, '', '', 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"address_type\":\"1\",\"now_time\":false,\"placeholder\":\"\",\"rand_config\":\"\",\"filetype\":\"\",\"liandong_field\":\"\",\"shuxing\":[\"tooltip\"],\"jdt\":\"changtiao\",\"remote_research_field\":\"\",\"rename_status\":\"\",\"default_tabs_value\":\"\",\"application_id\":\"\",\"crop\":\"\",\"time_search_tempate\":true,\"guige\":[[]],\"maxrows\":4,\"inputRemark\":\"\",\"rangetime_type\":\"date\",\"tx_tiaojian\":\"\",\"tx_zhi\":\"\"}', '', NULL, '', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (5257, 3, 'DSkey', 'deepseekkey', 1, 2, 1, 1, 1, '', NULL, 5257, '', '', 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"address_type\":\"1\",\"now_time\":false,\"placeholder\":\"\\u8bf7\\u8f93\\u5165deepseek-key\",\"rand_config\":\"\",\"filetype\":\"\",\"liandong_field\":\"\",\"shuxing\":[\"tooltip\"],\"jdt\":\"changtiao\",\"remote_research_field\":\"\",\"rename_status\":\"\",\"default_tabs_value\":\"\",\"application_id\":\"\",\"crop\":\"\",\"time_search_tempate\":true,\"guige\":[[]],\"maxrows\":4,\"inputRemark\":\"\",\"rangetime_type\":\"date\",\"tx_tiaojian\":\"\",\"tx_zhi\":\"\"}', '', NULL, '', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (5308, 3, '百度地图KEY', 'bdKey', 1, 2, 1, 1, 1, '', NULL, 5308, '', '', 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"address_type\":\"1\",\"now_time\":false,\"placeholder\":\"\",\"rand_config\":\"\",\"filetype\":\"\",\"liandong_field\":\"\",\"shuxing\":[\"tooltip\"],\"jdt\":\"changtiao\",\"remote_research_field\":\"\",\"rename_status\":\"\",\"default_tabs_value\":\"\",\"application_id\":\"\",\"crop\":\"\",\"time_search_tempate\":true,\"guige\":[[]],\"maxrows\":4,\"inputRemark\":\"\",\"rangetime_type\":\"date\",\"tx_tiaojian\":\"\",\"tx_zhi\":\"\"}', '', NULL, '', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (5309, 3, '高德地图KEY', 'gdKey', 1, 2, 1, 1, 1, '', NULL, 5309, '', '', 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"shuxing\":[\"tooltip\"],\"guige\":[[]],\"tx_tiaojian\":\"\",\"tx_zhi\":\"\"}', '', NULL, '', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, 1);
INSERT INTO `cd_field` VALUES (5310, 3, '腾讯地图KEY', 'txKey', 1, 2, 1, 1, 1, '', NULL, 5310, '', '', 'varchar', '250', NULL, NULL, '', NULL, NULL, '{\"shuxing\":[\"tooltip\"],\"guige\":[[]]}', '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);

-- ----------------------------
-- Table structure for cd_file
-- ----------------------------
DROP TABLE IF EXISTS `cd_file`;
CREATE TABLE `cd_file`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `filepath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图片路径',
  `hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件hash值',
  `create_time` int NULL DEFAULT NULL COMMENT '创建时间',
  `disk` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '存储类似',
  `type` tinyint NULL DEFAULT NULL COMMENT '文件类型',
  `use_in` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `only_param` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `hash`(`hash` ASC) USING BTREE,
  INDEX `only_param`(`only_param` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_file
-- ----------------------------

-- ----------------------------
-- Table structure for cd_log
-- ----------------------------
DROP TABLE IF EXISTS `cd_log`;
CREATE TABLE `cd_log`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `application_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '应用名称',
  `username` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '操作用户',
  `url` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '请求url',
  `ip` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ip',
  `useragent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'useragent',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '请求内容',
  `errmsg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '异常信息',
  `create_time` int NULL DEFAULT NULL COMMENT '创建时间',
  `type` smallint NULL DEFAULT NULL COMMENT '类型',
  `times` int NULL DEFAULT NULL COMMENT '日期',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 347 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


-- ----------------------------
-- Table structure for cd_menu
-- ----------------------------
DROP TABLE IF EXISTS `cd_menu`;
CREATE TABLE `cd_menu`  (
  `menu_id` int NOT NULL AUTO_INCREMENT,
  `pid` mediumint NULL DEFAULT 0 COMMENT '父级id',
  `controller_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '模块名称',
  `title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '模块标题',
  `pk` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '主键名',
  `table_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '模块数据库表',
  `create_code` tinyint NULL DEFAULT NULL COMMENT '是否允许生成模块',
  `status` tinyint NULL DEFAULT 1 COMMENT '0隐藏 1显示',
  `sortid` mediumint NULL DEFAULT 0 COMMENT '排序号',
  `create_table` tinyint NULL DEFAULT NULL COMMENT '是否生成数据库表',
  `url` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '组件路径',
  `icon` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'icon字体图标',
  `tab_config` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'tab选项卡菜单配置',
  `app_id` int NULL DEFAULT NULL COMMENT '所属模块',
  `is_post` tinyint NULL DEFAULT NULL COMMENT '是否允许投稿',
  `upload_config_id` smallint NULL DEFAULT NULL COMMENT '上传配置id',
  `connect` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '数据库连接',
  `page_type` tinyint NULL DEFAULT NULL COMMENT '页面类型',
  `home_show` tinyint NULL DEFAULT 0 COMMENT '首页快捷导航显示状态',
  `menu_pic` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '快捷导航的图片',
  `notice` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '列表提示说明',
  `prompt_session` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '提醒区别session字段名',
  `prompt` tinyint NULL DEFAULT 0 COMMENT '显示提醒图标',
  `flow_subtable` tinyint NULL DEFAULT 0 COMMENT '是否为审核流子表 默认0 不是',
  `menu_show` tinyint NULL DEFAULT 1 COMMENT '列表显示 0不显示 1显示',
  `enable_es` tinyint NULL DEFAULT 0 COMMENT '启用es 1=>启用 0 禁用',
  `open_mode` tinyint(1) NOT NULL DEFAULT 1 COMMENT '打开方式 1=iframe0 2=_blank',
  PRIMARY KEY (`menu_id`) USING BTREE,
  INDEX `controller_name`(`controller_name` ASC) USING BTREE,
  INDEX `module_id`(`app_id` ASC) USING BTREE,
  INDEX `pid`(`pid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 181 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_menu
-- ----------------------------
INSERT INTO `cd_menu` VALUES (1, 0, '', '控制台', '', '', 0, 1, 1, 0, '/admin/Index/main.html', 'el-icon-platform-eleme', NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (2, 0, 'Config', '站长配置', NULL, NULL, NULL, 1, 1000, NULL, NULL, 'fas fa-database', NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (3, 2, 'Baseconfig', '基本配置', 'id', 'base_config', 1, 1, 1, 1, NULL, NULL, NULL, 1, NULL, NULL, 'mysql', NULL, 0, NULL, '', NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (4, 2, 'Uploadconfig', '缩略图配置', 'id', 'upload_config', 1, 1, 2, 1, NULL, 'el-icon-camera-solid', NULL, 1, NULL, NULL, 'mysql', 1, 0, NULL, '', NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (5, 0, 'System', '系统管理', '', '', 0, 1, 1001, 0, '', 'fas fa-user-cog', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (6, 5, 'Adminuser', '用户管理', 'user_id', 'admin_user', 1, 1, 6, 1, '', 'el-icon-user-solid', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (7, 5, 'Role', '角色管理', 'role_id', 'role', 1, 1, 7, 1, '', 'el-icon-s-check', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (8, 5, 'Log', '日志管理', 'id', 'log', 1, 1, 8, 1, '', 'el-icon-s-promotion', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (9, 0, 'Tool', '工具管理', '', '', 1, 1, 1002, 0, '', 'dripicons-gear', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (10, 9, 'Application', '应用管理', '', '', 0, 1, 10, 0, '/admin/Sys.Base/applicationList', 'el-icon-s-shop', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (11, 0, 'Menu', '菜单管理', '', '', 1, 1, 9999, 0, '/admin/Sys.Base/menu', '', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (12, 11, 'Action', '方法管理', '', '', 1, 0, 12, 0, '', '', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (13, 11, 'Field', '字段管理', '', '', 0, 0, 13, 0, '', '', NULL, 1, 0, NULL, 'mysql', 1, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (19, 9, 'Secrect', '秘钥管理', '', '', 0, 1, 19, 0, '/admin/Sys.Base/secrect', 'el-icon-s-tools', NULL, 1, 0, NULL, 'mysql', 2, 0, NULL, NULL, NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (104, 11, 'Actionremarks', '版本记录', 'id', 'action_remarks', 1, 0, 104, 1, '', NULL, NULL, 1, 0, 0, 'mysql', 1, 0, '', '', NULL, 0, 0, 1, 0, 1);
INSERT INTO `cd_menu` VALUES (179, 2, 'MyConfig', '系统配置', '', '', 0, 1, 179, 0, '', 'el-icon-cpu', NULL, 1, 0, 0, 'mysql', 1, 0, '', '', NULL, 0, 0, 1, 0, 1);

-- ----------------------------
-- Table structure for cd_role
-- ----------------------------
DROP TABLE IF EXISTS `cd_role`;
CREATE TABLE `cd_role`  (
  `role_id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '分组名称',
  `status` tinyint NULL DEFAULT NULL COMMENT '状态',
  `pid` smallint NULL DEFAULT NULL COMMENT '所属父类',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '描述',
  `access` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '权限节点',
  PRIMARY KEY (`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 60 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_role
-- ----------------------------
INSERT INTO `cd_role` VALUES (1, '超级管理员', 1, 0, '超级管理员', '');

-- ----------------------------
-- Table structure for cd_secrect
-- ----------------------------
DROP TABLE IF EXISTS `cd_secrect`;
CREATE TABLE `cd_secrect`  (
  `secrect_id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `appid` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'appid',
  PRIMARY KEY (`secrect_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_secrect
-- ----------------------------
INSERT INTO `cd_secrect` VALUES (1, 'appid', '', NULL);
INSERT INTO `cd_secrect` VALUES (2, 'secrect', '', NULL);

-- ----------------------------
-- Table structure for cd_upload_config
-- ----------------------------
DROP TABLE IF EXISTS `cd_upload_config`;
CREATE TABLE `cd_upload_config`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '编号',
  `title` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '配置名称',
  `upload_replace` tinyint NULL DEFAULT NULL COMMENT '覆盖同名文件',
  `thumb_status` tinyint NULL DEFAULT NULL COMMENT '缩图开关',
  `thumb_width` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '缩放宽度',
  `thumb_height` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '缩放高度',
  `thumb_type` smallint NULL DEFAULT NULL COMMENT '缩图方式',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '缩略图配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cd_upload_config
-- ----------------------------
INSERT INTO `cd_upload_config` VALUES (1, '默认配置', 1, 1, '600', '400', 1);

SET FOREIGN_KEY_CHECKS = 1;
