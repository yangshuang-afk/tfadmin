// 添加CSS样式
const style = document.createElement('style');
style.textContent = `
/*字段验证部分下移一像素*/
.el-col-4 {
    margin-top: 1px;
}
.el-input {
width:100%;
}
.field-dialog {
    max-width: 100%;
    margin-top: 0 !important;
    height: 90vh;
    display: flex;
    flex-direction: column;
}

.field-dialog .el-dialog__header {
    padding: 15px 20px;
    border-bottom: 1px solid #ebeef5;
    background-color: #f5f7fa;
}

.field-dialog .el-dialog__body {
    flex: 1;
    padding: 20px;
    overflow: hidden;
}

.field-dialog .el-dialog__footer {
    padding: 15px 20px;
    border-top: 1px solid #ebeef5;
    background-color: #f5f7fa;
}

.field-container {
    display: flex;
    height: 100%;
    min-height: 600px;
}

.field-type-sidebar {
    width: 220px;
    height: 100%;
    border-right: 1px solid #ebeef5;
    background-color: #f8f9fa;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}

.sidebar-header {
    padding: 10px 15px;
    border-bottom: 1px solid #ebeef5;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.type-list-container {
    
    padding: 10px 0;
    height: 570px; /* 固定高度 */
    /* height: 650px;  固定高度 */
    overflow-y: auto; /* 超出高度出现滚动条 */
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f5f5f5;
}

/* 修改字段类型侧边栏的滚动区域 */
.type-radio-group {
    width: 100%;
    height: 100%; /* 确保填充容器 */
    display: flex;
    flex-direction: column; /* 垂直排列 */
}

/* 确保每个类型项的高度固定 */
.type-item {
    padding: 8px 15px;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    height: 40px; /* 固定高度 */
    box-sizing: border-box; /* 防止padding影响高度 */
}

.type-item:hover {
    background-color: #ecf5ff;
}

.type-radio {
    width: 100%;
    margin-right: 0;
    display: flex;
    align-items: center;
}

.type-radio .el-radio__label {
    width: 100%;
    display: flex;
    align-items: center;
    padding-left: 5px;
}

.type-content {
    display: flex;
    align-items: center;
    width: 100%;
}

.type-content i {
    margin-right: 8px;
    font-size: 16px;
    color: #409eff;
    flex-shrink: 0;
}

.type-content span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.field-main-content {
    flex: 1;
    display: flex;
    height: 100%;
    overflow: hidden;
    gap: 15px;
}

.basic-info-section {
    flex: 1; /* 改为flex:1使其与右侧区域等宽 */
    height: 100%;
    display: flex;
    flex-direction: column;
    /* 移除固定padding和边框 */
    padding-right: 0;
    border-right: none;
    overflow: hidden;
}

/* 修改拓展信息区域 */
.extended-info-section {
    flex: 1; /* 改为flex:1使其与左侧区域等宽 */
    height: 100%;
    display: flex;
    flex-direction: column;
    /* 移除固定宽度和左侧padding */
    width: auto;
    padding-left: 0;
    overflow: hidden;
}

.section-header {
    padding: 10px 15px;
    border-bottom: 1px solid #ebeef5;
    margin-bottom: 15px;
}

.section-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.section-content {
    flex: 1;
    overflow-y: auto;
    padding-right: 10px;
}

.option-item {
    margin-bottom: 10px;
}

.option-actions {
    margin-top: 10px;
}

.option-actions .el-button {
    margin-right: 10px;
}

.el-form-item {
    margin-bottom: 18px;
}

/* 修复弹出窗口定位 */
.el-dialog__wrapper.field-dialog {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    width: 100%;
    margin: 0;
    padding: 0;
    z-index: 2001;
}

.el-dialog.is-fullscreen {
    width: 100%;
    height: 100%;
    margin: 0;
    border-radius: 0;
}

/* 覆盖可能存在的布局限制 */
body[data-layout-size=boxed] .el-dialog__wrapper.field-dialog {
    right: 0 !important;
    max-width: none;
}

/* 清除父容器边距 */
.field-dialog .el-dialog__body {
    padding: 0;
}

/* 确保滚动条不影响定位 */
html {
    overflow-x: hidden;
}






/* 添加滚动条样式兼容webkit浏览器 */
.type-list-container::-webkit-scrollbar {
    width: 6px;
}

.type-list-container::-webkit-scrollbar-track {
    background: #f5f5f5;
}

.type-list-container::-webkit-scrollbar-thumb {
    background-color: #c1c1c1;
    border-radius: 3px;
}






/* 选项配置CSS */
.option-item-container {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 10px;
    border: 1px solid #ebeef5;
    border-radius: 4px;
    padding: 5px 5px 0px 5px;
}

.option-item {
    margin-bottom: 5px;
    display: flex;
    align-items: center;
}

.option-item .el-form-item {
    margin-bottom: 0;
}

.option-actions {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.option-actions .el-button {
    margin-right: 0;
}

.jzd-handle {
    cursor: move;
}
.code-tabs .el-tabs__nav {
  margin-left: 120px; /* 与方法名称label相同的左侧距离 */
}


`;
document.head.appendChild(style);
//后台方法添加
//后台方法添加
Vue.component('AdminAdd', {
    template: `
	<el-dialog title="创建操作方法" width="95%" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm">
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="120px">
        	<div class="field-container">
        		<!-- 左侧 - 字段类型选择 -->
				<div class="field-type-sidebar">
					<div class="sidebar-header">
						<h3>方法类型</h3>
					</div>
					<div class="type-list-container">
						<el-radio-group v-model="form.type" @change="selectType" class="type-radio-group">
							<div v-for="(item,i) in action" :key="i" class="type-item">
								<el-radio :label="item.type" class="type-radio">
									<div class="type-content">
										<i :class="item.icon || 'el-icon-menu'"></i>
										<span>{{item.name}}</span>
									</div>
								</el-radio>
							</div>
						</el-radio-group>
					</div>
				</div>
				<!-- 右侧主内容区 -->
				<div class="field-main-content">
					<!-- 中间 - 基本信息 -->
					<div class="basic-info-section">
						<div class="section-header">
							<h3>基本信息</h3>
						</div>
						<el-row>
							<el-col :span="12">
								<el-form-item label="方法名称" prop="name">
									<el-input v-model="form.name" clearable placeholder="方法中文名称"  />
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="英文名称" prop="action_name">
									<el-input v-model="form.action_name" clearable placeholder="方法英文名称"/>
								</el-form-item>
							</el-col>
						</el-row>
						
						<el-row v-if="button">
							<el-form-item label="按钮图标" prop="icon">
								<el-input v-model="form.icon" placeholder="点击选择图标" clearable>
									<el-button type="success" slot="append" icon="el-icon-thumb"  @click="iconDialogStatus = true">请选择</el-button>
								</el-input>
							</el-form-item>
						</el-row>
						
						
						<el-row v-if="form.type == 11">
							<el-form-item label="导出方式" prop="form.other_config.export_type">
								<el-select style="width:100%" :size="size" v-model="form.other_config.export_type" clearable  filterable placeholder="请选择导出方式">
									<el-option key="1" label="客户端导出" value="client"></el-option>
									<el-option key="2" label="服务端导出" value="server"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 15 || form.type == 16">
							<el-col :span="24">
								<el-form-item label="跳转url" prop="jump">
									<el-input v-model="form.jump" clearable placeholder="跳转地址 如 /admin/User/index"  />
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 15 || form.type == 16 || form.type == 55">
							<el-form-item label="url参数字段" prop="fields">
								<el-checkbox-group v-model="form.fields">
									<el-checkbox v-for="item in jump_fields" :label="item.field" :key="item.field">{{item.title}}</el-checkbox>
								</el-checkbox-group>
							</el-form-item>
						</el-row>
						
                        <!-- 审批流 -->
                        <el-row v-if="form.type == 57">
                                <el-col :span="22">
                                    <el-form-item prop="flow_join" label="设置流程">
                                        <el-row>
                                            <el-col :span="5">
                                                <el-form-item label="关联字段"
                                                        prop="flow_join"
                                                        class="no-label-width"
                                                >
                                                    <el-select
                                                            style="width:100%"
                                                            v-model="form.other_config.flow_join"
                                                            filterable
                                                            clearable
                                                            placeholder="选择关联字段"
                                                    >
                                                        <el-option v-for="(vo,i) in jump_fields"
                                                            :key="i"
                                                            :label="vo.title"
                                                            :value="vo.field"
                                                        ></el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="6" style="margin-left: 15px">
                                                <el-form-item label="关联流程"
                                                        prop="flow_table"
                                                        class="no-label-width"
                                                >
                                                    <el-select
                                                            style="width:100%"
                                                            v-model="form.other_config.flow_table"
                                                            @change="onFlowTableChange"
                                                            filterable
                                                            clearable
                                                            placeholder="选择关联流程表"
                                                    >
                                                        <el-option v-for="(vo,i) in flow_tablelist"
                                                            :key="i"
                                                            :label="vo.title"
                                                            :value="vo.menu_id"
                                                        ></el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="5" style="margin-left: 15px">
                                                <el-form-item label="流程字段"
                                                        prop="flow_filed"
                                                        class="no-label-width"
                                                >
                                                    <el-select
                                                            style="width:100%"
                                                            v-model="form.other_config.flow_filed"
                                                            :disabled="true"
                                                            filterable
                                                            placeholder="选择关联流程对应字段"
                                                   >
                                                        <el-option v-for="(vo,i) in table_fields"
                                                                :key="i"
                                                                :value="vo.Field"
                                                                :label="vo.Comment"
                                                                >
                                                        </el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="5" style="margin-left: 15px">
                                                <el-form-item label="关联组"
                                                        prop="flow_filed_label"
                                                        class="no-label-width"
                                                >
                                                    <el-select
                                                            @focus="getFlowTableGroup(form.other_config.flow_table)"
                                                            style="width:100%"
                                                            v-model="form.other_config.flow_group_field"
                                                            filterable
                                                            clearable
                                                            placeholder="选择关联流程组"
                                                   >
                                                        <el-option
                                                            v-for="(vo,i) in group_fields"
                                                            :key="i"
                                                            :value="vo.val"
                                                            :label="vo.key">
                                                        </el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                    </el-form-item>
                                    <div style="margin-left: 52px;color: red;font-size: 15px;">关联字段选择 需与关联流程中的设置字段对应  如: 部门管理审批流  中选择部门id 关联字段选择对应的部门id</div>
                                </el-col>
                            </el-row>
                            
						<!-- 审批组 -->
                        <el-row v-if="form.type == 60">
                            <el-form-item label="关联字段" prop="flow_group">
                                <el-radio v-model="form.other_config.flow_group" v-for="item in jump_fields" :label="item.field" :key="item.field">{{item.title}}</el-radio>
                            </el-form-item>
                        </el-row>
                        
						<el-row v-if="dialog">
							<el-form-item :label="form.type ==5 ? '显示字段' : '操作字段'" prop="fields">
								<el-checkbox-group v-model="form.fields">
									<el-checkbox v-for="item in post_fields" :label="item.field" :key="item.field">{{item.title}}</el-checkbox>
								</el-checkbox-group>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 10">
							<el-col :span="24">
								<el-form-item label="样例excel" prop="excel">
									<Upload v-if="show" size="small" file_type="file"  rename="2"   :file.sync="form.other_config.excel"></Upload>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 7">
							<el-form-item label="状态值" prop="status_val">
								<el-input v-model="form.status_val" placeholder="状态值"/>
							</el-form-item>
						</el-row>
						
						
						<!-- 新增超级页面表单开始 -->
        <el-row v-if="form.type == 55">
            <el-col :span="24">
                <el-form-item label="">
                    <div style="font-size: 12px; color: #999; margin-top: 5px;">
                        <p><strong>URL参数选择逻辑：</strong></p>
                        <p>1. <strong>未选择任何字段</strong>：按钮始终可点击（如"添加"操作）</p>
                        <p>2. <strong>仅选择主键字段</strong>：允许多选操作（如"删除"）</p>
                        <p>3. <strong>选择多个字段</strong>：仅允许单选操作（如"修改"）</p>
                    </div>
                </el-form-item>
            </el-col>
        </el-row>

      <el-row v-if="form.type == 55">
        <el-col :span="24">
          <el-tabs v-model="codeTabActive" class="code-tabs">
            <el-tab-pane label="前端代码" name="frontend">
              <el-form-item style="margin-left: 0px;">
                <el-input
                  v-model="form.q_template"
                  type="textarea"
                  :autosize="{minRows: 10}"
                  placeholder="输入HTML模板代码">
                </el-input>
              </el-form-item>
            </el-tab-pane>
            <el-tab-pane label="后端代码" name="backend">
              <el-form-item style="margin-left: 0px;">
                <el-input
                  v-model="form.h_php"
                  type="textarea"
                  :autosize="{minRows: 10}"
                  placeholder="输入后端处理代码">
                </el-input>
              </el-form-item>
            </el-tab-pane>
          </el-tabs>
        </el-col>
      </el-row>
						<!-- 新增超级页面表单结束 -->
						
						<el-row v-if="form.type == 5">
							<el-form-item label="是否打印" prop="printer_status">
								<el-select @change="selectTreeLoadType" style="width:100%" v-model="form.other_config.printer_status" :size="size" clearable filterable placeholder="是否打印详情页数据">
									<el-option key="1" label="否" :value="2"></el-option>
									<el-option key="2" label="是" :value="1"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 1">
							<el-col :span="12">
								<el-form-item label="分页大小" prop="pagesize">
									<el-select v-model="form.pagesize" placeholder="请选择">
										<el-option key="1" label="10条每页" value="10"></el-option>
										<el-option key="2" label="20条每页" value="20"></el-option>
										<el-option key="3" label="50条每页" value="50"></el-option>
										<el-option key="4" label="100条每页" value="100"></el-option>
										<el-option key="5" label="200条每页" value="200"></el-option>
										<el-option key="6" label="1000条每页" value="1000"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="排序" prop="orderby">
									<el-input v-model="form.orderby" placeholder="如 id desc"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 1">
							<el-col :span="24">
								<el-form-item label="树table配置" prop="tree_config">
									<el-input v-model="form.tree_config" placeholder="父类字段名"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.tree_config">
							<el-form-item label="子节点加载方式" prop="tree_show">
								<el-select style="width:100%" v-model="form.other_config.tree_show" :size="size" clearable filterable placeholder="树列表是否收缩">
									<el-option key="1" label="收缩" :value="2"></el-option>
									<el-option key="2" label="展开" :value="1"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.tree_config">
							<el-form-item label="树收缩" prop="tree_show">
								<el-select style="width:100%" v-model="form.other_config.tree_show" :size="size" clearable filterable placeholder="树列表是否收缩">
									<el-option key="1" label="收缩" :value="2"></el-option>
									<el-option key="2" label="展开" :value="1"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 1">
							<el-col :span="24">
								<el-form-item label="操作栏样式" prop="list_button_style">
									<el-select style="width:100%" v-model="form.other_config.list_button_style" :size="size" clearable filterable placeholder="请选择操作样式">
										<el-option key="1" label="按钮形式" :value="1"></el-option>
										<el-option key="2" label="文字形式" :value="2"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 1">
							<el-col :span="8">
								<el-form-item label="选中方式" prop="select_type">
									<el-radio v-model="form.select_type" :label="1">多选</el-radio>
									<el-radio v-model="form.select_type" :label="2">单选</el-radio>
								</el-form-item>
							</el-col>
							<el-col :span="8">
								<el-form-item label="表格高度" prop="table_height">
									<el-input v-model="form.table_height" placeholder="如设置则表头固定"/>
								</el-form-item>
							</el-col>
							<el-col :span="8">
								<el-form-item label="按钮样式" prop="table_height">
									<el-input v-model="form.table_height" placeholder="如设置则表头固定"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 1 || form.type == 11">
							<el-form-item label="过滤条件" prop="list_filter">
								<el-row v-for="(item,i) in form.list_filter" :key="i">
									<el-col :span="8">
										<el-form-item style="margin-bottom:3px !important">
											<el-input v-model="item.searchField" placeholder="请设置字段"/>
										</el-form-item>
									</el-col>
									<el-col :span="6">
										<el-form-item style="margin-bottom:3px !important">
											<el-select style="width:100%;position:relative; left:10px" v-model="item.searchCondition" filterable placeholder="请选择条件">
												<el-option key="0" value="=">=</el-option>
												<el-option key="1" value="<>"><></el-option>
												<el-option key="2" value="in">in</el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :span="6">
										<el-form-item style="margin-bottom:3px !important">
											<el-autocomplete :fetch-suggestions="querySearch" style="position:relative; left:20px" v-model="item.serachVal" placeholder="值"/>
										</el-form-item>
									</el-col>
									<el-col :span="4">
										<el-button type="danger" size="mini" style="position:relative;left:35px"  icon="el-icon-close" @click="deleteItem('list_filter',i)"></el-button>
									</el-col>
								</el-row>
								<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('list_filter')">追加</el-button>
								<el-button v-if="form.list_filter.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('list_filter')">清空</el-button>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 2 || form.type ==3 || form.type == 14">
							<el-form-item label="选项卡配置" prop="tab_config">
								<el-row v-for="(item,i) in form.tab_config" :key="i">
									<el-col :span="8">
										<el-form-item style="margin-bottom:3px !important">
											<el-input clearable v-model="item.tab_name" placeholder="选项卡名称"/>
										</el-form-item>
									</el-col>
									<el-col :span="12">
										<el-form-item style="margin-bottom:3px !important">
											<el-select style="width:100%;position:relative; left:10px" clearable v-model="item.tab_fields" multiple collapse-tags placeholder="包含字段">
												<el-option v-for="(vo,i) in tab_fields" :key="i" :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :span="2">
										<el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem('tab_config',i)"></el-button>
									</el-col>
								</el-row>
								<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('tab_config')">追加</el-button>
								<el-button v-if="form.tab_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('tab_config')">清空</el-button>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type==1 && dbtype !== 'mongo'">
							<el-form-item label="侧栏列表sql" prop="left_tree_sql">
								<el-input v-model="form.left_tree_sql" placeholder="通过sql语句生成table侧栏列表"  />
							</el-form-item>
						</el-row>
						<el-row v-if="form.left_tree_sql">
							<el-form-item label="树收缩" prop="left_tree_show">
								<el-select style="width:100%" v-model="form.other_config.left_tree_show" :size="size" clearable filterable placeholder="侧栏树列表是否收缩">
									<el-option key="1" label="收缩" :value="2"></el-option>
									<el-option key="2" label="展开" :value="1"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="[1,2,3,4,5,7,8,9,10,11,12,17,18,19,20,22].includes(form.type)">
							<el-form-item label="方法钩子" prop="hook">
                                    <el-row style="margin: 5px 0;">
                                        <div style="display: inline-block; width: 100px;">
                                            <el-checkbox key="0" label="beforHook" @change="setBeforHook">前置钩子</el-checkbox>
                                        </div>
                                        <el-input style="width:300px;"  v-model="form.other_config.befor_hook" placeholder="方法前置钩子路径"  />
									</el-row>
									
                                    <el-row style="margin: 5px 0;">
                                        <div  style="display: inline-block; width: 100px;">前置钩子备注</div>
                                        <el-input style="width:300px;"  v-model="form.other_config.befor_hook_remarks" placeholder="方法前置钩子备注"  />
									</el-row>
									
                                    <el-row style="margin: 5px 0;">
                                        <div style="display: inline-block; width: 100px;">
                                            <el-checkbox key="1" label="afterHook" @change="setAfterHook">后置钩子</el-checkbox>
                                        </div>
                                        <el-input style="width:300px;" v-model="form.other_config.after_hook" placeholder="方法后置钩子路径"  />
									</el-row>
									
                                    <el-row style="margin: 5px 0;">
                                        <div  style="display: inline-block; width: 100px;">后置钩子备注</div>
                                        <el-input style="width:300px;"  v-model="form.other_config.after_hook_remarks" placeholder="方法后置钩子备注"  />
									</el-row>
							</el-form-item>
						</el-row>
						<el-row v-if="[53].includes(form.type)">
							<el-form-item label="匹配session" prop="sql">
								<el-input v-model="form.sql" type="textarea" placeholder="填写匹配【操作字段】的session名称"  />
							</el-form-item>
						</el-row>
					</div>
				</div>
				<!-- 右侧 - 拓展信息 -->
				<div class="extended-info-section" v-if="[1,2,3,4,5,11].includes(form.type) && dbtype !== 'mongo'" style="padding-top:10px">
					<div class="section-header">
						<h3>拓展信息</h3>
					</div>
					<div class="section-content">
						<el-form-item label="关联模型" prop="with_join">
							<el-row :gutter="2" v-for="(item,i) in form.with_join" :key="i">
								<el-col style="margin-left:0px" :span="5">
									<el-form-item style="margin-bottom:3px !important"  prop="fk">
										<el-select style="width:100%" v-model="item.fk" filterable placeholder="主表外键">
											<el-option v-for="(vo,i) in model_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="5">
									<el-form-item style="margin-bottom:3px !important" prop="relative_table">
										<el-select style="width:100%" v-model="item.relative_table" filterable placeholder="模型">
											<el-option v-for="(vo,i) in tableList" :key="i" :value="vo.controller_name">{{vo.controller_name}}</el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="5">
									<el-form-item style="margin-bottom:3px !important" prop="pk">
										<el-select @focus="getTableFields(i)" style="width:100%" v-model="item.pk" filterable placeholder="关联键">
											<el-option v-for="(vo,i) in table_fields" :key="i"  :value="vo.Field">{{vo.Field}}({{vo.Comment}})</el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="7">
									 <el-form-item style="margin-bottom:3px !important" prop="fields">
										<el-select @focus="getTableFields(i)" style="width:100%" multiple collapse-tags v-model="item.fields" filterable :placeholder="form.type == 1?'关联表查询字段':'操作字段'">
											<el-option v-for="(vo,i) in table_fields" :key="i"  :value="vo.Field">{{vo.Field}}({{vo.Comment}})</el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="1">
									<el-button type="danger" size="mini" style="position:relative;left:5px"  icon="el-icon-close" @click="deleteItem('with_join',i)"></el-button>
								</el-col>
							</el-row>
							<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('with_join')">追加</el-button>
							<el-button v-if="form.with_join.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('with_join')">清空</el-button>
						</el-form-item>
						<el-row v-if="[1,5,11].includes(form.type) && dbtype !== 'mongo'">
							<el-form-item label="table列表sql" prop="sql">
								<el-input v-model="form.sql" type="textarea" placeholder="通过sql语句生成table列表"  />
							</el-form-item>
						</el-row>
					</div>
				</div>
			</div>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button :size="size" :loading="loading" type="primary" @click="submit" >
                <span v-if="!loading">确 定</span>
                <span v-else>提 交 中...</span>
            </el-button>
            <el-button :size="size" @click="closeForm">取 消</el-button>
        </div>
        <Icon :iconshow.sync="iconDialogStatus" :icon.sync="form.icon" size="small"></Icon>
    </el-dialog>
	`
    ,
    props: {
        show: {
            type: Boolean,
            default: false
        },
        size: {
            type: String,
        },
        menuid: {
            type: String,
        },
        action: {
            type: Array,
        }
    },
    data() {
        return {
            form: {
                server_create_status: 1,
                vue_create_status: 1,
                button_color: 'primary',
                select_type: 1,
                fields: [],
                list_filter: [],
                tab_config: [],
                with_join: [],
                type: '',
                icon: '',
                name: '',
                action_name: '',
                dialog_size: '85%',
                pagesize: '20',
                sql: '',
                menu_id: this.menuid,
                jump: '',
                status_val: '',
                orderby: '',
                tree_config: '',
                table_height: '',
                // 新增超级页面数据结构开始
                q_template: '<div class="super-page">\n  <h1>自定义页面</h1>\n</div>', // 前端代码
                h_php: 'public function ygluntan() {\n   if (!$this->request->isPost()){\n     return view(\'ygluntan\');\n   }\n}\n', // 后端PHP代码
                codeTabActive: 'frontend', // 新增选项卡激活状态
                // 新增超级页面数据结构结束
                
                other_config: {
                    export_type: '',
                    hook: [],
                    excel: '',
                    left_tree_show: '',
                    tree_show: 1,
                    after_hook: '',
                    befor_hook: '',
                    printer_status: 2,
                    list_button_style: 1,
                }
            },
            iconDialogStatus: false,
            post_fields: [],
            jump_fields: [],
            activeName: 'first',
            tableList: [],
            dialog: true,
            button: true,
            loading: false,
            ischeck_fields: [],
            activeName: '基本信息',
            table_fields: [],
            tab_fields: [],
            model_fields: [],
            group_fields: [], // 审批组
            flow_tablelist: [], // 审批表
            dbtype: '',
            restaurants: [{'value': 'null'}, {'value': 'not null'}],
            rules: {
                name: [{
                        required: true,
                        message: '方法中文名不能为空',
                    trigger: 'blur'
                }],
                action_name: [{
                    required: true,
                    message: '方法英文名不能为空',
                    trigger: 'blur'
                }, {
                    pattern: /^[a-zA-Z0-9_]+$/,
                    message: '仅允许输入字母、数字和下划线',
                    trigger: 'blur'
                }],
                type: [{
                    required: true,
                    message: '方法类型不能为空',
                    trigger: 'blur'
                }],
            },
        }
    },
    methods: {
        submit() {
            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    
                    // 新增超级页面安全验证开始
                    if (this.form.type == 55 && !this.validateSuperPage()) {
                        this.loading = false
                        return
                    }
                    // 新增超级页面安全验证结束
                    
                    axios.post(base_url + '/Sys.Base/createAction', this.form).then(res => {
                        if (res.data.status == 200) {
                            this.$message({message: '操作成功', type: 'success'})
                            this.$emit('refesh_list')
                            this.closeForm()
                        } else {
                            this.loading = false
                            this.$message.error(res.data.msg)
                        }
                    }).catch(() => {
                        this.loading = false
                    })
                }
            })
        },
        
        // 新增超级页面安全验证方法开始
        validateSuperPage() {
            // const forbidden = ['eval', 'Function', 'exec', 'system', 'shell_exec']
            const forbidden = ['eval', 'Function', 'system']
            
            // 检查前端代码
            if (this.form.q_template) {
                for (let word of forbidden) {
                    if (this.form.q_template.includes(word)) {
                        this.$message.error(`前端代码中检测到危险代码: ${word}`)
                        return false
                    }
                }
            }
            
            // 检查后端代码
            if (this.form.h_php) {
                for (let word of forbidden) {
                    if (this.form.h_php.includes(word)) {
                        this.$message.error(`后端代码中检测到危险代码: ${word}`)
                        return false
                    }
                }
            }
            
            return true
        },
        // 新增超级页面安全验证方法结束
        
        open() {
            axios.post(base_url + '/Sys.Base/getPostField', {menu_id: this.menuid}).then(res => {
                this.post_fields = res.data.data
                this.jump_fields = res.data.jump_field
                this.tab_fields = res.data.tab_fields
                this.model_fields = res.data.model_fields
                this.tableList = res.data.tableList
                this.dbtype = res.data.dbtype
                this.codeTabActive = 'frontend'; // 设置默认激活的选项卡
                this.flow_tablelist = res.data?.flowTableList || []; // 审批表数据
                this.group_fields = res.data?.groupFields || []; // 审批组数据
            })
        },
        selectType(val) {
            if (val !== 1) {
                this.form.list_filter = []
                this.form.with_join = []
            }
            if (val !== 3 || val !== 4) {
                this.form.tab_config = []
            }
            if (val !== 7) {
                this.form.dialog_size = ''
            }
            this.action.forEach(item => {
                if (this.form.type == item.type) {
                    this.dialog = item.dialog
                    this.button = item.button
                    this.form.icon = item.icon
                    this.form.button_color = item.button_color
                    this.form.name = item.name
                    this.form.action_name = item.action_name
                }
            })
            
            // 新增超级页面初始化开始
            if (val == 55 && !this.form.super_page) {
                this.$set(this.form, 'super_page', {
                    frontend: {template: '', script: '', style: ''},
                    backend: {language: 'php', code: ''}
                })
            }
            // 新增超级页面初始化结
        },
        getTableFields(i) {
            axios.post(base_url + '/Sys.Base/getTableFields', {controller_name: this.form.with_join[i].relative_table}).then(res => {
                this.table_fields = res.data.filedList
            })
        },
        
        // 添加新的方法
        onFlowTableChange(menu_id) {
            // 清空之前的选择
            this.form.other_config.flow_filed = ''
            this.table_fields = []
            
            if (menu_id) {
                axios.post(base_url + '/Sys.Base/getFlowTableFields', {menu_id}).then(res => {
                    this.table_fields = res.data.filedList
                    // 如果有字段数据，自动选择第一个字段
                    if (res.data.filedList && res.data.filedList.length > 0) {
                        this.form.other_config.flow_filed = res.data.filedList[0].Field
                    }
                })
            }
        },
        
        // 修改 getFlowTableGroup 方法
        getFlowTableGroup(menu_id) {
            axios.post(base_url + '/Sys.Base/getFlowTableGroup', {menu_id}).then(res => {
                this.group_fields = res.data.group
            })
        },
        closeForm() {
            this.$emit('update:show', false)
            this.loading = false
            this.$nextTick(() => {
                this.$refs['form'].resetFields()
                this.form.dialog_size = ''
                this.form.icon = ''
                this.form.sql = ''
                this.form.other_config.after_hook = ''
                this.form.other_config.befor_hook = ''
            })
        },
        addItem(key) {
            this.form[key].push({})
        },
        deleteItem(key, index) {
            this.form[key].splice(index, 1)
        },
        clearItem(key) {
            this.form[key] = []
        },
        querySearch(queryString, cb) {
            var restaurants = this.restaurants;
            cb(restaurants);
        },
        setBeforHook(val) {
            if (val) {
                axios.post(base_url + '/Sys.Base/getHookPath', {
                    'menu_id': this.menuid,
                    'actionName': this.form.action_name,
                    'type': 1
                }).then(res => {
                    this.form.other_config.befor_hook = res.data.data
                })
            } else {
                this.form.other_config.befor_hook = ''
            }
        },
        setAfterHook(val) {
            if (val) {
                axios.post(base_url + '/Sys.Base/getHookPath', {
                    'menu_id': this.menuid,
                    'actionName': this.form.action_name,
                    'type': 2
                }).then(res => {
                    this.form.other_config.after_hook = res.data.data
                })
            } else {
                this.form.other_config.after_hook = ''
            }
        },
    },
});

//后台方法修改
Vue.component('AdminUpdate', {
    template: `
	<el-dialog title="更新操作方法" width="95%" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm" append-to-body>
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="120px">
        	<div class="field-container">
        		<!-- 左侧 - 字段类型选择 -->
				<div class="field-type-sidebar">
					<div class="sidebar-header">
						<h3>方法类型</h3>
					</div>
					<div class="type-list-container">
						<el-radio-group v-model="form.type" @change="selectType" class="type-radio-group">
							<div v-for="(item,i) in action" :key="i" class="type-item">
								<el-radio :label="item.type" class="type-radio">
									<div class="type-content">
										<i :class="item.icon || 'el-icon-menu'"></i>
										<span>{{item.name}}</span>
									</div>
								</el-radio>
							</div>
						</el-radio-group>
					</div>
				</div>
				<!-- 右侧主内容区 -->
				<div class="field-main-content">
					<!-- 中间 - 基本信息 -->
					<div class="basic-info-section">
						<div class="section-header">
							<h3>基本信息</h3>
						</div>
						<el-row>
							<el-form-item label="方法类型" prop="type">
								<el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
									<el-option v-for="(item,i) in action" :key="i" :label="item.name" :value="item.type"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row>
							<el-col :span="12">
								<el-form-item label="方法名称" prop="name">
									<el-input v-model="form.name" clearable placeholder="方法中文名称"  />
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="英文名称" prop="action_name">
									<el-input v-model="form.action_name" clearable placeholder="方法英文名称"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="button">
							<el-form-item label="按钮图标" prop="icon">
								<el-input v-model="form.icon" placeholder="点击选择图标" clearable>
									<el-button type="success" slot="append" icon="el-icon-thumb"  @click="iconDialogStatus = true">请选择</el-button>
								</el-input>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 11">
							<el-form-item label="导出方式" prop="export_type">
								<el-select style="width:100%" :size="size" v-model="form.other_config.export_type" clearable  filterable placeholder="请选择导出方式">
										<el-option key="1" label="客户端导出" value="client"></el-option>
										<el-option key="2" label="服务端导出" value="server"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 15 || form.type == 16">
							<el-col :span="24">
								<el-form-item label="跳转url" prop="jump">
									<el-input v-model="form.jump" clearable placeholder="跳转地址 如 /admin/User/index"  />
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 15 || form.type == 16 || form.type == 55">
							<el-form-item label="url参数字段" prop="fields">
								<el-checkbox-group v-model="form.fields">
									<el-checkbox v-for="item in jump_fields" :label="item.field" :key="item.field">{{item.title}}</el-checkbox>
								</el-checkbox-group>
							</el-form-item>
						</el-row>
						
                        <!-- 审批流 -->
                        <el-row v-if="form.type == 57">
                                <el-col :span="22">
                                    <el-form-item prop="flow_join" label="设置流程">
                                        <el-row>
                                            <el-col :span="5">
                                                <el-form-item label="关联字段"
                                                        prop="flow_join"
                                                        class="no-label-width"
                                                >
                                                    <el-select
                                                            style="width:100%"
                                                            v-model="form.other_config.flow_join"
                                                            filterable
                                                            clearable
                                                            placeholder="选择关联字段"
                                                    >
                                                        <el-option v-for="(vo,i) in jump_fields"
                                                            :key="i"
                                                            :label="vo.title"
                                                            :value="vo.field"
                                                        ></el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="6" style="margin-left: 15px">
                                                <el-form-item label="关联流程"
                                                        prop="flow_table"
                                                        class="no-label-width"
                                                >
                                                    <el-select
                                                            style="width:100%"
                                                            v-model="form.other_config.flow_table"
                                                            @change="onFlowTableChange"
                                                            filterable
                                                            clearable
                                                            placeholder="选择关联流程表"
                                                    >
                                                        <el-option v-for="(vo,i) in flow_tablelist"
                                                            :key="i"
                                                            :label="vo.title"
                                                            :value="vo.menu_id"
                                                        ></el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="5" style="margin-left: 15px">
                                                <el-form-item label="流程字段"
                                                        prop="flow_filed"
                                                        class="no-label-width"
                                                >
                                                    <el-select
                                                            style="width:100%"
                                                            v-model="form.other_config.flow_filed"
                                                            :disabled="true"
                                                            filterable
                                                            placeholder="选择关联流程对应字段"
                                                   >
                                                        <el-option v-for="(vo,i) in table_fields"
                                                                :key="i"
                                                                :value="vo.Field"
                                                                :label="vo.Comment"
                                                                >
                                                        </el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="5" style="margin-left: 15px">
                                                <el-form-item label="关联组"
                                                        prop="flow_filed_label"
                                                        class="no-label-width"
                                                >
                                                    <el-select
                                                            @focus="getFlowTableGroup(form.other_config.flow_table)"
                                                            style="width:100%"
                                                            v-model="form.other_config.flow_group_field"
                                                            filterable
                                                            clearable
                                                            placeholder="选择关联流程组"
                                                   >
                                                        <el-option
                                                            v-for="(vo,i) in group_fields"
                                                            :key="i"
                                                            :value="vo.val"
                                                            :label="vo.key">
                                                        </el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                    </el-form-item>
                                    <div style="margin-left: 52px;color: red;font-size: 15px;">关联字段选择 需与关联流程中的设置字段对应  如: 部门管理审批流  中选择部门id 关联字段选择对应的部门id</div>
                                </el-col>
                            </el-row>
                            
						<!-- 审批组 -->
                        <el-row v-if="form.type == 60">
                            <el-form-item label="关联字段" prop="flow_group">
                                <el-radio v-model="form.other_config.flow_group" v-for="item in jump_fields" :label="item.field" :key="item.field">{{item.title}}</el-radio>
                            </el-form-item>
                        </el-row>
                        
						<el-row v-if="dialog">
							<el-form-item :label="form.type ==5 ? '显示字段' : '操作字段'" prop="fields">
								<el-checkbox-group v-model="form.fields">
									<el-checkbox v-for="item in post_fields" :label="item.field" :key="item.field">{{item.title}}</el-checkbox>
								</el-checkbox-group>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 10">
							<el-col :span="24">
								<el-form-item label="样例excel" prop="excel">
									<Upload v-if="show" size="small" file_type="file"  rename="2"   :file.sync="form.other_config.excel"></Upload>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 7">
							<el-form-item label="状态值" prop="status_val">
								<el-input v-model="form.status_val" placeholder="状态值"/>
							</el-form-item>
						</el-row>
						
                        <el-row v-if="form.type == 55">
                            <el-col :span="24">
                                <el-form-item label="">
                                    <div style="font-size: 12px; color: #999; margin-top: 5px;">
                                        <p><strong>URL参数选择逻辑：</strong></p>
                                        <p>1. <strong>未选择任何字段</strong>：按钮始终可点击（如"添加"操作）</p>
                                        <p>2. <strong>仅选择主键字段</strong>：允许多选操作（如"删除"）</p>
                                        <p>3. <strong>选择多个字段</strong>：仅允许单选操作（如"修改"）</p>
                                    </div>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        
                        <el-row v-if="form.type == 55">
                            <el-col :span="24">
                                <el-tabs v-model="codeTabActive" class="code-tabs">
                                    <el-tab-pane label="前端代码" name="frontend">
                                        <el-form-item style="margin-left: 0px;">
                                            <el-input
                                                v-model="form.q_template"
                                                type="textarea"
                                                :autosize="{minRows: 10}"
                                                placeholder="输入HTML模板代码">
                                            </el-input>
                                        </el-form-item>
                                    </el-tab-pane>
                                    <el-tab-pane label="后端代码" name="backend">
                                        <el-form-item style="margin-left: 0px;">
                                            <el-input
                                                v-model="form.h_php"
                                                type="textarea"
                                                :autosize="{minRows: 10}"
                                                placeholder="输入后端处理代码">
                                            </el-input>
                                        </el-form-item>
                                    </el-tab-pane>
                                </el-tabs>
                            </el-col>
                        </el-row>

						<el-row v-if="form.type == 5">
							<el-form-item label="是否打印" prop="printer_status">
								<el-select @change="selectTreeLoadType" style="width:100%" v-model="form.other_config.printer_status" :size="size" clearable filterable placeholder="是否打印详情页数据">
									<el-option key="1" label="否" :value="2"></el-option>
									<el-option key="2" label="是" :value="1"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 1">
							<el-col :span="12">
								<el-form-item label="分页大小" prop="pagesize">
									<el-select v-model="form.pagesize" filterable placeholder="请选择">
										<el-option key="1" label="10条每页" value="10"></el-option>
										<el-option key="2" label="20条每页" value="20"></el-option>
										<el-option key="3" label="50条每页" value="50"></el-option>
										<el-option key="4" label="100条每页" value="100"></el-option>
										<el-option key="5" label="200条每页" value="200"></el-option>
										<el-option key="6" label="1000条每页" value="1000"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="排序方式" prop="orderby">
									<el-input v-model="form.orderby" placeholder="如 id desc"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 1">
							<el-col :span="24">
								<el-form-item label="树table配置" prop="tree_config">
									<el-input v-model="form.tree_config" @input="setTreeLoadType" placeholder="父类ID 如 pid"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.tree_config">
							<el-form-item label="节点加载方式" prop="tree_load_type">
								<el-select @change="selectTreeLoadType" style="width:100%" v-model="form.other_config.tree_load_type" :size="size" clearable filterable placeholder="节点加载方式，数据多建议懒加载，数据少建议全部加载">
									<el-option key="1" label="懒加载" :value="2"></el-option>
									<el-option key="2" label="全部加载" :value="1"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.other_config.tree_load_type == 1">
							<el-form-item label="树收缩" prop="tree_show">
								<el-select style="width:100%" v-model="form.other_config.tree_show" :size="size" clearable filterable placeholder="树列表是否收缩">
									<el-option key="1" label="收缩" :value="2"></el-option>
									<el-option key="2" label="展开" :value="1"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 1">
							<el-col :span="24">
								<el-form-item label="操作栏样式" prop="list_button_style">
									<el-select style="width:100%" v-model="form.other_config.list_button_style" :size="size" clearable filterable placeholder="请选择操作样式">
										<el-option key="1" label="按钮形式" :value="1"></el-option>
										<el-option key="2" label="文字形式" :value="2"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 1">
							<el-col :span="12">
								<el-form-item label="选中方式" prop="select_type">
									<el-radio v-model="form.select_type" :label="1">多选</el-radio>
									<el-radio v-model="form.select_type" :label="2">单选</el-radio>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="表格高度" prop="table_height">
									<el-input v-model="form.table_height" placeholder="如设置则表头固定"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 1 || form.type == 11">
							<el-form-item label="过滤条件" prop="list_filter">
								<el-row v-for="(item,i) in form.list_filter" :key="i">
									<el-col :span="8">
										<el-form-item style="margin-bottom:3px !important">
											<el-input v-model="item.searchField" placeholder="请设置字段"/>
										</el-form-item>
									</el-col>
									<el-col :span="6">
										<el-form-item style="margin-bottom:3px !important">
											<el-select style="width:100%;position:relative; left:10px" v-model="item.searchCondition" filterable placeholder="请选择条件">
												<el-option key="0" value="=">=</el-option>
												<el-option key="1" value="<>"><></el-option>
												<el-option key="2" value="in">in</el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :span="6">
										<el-form-item style="margin-bottom:3px !important">
											<el-autocomplete :fetch-suggestions="querySearch" style="position:relative; left:20px" v-model="item.serachVal" placeholder="值"/>
										</el-form-item>
									</el-col>
									<el-col :span="4">
										<el-button type="danger" size="mini" style="position:relative;left:35px"  icon="el-icon-close" @click="deleteItem('list_filter',i)"></el-button>
									</el-col>
								</el-row>
								<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('list_filter')">追加</el-button>
								<el-button v-if="form.list_filter.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('list_filter')">清空</el-button>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 2 || form.type ==3 || form.type == 14">
							<el-form-item label="选项卡配置" prop="tab_config">
								<el-row v-for="(item,i) in form.tab_config" :key="i">
									<el-col :span="8">
										<el-form-item style="margin-bottom:3px !important">
											<el-input  v-model="item.tab_name" clearable placeholder="选项卡名称"/>
										</el-form-item>
									</el-col>
									<el-col :span="12">
										<el-form-item style="margin-bottom:3px !important">
											<el-select style="width:100%;position:relative; left:10px" clearable v-model="item.tab_fields" multiple collapse-tags placeholder="包含字段">
												<el-option v-for="(vo,i) in tab_fields" :key="i" :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :span="2">
										<el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem('tab_config',i)"></el-button>
									</el-col>
								</el-row>
								<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('tab_config')">追加</el-button>
								<el-button v-if="form.tab_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('tab_config')">清空</el-button>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type==1 && dbtype !== 'mongo'">
							<el-form-item label="侧栏列表sql" prop="left_tree_sql">
								<el-input v-model="form.left_tree_sql" placeholder="通过sql语句生成table侧栏列表"  />
							</el-form-item>
						</el-row>
						<el-row v-if="form.left_tree_sql">
							<el-form-item label="树收缩" prop="left_tree_show">
								<el-select style="width:100%" v-model="form.other_config.left_tree_show" :size="size" clearable filterable placeholder="侧栏树列表是否收缩">
									<el-option key="1" label="收缩" :value="2"></el-option>
									<el-option key="2" label="展开" :value="1"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						
						<el-row v-if="form.group_button_status">
							<el-form-item label="表头按钮显示条件" prop="show_list_button">
								<el-input v-model="form.other_config.show_group_button" placeholder="status != 1 或 pay_type == 1 && scope.row.status == 1 或wl_date > Date.now()"  />
                                <div style="font-size: 12px; color: #999; margin-top: 5px;">
                                    <p><strong>表头按钮 多依赖于页面地址urlobj传参</strong></p>
                                    <p><strong>填写示例：</strong></p>
                                    <p><code>urlobj?.member_id</code> → 当存在member_id时显示</p>
                                    <p><strong>注意：</strong>表达式需返回布尔值（true/false），支持JS语法。</p>
                                </div>
							</el-form-item>
						</el-row>
						
						<el-row v-if="form.list_button_status">
							<el-form-item label="按钮显示条件" prop="show_list_button">
								<el-input v-model="form.other_config.show_list_button" placeholder="status != 1 或 pay_type == 1 && scope.row.status == 1 或wl_date > Date.now()"  />
								        <div style="font-size: 12px; color: #999; margin-top: 5px;">
                                            <p><strong>填写示例：</strong></p>
                                            <p>1. <code>status != 1</code> → 仅当状态不等于1时显示</p>
                                            <p>2. <code>pay_type == 1 && scope.row.status == 1</code> → 支付类型为1且状态为1时显示</p>
                                            <p>3. <code>wl_date == scope.row.wl_date && new Date(parseInt(scope.row.wl_date) * 1000).toDateString() === new Date().toDateString()</code> → 仅当天日志可修改</p>
                                            <p>4. <code>kq_riqi == scope.row.kq_riqi && (new Date().getTime() - new Date(parseInt(scope.row.kq_riqi) * 1000).getTime()) <= 86400000</code> → 仅显示24小时内的日志</p>
                                            <p>5. <code>fp_zhuangtai == 1 && (scope.row.yuangong_id == '{:session('admin.yg_xingming')}' || '1' == '{:session('admin.role_id')}' || {if in_array('/admin/Gongzi/caiwucaozuo.html',session('admin.access'))}true{else /}false{/if})</code> → 有财务操作权限的显示</p>
                                            <p><strong>注意：</strong>表达式需返回布尔值（true/false），支持JS语法。</p>
                                        </div>
							</el-form-item>
						</el-row>
						<el-row v-if="[1,2,3,4,5,7,8,9,10,11,12,17,18,19,20,22].includes(form.type)">
							<el-form-item label="方法钩子" prop="hook">
								<el-row style="margin: 5px 0;">
								    <div style="display: inline-block; width: 100px;">
                                        <el-checkbox key="0" label="beforHook" v-model="beforHookChecked" @change="setBeforHook">前置钩子 </el-checkbox>
                                    </div>
                                    <el-input style="width:300px;"  v-model="form.other_config.befor_hook" placeholder="方法前置钩子路径"  />
								</el-row>
								
                                <el-row style="margin: 5px 0;">
                                    <div  style="display: inline-block; width: 100px;">前置钩子备注</div>
                                    <el-input style="width:300px;"  v-model="form.other_config.befor_hook_remarks" placeholder="方法前置钩子备注"  />
                                </el-row>
									
								<el-row style="margin: 5px 0;">
								    <div style="display: inline-block; width: 100px;">
                                        <el-checkbox key="1" label="afterHook" v-model="afterHookChecked" @change="setAfterHook">后置钩子 </el-checkbox>
                                    </div>
                                    <el-input style="width:300px;" v-model="form.other_config.after_hook" placeholder="方法后置钩子路径"  />
								</el-row>
								
                                <el-row style="margin: 5px 0;">
                                    <div  style="display: inline-block; width: 100px;">后置钩子备注</div>
                                    <el-input style="width:300px;"  v-model="form.other_config.after_hook_remarks" placeholder="方法后置钩子备注"  />
                                </el-row>
							</el-form-item>
						</el-row>
						<el-row v-if="[53].includes(form.type)">
							<el-form-item label="匹配session" prop="sql">
								<el-input v-model="form.sql" placeholder="填写匹配【操作字段】的session名称"  />
							</el-form-item>
						</el-row>
					</div>
				</div>
				<!-- 右侧 - 拓展信息 -->
				<div class="extended-info-section" v-if="[1,2,3,4,5,11].includes(form.type) && dbtype !== 'mongo'" style="padding-top:10px">
					<div class="section-header">
						<h3>拓展信息</h3>
					</div>
					<div class="section-content">
						<el-form-item label="关联模型" prop="with_join">
							<el-row :gutter="2" v-for="(item,i) in form.with_join" :key="i">
								<el-col style="margin-left:0px" :span="5">
									<el-form-item style="margin-bottom:3px !important"  prop="fk">
										<el-select style="width:100%" v-model="item.fk" filterable placeholder="主表外键">
											<el-option v-for="(vo,i) in model_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="5">
									<el-form-item style="margin-bottom:3px !important" prop="relative_table">
										<el-select style="width:100%" v-model="item.relative_table" filterable placeholder="模型">
											<el-option v-for="(vo,i) in tableList" :key="i" :value="vo.controller_name">{{vo.controller_name}}</el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="5">
									<el-form-item style="margin-bottom:3px !important" prop="pk">
										<el-select @focus="getTableFields(i)" style="width:100%" v-model="item.pk" filterable placeholder="关联键">
											<el-option v-for="(vo,i) in table_fields" :key="i"  :value="vo.Field">{{vo.Field}}({{vo.Comment}})</el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="7">
									 <el-form-item style="margin-bottom:3px !important" prop="fields">
										<el-select @focus="getTableFields(i)" style="width:100%" multiple collapse-tags v-model="item.fields" filterable :placeholder="form.type == 1?'关联表查询字段':'操作字段'">
											<el-option v-for="(vo,i) in table_fields" :key="i"  :value="vo.Field">{{vo.Field}}({{vo.Comment}})</el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="1">
									<el-button type="danger" size="mini" style="position:relative;left:5px"  icon="el-icon-close" @click="deleteItem('with_join',i)"></el-button>
								</el-col>
							</el-row>
							<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('with_join')">追加</el-button>
							<el-button v-if="form.with_join.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('with_join')">清空</el-button>
						</el-form-item>
						<el-row v-if="[1,5,11].includes(form.type) && dbtype !== 'mongo'">
							<el-form-item label="table列表sql" prop="sql">
								<el-input v-model="form.sql" type="textarea" placeholder="通过sql语句生成table列表"  />
							</el-form-item>
						</el-row>
					</div>
				</div>
			</div>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button :size="size" :loading="loading" type="primary" @click="submit" >
                <span v-if="!loading">确 定</span>
                <span v-else>提 交 中...</span>
            </el-button>
            <el-button :size="size" @click="closeForm">取 消</el-button>
        </div>
        <Icon :iconshow.sync="iconDialogStatus" :icon.sync="form.icon" size="small"></Icon>
    </el-dialog>
	`
    ,
    props: {
        show: {
            type: Boolean,
            default: false
        },
        size: {
            type: String,
        },
        menu_id: {
            type: String,
        },
        action: {
            type: Array,
        },
        info: {
            type: Object,
        }
    },
    data() {
        return {
            form: {
                server_create_status: 1,
                vue_create_status: 1,
                button_color: 'primary',
                select_type: 1,
                fields: [],
                list_filter: [],
                tab_config: [],
                with_join: [],
                type: '',
                icon: '',
                name: '',
                action_name: '',
                dialog_size: '85%',
                pagesize: '20',
                sql: '',
                status_val: '',
                orderby: '',
                tree_config: '',
                table_height: '',
                
                codeTabActive: 'frontend', // 控制选项卡激活状态
                q_template: '<div class="super-page">\n  <h1>自定义页面</h1>\n</div>', // 前端代码
                h_php: 'public function handle($request) {\n    // 处理请求\n    return [];\n}', // 后端PHP代码
                
                other_config: {
                    export_type: '',
                    show_list_button: '',
                    hook: [],
                    excel: '',
                    tree_show: 2,
                    left_tree_show: 2,
                    after_hook: '',
                    befor_hook: '',
                    printer_status: 2,
                    list_button_style: 1,
                }
            },
            iconDialogStatus: false,
            post_fields: [],
            activeName: 'first',
            tableList: [],
            dialog: true,
            button: true,
            loading: false,
            ischeck_fields: [],
            activeName: '基本信息',
            table_fields: [],
            tab_fields: [],
            model_fields: [],
            jump_fields: [],
            group_fields: [], // 审批组
            flow_tablelist: [], // 审批表
            dbtype: '',
            beforHookChecked: false,
            afterHookChecked: false,
            restaurants: [{'value': 'null'}, {'value': 'not null'}],
            rules: {
                name: [{
                    required: true,
                    message: '方法中文名不能为空',
                    trigger: 'blur'
                }],
                action_name: [{
                    required: true,
                    message: '方法英文名不能为空',
                    trigger: 'blur'
                }, {
                    pattern: /^[a-zA-Z0-9_]+$/,
                    message: '仅允许输入字母、数字和下划线',
                    trigger: 'blur'
                }],
                type: [{
                    required: true,
                    message: '方法类型不能为空',
                    trigger: 'blur'
                }],
            },
        }
    },
    methods: {
        submit() {
            this.$refs['form'].validate(valid => {
                if (valid) {
                    //超级页面开始
                    if (this.form.type == 55 && !this.validateSuperPage()) {
                        this.loading = false
                        return
                    }
                    //超级页面结束
                    
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/updateAction', this.form).then(res => {
                        if (res.data.status == 200) {
                            this.$message({message: '操作成功', type: 'success'})
                            this.$emit('refesh_list')
                            this.closeForm()
                        } else {
                            this.loading = false
                            this.$message.error(res.data.msg)
                        }
                    }).catch(() => {
                        this.loading = false
                    })
                }
            })
        },
        
        // 超级页面安全验证方法
        validateSuperPage() {
            // const forbidden = ['eval', 'Function', 'exec', 'system', 'shell_exec']
            const forbidden = ['eval', 'Function', 'system']
            
            // 检查前端代码
            if (this.form.q_template) {
                for (let word of forbidden) {
                    if (this.form.q_template.includes(word)) {
                        this.$message.error(`前端代码中检测到危险代码: ${word}`)
                        return false
                    }
                }
            }
            
            // 检查后端代码
            if (this.form.h_php) {
                for (let word of forbidden) {
                    if (this.form.h_php.includes(word)) {
                        this.$message.error(`后端代码中检测到危险代码: ${word}`)
                        return false
                    }
                }
            }
            
            return true
        },
        // 超级页面安全验证方法结束
        open() {
            this.form = this.info
            this.setDefaultVal('list_filter')
            this.setDefaultVal('tab_config')
            this.setDefaultVal('fields')
            this.setDefaultVal('with_join')
            this.codeTabActive = 'frontend'; // 设置默认激活的选项卡
            if (this.form.other_config == null || this.form.other_config == '') {
                this.form.other_config = {}
            } else {
                this.form.other_config = JSON.parse(this.form.other_config)
            }
            if (this.form.other_config.befor_hook !== '') {
                this.beforHookChecked = true
            }
            if (this.form.other_config.after_hook !== '') {
                this.afterHookChecked = true
            }
            
            //超级页面开始
            if (!this.form.q_template) {
                this.form.q_template = '<div class="super-page">\n  <h1>自定义页面</h1>\n</div>'
            }
            if (!this.form.h_php) {
                this.form.h_php = 'public function handle($request) {\n    // 处理请求\n    return [];\n}'
            }
            //超级页面结束
            
            this.initAction()
            axios.post(base_url + '/Sys.Base/getPostField', {menu_id: this.menu_id}).then(res => {
                this.post_fields = res.data.data
                this.jump_fields = res.data.jump_field
                this.tab_fields = res.data.tab_fields
                this.model_fields = res.data.model_fields
                this.tableList = res.data.tableList
                this.dbtype = res.data.dbtype
                this.flow_tablelist = res.data?.flowTableList || []; // 审批表数据
                this.group_fields = res.data?.groupFields || []; // 审批组数据
            })
        },
        selectType(val) {
            if (val !== 1) {
                this.form.list_filter = []
                this.form.with_join = []
            }
            if (val !== 3 || val !== 4) {
                this.form.tab_config = []
            }
            if (val !== 7) {
                this.form.dialog_size = ''
            }
            
            
            if (val == 55 && !this.form.super_page) {
                this.$set(this.form, 'super_page', {
                    frontend: {q_template: '', script: '', style: ''},
                    backend: {language: 'php', code: ''}
                })
            }
            
            this.action.forEach(item => {
                if (this.form.type == item.type) {
                    this.dialog = item.dialog
                    this.button = item.button
                    this.form.icon = item.icon
                    this.form.button_color = item.button_color
                    this.form.name = item.name
                    this.form.action_name = item.action_name
                }
            })
        },
        getTableFields(i) {
            axios.post(base_url + '/Sys.Base/getTableFields', {controller_name: this.form.with_join[i].relative_table}).then(res => {
                this.table_fields = res.data.filedList
            })
        },
        // 添加新的方法
        onFlowTableChange(menu_id) {
            // 清空之前的选择
            this.form.other_config.flow_filed = ''
            this.table_fields = []
            
            if (menu_id) {
                axios.post(base_url + '/Sys.Base/getFlowTableFields', {menu_id}).then(res => {
                    this.table_fields = res.data.filedList
                    // 如果有字段数据，自动选择第一个字段
                    if (res.data.filedList && res.data.filedList.length > 0) {
                        this.form.other_config.flow_filed = res.data.filedList[0].Field
                    }
                })
            }
        },
        
        // 修改 getFlowTableGroup 方法
        getFlowTableGroup(menu_id) {
            axios.post(base_url + '/Sys.Base/getFlowTableGroup', {menu_id}).then(res => {
                this.group_fields = res.data.group
            })
        },
        closeForm() {
            this.$emit('update:show', false)
            this.loading = false
            this.$nextTick(() => {
                this.$refs['form'].resetFields()
                this.form.dialog_size = ''
                this.form.icon = ''
                this.form.sql = ''
                this.beforHookChecked = false
                this.afterHookChecked = false
            })
        },
        initAction() {
            this.action.forEach(item => {
                if (this.form.type == item.type) {
                    this.dialog = item.dialog
                    this.button = item.button
                }
            })
        },
        setDefaultVal(key) {
            if (this.form[key] == null || this.form[key] == '') {
                this.form[key] = []
            }
        },
        addItem(key) {
            this.form[key].push({})
        },
        deleteItem(key, index) {
            this.form[key].splice(index, 1)
        },
        clearItem(key) {
            this.form[key] = []
        },
        setTreeLoadType(val) {
            if (val) {
                this.form.other_config.tree_load_type = 1
                this.form.other_config.tree_show = 1
            } else {
                this.form.other_config.tree_load_type = ""
            }
        },
        selectTreeLoadType() {
            this.form.list_filter = []
        },
        querySearch(queryString, cb) {
            var restaurants = this.restaurants;
            cb(restaurants);
        },
        setBeforHook(val) {
            if (val) {
                axios.post(base_url + '/Sys.Base/getHookPath', {
                    'menu_id': this.form.menu_id,
                    'actionName': this.form.action_name,
                    'type': 1
                }).then(res => {
                    this.form.other_config.befor_hook = res.data.data
                })
            } else {
                this.form.other_config.befor_hook = ''
            }
        },
        setAfterHook(val) {
            if (val) {
                axios.post(base_url + '/Sys.Base/getHookPath', {
                    'menu_id': this.form.menu_id,
                    'actionName': this.form.action_name,
                    'type': 2
                }).then(res => {
                    this.form.other_config.after_hook = res.data.data
                })
            } else {
                this.form.other_config.after_hook = ''
            }
        },
    },
});


//api方法添加
Vue.component('ApiAdd', {
    template: `
	<el-dialog title="创建操作方法" width="700px" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm">
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="100px">
            <el-tabs v-model="activeName">
                <el-tab-pane style="padding-top:10px"  label="基本信息" name="基本信息">
                    <el-row>
                        <el-form-item label="方法类型" prop="type">
                            <el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
                                <el-option v-for="(item,i) in actionList" :key="i" :label="item.name" :value="item.type"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-row>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="方法名称" prop="name">
                                <el-input v-model="form.name" clearable placeholder="方法中文名称"  />
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="英文名称" prop="action_name">
                                <el-input v-model="form.action_name" clearable placeholder="方法英文名称"/>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row v-if="form.type==50">
                        <el-form-item label="登录方式" prop="login_type">
                            <el-select style="width:100%" v-model="form.other_config.login_type" filterable placeholder="请选择">
                                <el-option label="账号密码登录" value="1"></el-option>
                                <el-option label="短信验证码登录" value="2"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-row>
                    <el-row v-if="form.type==50">
                        <el-row v-if="form.other_config.login_type == 1">
                            <el-col :span="12">
                                <el-form-item label="用户名字段" prop="userField">
                                    <el-select style="width:100%" v-model="form.other_config.userField" filterable placeholder="请选择字段">
                                        <el-option v-for="(vo,i) in post_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12">
                                <el-form-item label="密码字段" prop="pwdField">
                                    <el-select style="width:100%" v-model="form.other_config.pwdField" filterable placeholder="请选择字段">
                                        <el-option v-for="(vo,i) in post_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row v-if="form.other_config.login_type == 2">
                            <el-col :span="24">
                                <el-form-item label="手机号字段" prop="smsField">
                                    <el-select style="width:100%" v-model="form.other_config.smsField" filterable placeholder="请选择字段">
                                        <el-option v-for="(vo,i) in post_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-row>
                   <el-row v-if="form.type==51">
                        <el-form-item label="短信平台" prop="sms_partenr">
                            <el-select style="width:100%" v-model="form.other_config.sms_partenr" filterable placeholder="请选择">
                                <el-option v-for="(item,i) in sms_list" :key="i" :label="item.name" :value="item.type"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-row>
					<el-row v-if="form.type == 5">
                        <el-form-item :label="'查询条件字段'" prop="search_field">
                            <el-checkbox-group v-model="form.other_config.detail_search_field">
                                <el-checkbox v-for="item in search_field" :label="item.field" :key="item.field">{{item.title}}</el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
                    </el-row>
                    <el-row v-if="dialog || form.type == 1">
                        <el-form-item :label="form.type ==5 || form.type ==50 ? '返回字段' : '操作字段'" prop="fields">
                            <el-checkbox-group v-model="form.fields">
                                <el-checkbox v-for="item in post_fields" :label="item.field" :key="item.field">{{item.title}}</el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
                    </el-row>
                    <el-row v-if="form.type == 7">
                        <el-form-item label="状态值" prop="status_val">
                            <el-input v-model="form.status_val" placeholder="状态值"/>
                        </el-form-item>
                    </el-row>
                    <el-row v-if="form.type == 1">
                        <el-col :span="12">
                            <el-form-item label="分页大小" prop="type">
                                <el-select v-model="form.pagesize" placeholder="请选择">
                                    <el-option key="1" label="10条每页" value="10"></el-option>
                                    <el-option key="2" label="20条每页" value="20"></el-option>
                                    <el-option key="3" label="50条每页" value="50"></el-option>
                                    <el-option key="4" label="100条每页" value="100"></el-option>
                                    <el-option key="5" label="200条每页" value="200"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="排序" prop="orderby">
                                <el-input v-model="form.orderby" placeholder="如 id desc"/>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row v-if="form.type == 1">
                        <el-form-item label="过滤条件" prop="list_filter">
                            <el-row v-for="(item,i) in form.list_filter" :key="i">
                                <el-col :span="8">
                                    <el-form-item style="margin-bottom:3px !important">
                                        <el-input v-model="item.searchField" placeholder="请设置字段"/>
                                    </el-form-item>
                                </el-col>
								<el-col :span="6">
                                    <el-form-item style="margin-bottom:3px !important">
                                        <el-select style="width:100%;position:relative; left:10px" v-model="item.searchCondition" filterable placeholder="请选择条件">
                                            <el-option key="0" value="=">=</el-option>
											<el-option key="1" value="<>"><></el-option>
											<el-option key="2" value="in">in</el-option>
                                        </el-select>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="6">
                                    <el-form-item style="margin-bottom:3px !important">
                                        <el-autocomplete :fetch-suggestions="querySearch" style="position:relative; left:20px" v-model="item.serachVal" placeholder="值"/>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="4">
                                    <el-button type="danger" size="mini" style="position:relative;left:35px"  icon="el-icon-close" @click="deleteItem('list_filter',i)"></el-button>
                                </el-col>
                            </el-row>
                            <el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('list_filter')">追加</el-button>
                            <el-button v-if="form.list_filter.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('list_filter')">清空</el-button>
                        </el-form-item>
                    </el-row>
					<el-row v-if="[1,2,3,4,5,6,7,8,9,50,51].includes(form.type)">
						<el-form-item label="方法钩子" prop="hook">
                            <el-row style="margin: 5px 0;">
                                <div style="display: inline-block; width: 100px;">
                                    <el-checkbox key="0" label="beforHook" v-model="beforHookChecked" @change="setBeforHook">前置钩子</el-checkbox>
                                </div>
                                <el-input style="width:300px;"  v-model="form.other_config.befor_hook" placeholder="方法前置钩子路径"  />
							</el-row>
							
                            <el-row style="margin: 5px 0;">
                                <div  style="display: inline-block; width: 100px;">前置钩子备注</div>
                                <el-input style="width:300px;"  v-model="form.other_config.befor_hook_remarks" placeholder="方法前置钩子备注"  />
                            </el-row>
									
                            <el-row style="margin: 5px 0;">
                                <div style="display: inline-block; width: 100px;">
							        <el-checkbox key="1" label="afterHook" v-model="afterHookChecked" @change="setAfterHook">后置钩子</el-checkbox>
							    </div>
							    <el-input style="width:300px;" v-model="form.other_config.after_hook" placeholder="方法后置钩子路径"  />
							</el-row>
							
                            <el-row style="margin: 5px 0;">
                                <div  style="display: inline-block; width: 100px;">后置钩子备注</div>
                                <el-input style="width:300px;"  v-model="form.other_config.after_hook_remarks" placeholder="方法后置钩子备注"  />
                            </el-row>
						</el-form-item>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane v-if="[1,2,3,4,5,11].includes(form.type) && dbtype !== 'mongo'" style="padding-top:10px"  label="多表配置" name="多表配置">
                    <el-form-item label="关联模型" prop="with_join">
                        <el-row :gutter="2" v-for="(item,i) in form.with_join" :key="i">
                            <el-col style="margin-left:0px" :span="5">
                                <el-form-item style="margin-bottom:3px !important"  prop="fk">
                                    <el-select style="width:100%" v-model="item.fk" filterable placeholder="主表外键">
                                        <el-option v-for="(vo,i) in model_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="5">
                                <el-form-item style="margin-bottom:3px !important" prop="relative_table">
                                    <el-select style="width:100%" v-model="item.relative_table" filterable placeholder="模型">
                                        <el-option v-for="(vo,i) in tableList" :key="i" :value="vo.controller_name">{{vo.controller_name}}</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="5">
                                <el-form-item style="margin-bottom:3px !important" prop="pk">
                                    <el-select @focus="getTableFields(i)" style="width:100%" v-model="item.pk" filterable placeholder="关联键">
                                        <el-option v-for="(vo,i) in table_fields" :key="i"  :value="vo.Field">{{vo.Field}}({{vo.Comment}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="7">
                                 <el-form-item style="margin-bottom:3px !important" prop="fields">
                                    <el-select @focus="getTableFields(i)" style="width:100%" multiple collapse-tags v-model="item.fields" filterable :placeholder="form.type == 1?'关联表查询字段':'操作字段'">
                                        <el-option v-for="(vo,i) in table_fields" :key="i"  :value="vo.Field">{{vo.Field}}({{vo.Comment}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="1">
                                <el-button type="danger" size="mini" style="position:relative;left:5px"  icon="el-icon-close" @click="deleteItem('with_join',i)"></el-button>
                            </el-col>
                        </el-row>
                        <el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('with_join')">追加</el-button>
                        <el-button v-if="form.with_join.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('with_join')">清空</el-button>
                    </el-form-item>
					<el-row v-if="[1,5].includes(form.type) && dbtype !== 'mongo'">
                        <el-form-item label="table列表sql" prop="sql">
                            <el-input v-model="form.sql" type="textarea" placeholder="通过sql语句生成table列表"  />
                        </el-form-item>
                    </el-row>
                </el-tab-pane>
            </el-tabs>
            
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button :size="size" :loading="loading" type="primary" @click="submit" >
                <span v-if="!loading">确 定</span>
                <span v-else>提 交 中...</span>
            </el-button>
            <el-button :size="size" @click="closeForm">取 消</el-button>
        </div>
    </el-dialog>
	`
    ,
    props: {
        show: {
            type: Boolean,
            default: false
        },
        size: {
            type: String,
        },
        action: {
            type: Array,
        },
        menuid: {
            type: String,
        },
        info: {
            type: Object,
        }
    },
    computed: {
        actionList() {
            return this.action.filter(item => item.show_api)
        }
    },
    data() {
        return {
            form: {
                server_create_status: 1,
                vue_create_status: 1,
                button_color: 'primary',
                select_type: 1,
                fields: [],
                list_filter: [],
                tab_config: [],
                with_join: [],
                type: '',
                icon: '',
                name: '',
                action_name: '',
                dialog_size: '600px',
                pagesize: '20',
                sql: '',
                menu_id: this.menuid,
                other_config: {
                    login_type: '',
                    hook: [],
                    after_hook: '',
                    befor_hook: '',
                    detail_search_field: [],
                }
            },
            iconDialogStatus: false,
            post_fields: [],
            activeName: 'first',
            tableList: [],
            dialog: true,
            button: true,
            loading: false,
            ischeck_fields: [],
            activeName: '基本信息',
            table_fields: [],
            search_field: [],
            tab_fields: [],
            model_fields: [],
            sms_list: [],
            dbtype: '',
            restaurants: [{'value': 'null'}, {'value': 'not null'}],
            beforHookChecked: false,
            afterHookChecked: false,
            rules: {
                name: [{required: true, message: '方法中文名不能为空', trigger: 'blur'}],
                action_name: [{
                    required: true,
                    message: '方法英文名不能为空',
                    trigger: 'blur'
                }, {pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许输入字母、数字和下划线', trigger: 'blur'}],
                type: [{required: true, message: '方法类型不能为空', trigger: 'blur'}],
            },
        }
    },
    methods: {
        submit() {
            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/createAction', this.form).then(res => {
                        if (res.data.status == 200) {
                            this.$message({message: '操作成功', type: 'success'})
                            this.$emit('refesh_list')
                            this.closeForm()
                        } else {
                            this.loading = false
                            this.$message.error(res.data.msg)
                        }
                    }).catch(() => {
                        this.loading = false
                    })
                }
            })
        },
        open() {
            axios.post(base_url + '/Sys.Base/getPostField', {menu_id: this.menuid}).then(res => {
                this.post_fields = res.data.data
                this.tab_fields = res.data.data
                this.model_fields = res.data.model_fields
                this.tableList = res.data.tableList
                this.sms_list = res.data.sms_list
                this.dbtype = res.data.dbtype
                this.search_field = res.data.search_field
            })
        },
        selectType(val) {
            if (val !== 1) {
                this.form.list_filter = []
                this.form.with_join = []
            }
            if (val !== 3 || val !== 4) {
                this.form.tab_config = []
            }
            if (val !== 7) {
                this.form.dialog_size = ''
            }
            this.action.forEach(item => {
                if (this.form.type == item.type) {
                    this.dialog = item.dialog
                    this.button = item.button
                    this.form.icon = item.icon
                    this.form.button_color = item.button_color
                    this.form.name = item.name
                    this.form.action_name = item.action_name
                }
            })
        },
        getTableFields(i) {
            axios.post(base_url + '/Sys.Base/getTableFields', {controller_name: this.form.with_join[i].relative_table}).then(res => {
                this.table_fields = res.data.filedList
            })
        },
        closeForm() {
            this.$emit('update:show', false)
            this.loading = false
            this.$nextTick(() => {
                this.$refs['form'].resetFields()
                this.form.dialog_size = ''
                this.form.icon = ''
                this.form.sql = ''
                this.form.other_config.after_hook = ''
                this.form.other_config.befor_hook = ''
            })
        },
        addItem(key) {
            this.form[key].push({})
        },
        deleteItem(key, index) {
            this.form[key].splice(index, 1)
        },
        clearItem(key) {
            this.form[key] = []
        },
        querySearch(queryString, cb) {
            var restaurants = this.restaurants;
            cb(restaurants);
        },
        setBeforHook(val) {
            if (val) {
                axios.post(base_url + '/Sys.Base/getHookPath', {
                    'menu_id': this.form.menu_id,
                    'actionName': this.form.action_name,
                    'type': 1
                }).then(res => {
                    this.form.other_config.befor_hook = res.data.data
                })
            } else {
                this.form.other_config.befor_hook = ''
            }
        },
        setAfterHook(val) {
            if (val) {
                axios.post(base_url + '/Sys.Base/getHookPath', {
                    'menu_id': this.form.menu_id,
                    'actionName': this.form.action_name,
                    'type': 2
                }).then(res => {
                    this.form.other_config.after_hook = res.data.data
                })
            } else {
                this.form.other_config.after_hook = ''
            }
        },
    },
});

//api方法修改
Vue.component('ApiUpdate', {
    template: `
	<el-dialog title="更新操作方法" width="700px" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm" append-to-body>
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="100px">
             <el-tabs v-model="activeName">
                <el-tab-pane style="padding-top:10px"  label="基本信息" name="基本信息">
                    <el-row>
                        <el-form-item label="方法类型" prop="type">
                            <el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
                                <el-option v-for="(item,i) in action" :key="i" :label="item.name" :value="item.type"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-row>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="方法名称" prop="name">
                                <el-input v-model="form.name" clearable placeholder="方法中文名称"  />
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="英文名称" prop="action_name">
                                <el-input v-model="form.action_name" clearable placeholder="方法英文名称"/>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row v-if="form.type==50">
                        <el-form-item label="登录方式" prop="login_type">
                            <el-select style="width:100%" v-model="form.other_config.login_type" filterable placeholder="请选择">
                                <el-option label="账号密码登录" value="1"></el-option>
                                <el-option label="短信验证码登录" value="2"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-row>
                    <el-row v-if="form.type==50">
                        <el-row v-if="form.other_config.login_type == 1">
                            <el-col :span="12">
                                <el-form-item label="用户名字段" prop="userField">
                                    <el-select style="width:100%" v-model="form.other_config.userField" filterable placeholder="请选择字段">
                                        <el-option v-for="(vo,i) in post_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12">
                                <el-form-item label="密码字段" prop="pwdField">
                                    <el-select style="width:100%" v-model="form.other_config.pwdField" filterable placeholder="请选择字段">
                                        <el-option v-for="(vo,i) in post_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row v-if="form.other_config.login_type == 2">
                            <el-col :span="24">
                                <el-form-item label="手机号字段" prop="smsField">
                                    <el-select style="width:100%" v-model="form.other_config.smsField" filterable placeholder="请选择字段">
                                        <el-option v-for="(vo,i) in post_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-row>
                    <el-row v-if="form.type==51">
                        <el-form-item label="短信平台" prop="sms_partenr">
                            <el-select style="width:100%" v-model="form.other_config.sms_partenr" filterable placeholder="请选择">
                                <el-option v-for="(item,i) in sms_list" :key="i" :label="item.name" :value="item.type"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-row>
					<el-row v-if="form.type == 5">
                        <el-form-item :label="'查询条件字段'" prop="search_field">
                            <el-checkbox-group v-model="form.other_config.detail_search_field">
                                <el-checkbox v-for="item in search_field" :label="item.field" :key="item.field">{{item.title}}</el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
                    </el-row>
                    <el-row v-if="dialog || form.type == 1">
                        <el-form-item :label="form.type ==5 || form.type ==50 ? '返回字段' : '操作字段'" prop="fields">
                            <el-checkbox-group v-model="form.fields">
                                <el-checkbox v-for="item in post_fields" :label="item.field" :key="item.field">{{item.title}}</el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
                    </el-row>
                    <el-row v-if="form.type == 7">
                        <el-form-item label="状态值" prop="status_val">
                            <el-input v-model="form.status_val" placeholder="状态值"/>
                        </el-form-item>
                    </el-row>
                    <el-row v-if="form.type == 1">
                        <el-col :span="12">
                            <el-form-item label="分页大小" prop="type">
                                <el-select v-model="form.pagesize" filterable placeholder="请选择">
                                    <el-option key="1" label="10条每页" value="10"></el-option>
                                    <el-option key="2" label="20条每页" value="20"></el-option>
                                    <el-option key="3" label="50条每页" value="50"></el-option>
                                    <el-option key="4" label="100条每页" value="100"></el-option>
                                    <el-option key="5" label="200条每页" value="200"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="排序方式" prop="orderby">
                                <el-input v-model="form.orderby" placeholder="如 id desc"/>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row v-if="form.type == 1">
                        <el-form-item label="过滤条件" prop="list_filter">
                            <el-row v-for="(item,i) in form.list_filter" :key="i">
                                <el-col :span="8">
                                    <el-form-item style="margin-bottom:3px !important">
                                        <el-input v-model="item.searchField" placeholder="请设置字段"/>
                                    </el-form-item>
                                </el-col>
								<el-col :span="6">
                                    <el-form-item style="margin-bottom:3px !important">
                                        <el-select style="width:100%;position:relative; left:10px" v-model="item.searchCondition" filterable placeholder="请选择条件">
                                            <el-option key="0" value="=">=</el-option>
											<el-option key="1" value="<>"><></el-option>
											<el-option key="2" value="in">in</el-option>
                                        </el-select>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="6">
                                    <el-form-item style="margin-bottom:3px !important">
                                        <el-autocomplete :fetch-suggestions="querySearch" style="position:relative; left:20px" v-model="item.serachVal" placeholder="值"/>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="4">
                                    <el-button type="danger" size="mini" style="position:relative;left:35px"  icon="el-icon-close" @click="deleteItem('list_filter',i)"></el-button>
                                </el-col>
                            </el-row>
                            <el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('list_filter')">追加</el-button>
                            <el-button v-if="form.list_filter && form.list_filter.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('list_filter')">清空</el-button>
                        </el-form-item>
                    </el-row>
					<el-row v-if="[1,2,3,4,5,6,7,8,9,50,51].includes(form.type)">
						<el-form-item label="方法钩子" prop="hook">
                            <el-row style="margin: 5px 0;">
                                <div style="display: inline-block; width: 100px;">
                                    <el-checkbox key="0" label="beforHook" v-model="beforHookChecked" @change="setBeforHook">前置钩子</el-checkbox>
                                </div>
                                <el-input style="width:300px;"  v-model="form.other_config.befor_hook" placeholder="方法前置钩子路径"  />
							</el-row>
							
                            <el-row style="margin: 5px 0;">
                                <div  style="display: inline-block; width: 100px;">前置钩子备注</div>
                                <el-input style="width:300px;"  v-model="form.other_config.befor_hook_remarks" placeholder="方法前置钩子备注"  />
                            </el-row>
									
                            <el-row style="margin: 5px 0;">
                                <div style="display: inline-block; width: 100px;">
							        <el-checkbox key="1" label="afterHook" v-model="afterHookChecked" @change="setAfterHook">后置钩子</el-checkbox>
							    </div>
							    <el-input style="width:300px;" v-model="form.other_config.after_hook" placeholder="方法后置钩子路径"  />
							</el-row>
							
                            <el-row style="margin: 5px 0;">
                                <div  style="display: inline-block; width: 100px;">后置钩子备注</div>
                                <el-input style="width:300px;"  v-model="form.other_config.after_hook_remarks" placeholder="方法后置钩子备注"  />
                            </el-row>
						</el-form-item>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane v-if="[1,2,3,4,5,11].includes(form.type) && dbtype !== 'mongo'" style="padding-top:10px"  label="多表配置" name="多表配置">
                    <el-form-item label="关联模型" prop="with_join">
                        <el-row :gutter="2" v-for="(item,i) in form.with_join" :key="i">
                            <el-col style="margin-left:0px" :span="5">
                                <el-form-item style="margin-bottom:3px !important"  prop="fk">
                                    <el-select style="width:100%" v-model="item.fk" filterable placeholder="主表外键">
                                        <el-option v-for="(vo,i) in model_fields" :key="i"  :value="vo.field">{{vo.title}}({{vo.field}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="5">
                                <el-form-item style="margin-bottom:3px !important" prop="relative_table">
                                    <el-select style="width:100%" v-model="item.relative_table" filterable placeholder="模型">
                                        <el-option v-for="(vo,i) in tableList" :key="i" :value="vo.controller_name">{{vo.controller_name}}</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="5">
                                <el-form-item style="margin-bottom:3px !important" prop="pk">
                                    <el-select @focus="getTableFields(i)" style="width:100%" v-model="item.pk" filterable placeholder="关联键">
                                        <el-option v-for="(vo,i) in table_fields" :key="i"  :value="vo.Field">{{vo.Field}}({{vo.Comment}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="7">
                                 <el-form-item style="margin-bottom:3px !important" prop="fields">
                                    <el-select @focus="getTableFields(i)" style="width:100%" multiple collapse-tags v-model="item.fields" filterable :placeholder="form.type == 1?'关联表查询字段':'操作字段'">
                                        <el-option v-for="(vo,i) in table_fields" :key="i"  :value="vo.Field">{{vo.Field}}({{vo.Comment}})</el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="1">
                                <el-button type="danger" size="mini" style="position:relative;left:5px"  icon="el-icon-close" @click="deleteItem('with_join',i)"></el-button>
                            </el-col>
                        </el-row>
                        <el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('with_join')">追加</el-button>
                        <el-button v-if="form.with_join && form.with_join.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('with_join')">清空</el-button>
                    </el-form-item>
					<el-row v-if="[1,5].includes(form.type) && dbtype !== 'mongo'">
                        <el-form-item label="table列表sql" prop="sql">
                            <el-input v-model="form.sql" type="textarea" placeholder="通过sql语句生成table列表"  />
                        </el-form-item>
                    </el-row>
                </el-tab-pane>
            </el-tabs>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button :size="size" :loading="loading" type="primary" @click="submit" >
                <span v-if="!loading">确 定</span>
                <span v-else>提 交 中...</span>
            </el-button>
            <el-button :size="size" @click="closeForm">取 消</el-button>
        </div>
        <Icon :iconshow.sync="iconDialogStatus" :icon.sync="form.icon" size="small"></Icon>
    </el-dialog>
	`
    ,
    props: {
        show: {
            type: Boolean,
            default: false
        },
        size: {
            type: String,
        },
        menu_id: {
            type: String,
        },
        action: {
            type: Array,
        },
        info: {
            type: Object,
        }
    },
    computed: {
        actionList() {
            return this.action.filter(item => item.show_api)
        }
    },
    data() {
        return {
            form: {
                server_create_status: 1,
                vue_create_status: 1,
                button_color: 'primary',
                select_type: 1,
                fields: [],
                list_filter: [],
                tab_config: [],
                with_join: [],
                type: '',
                icon: '',
                name: '',
                action_name: '',
                dialog_size: '600px',
                pagesize: '20',
                sql: '',
                other_config: {
                    login_type: '',
                    hook: [],
                    after_hook: '',
                    befor_hook: '',
                }
            },
            iconDialogStatus: false,
            post_fields: [],
            activeName: 'first',
            tableList: [],
            dialog: true,
            button: true,
            loading: false,
            ischeck_fields: [],
            activeName: '基本信息',
            table_fields: [],
            tab_fields: [],
            search_field: [],
            model_fields: [],
            sms_list: [],
            dbtype: '',
            beforHookChecked: false,
            afterHookChecked: false,
            restaurants: [{'value': 'null'}, {'value': 'not null'}],
            rules: {
                name: [{required: true, message: '方法中文名不能为空', trigger: 'blur'}],
                action_name: [{
                    required: true,
                    message: '方法英文名不能为空',
                    trigger: 'blur'
                }, {pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许输入字母、数字和下划线', trigger: 'blur'}],
                type: [{required: true, message: '方法类型不能为空', trigger: 'blur'}],
            },
        }
    },
    methods: {
        submit() {
            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/updateAction', this.form).then(res => {
                        if (res.data.status == 200) {
                            this.$message({message: '操作成功', type: 'success'})
                            this.$emit('refesh_list')
                            this.closeForm()
                        } else {
                            this.loading = false
                            this.$message.error(res.data.msg)
                        }
                    }).catch(() => {
                        this.loading = false
                    })
                }
            })
        },
        open() {
            this.form = this.info
            this.setDefaultVal('list_filter')
            this.setDefaultVal('tab_config')
            this.setDefaultVal('fields')
            this.setDefaultVal('with_join')
            this.initAction()
            if (this.form.other_config == '' || this.form.other_config == null) {
                this.form.other_config = {}
            } else {
                this.form.other_config = JSON.parse(this.info.other_config)
            }
            if (this.form.other_config.befor_hook !== '') {
                this.beforHookChecked = true
            }
            if (this.form.other_config.after_hook !== '') {
                this.afterHookChecked = true
            }
            this.initAction()
            axios.post(base_url + '/Sys.Base/getPostField', {menu_id: this.menu_id}).then(res => {
                this.post_fields = res.data.data
                this.tab_fields = res.data.data
                this.model_fields = res.data.model_fields
                this.tableList = res.data.tableList
                this.sms_list = res.data.sms_list
                this.dbtype = res.data.dbtype
                this.search_field = res.data.search_field
            })
        },
        selectType(val) {
            if (val !== 1) {
                this.form.list_filter = []
                this.form.with_join = []
            }
            if (val !== 3 || val !== 4) {
                this.form.tab_config = []
            }
            if (val !== 7) {
                this.form.dialog_size = ''
            }
            this.action.forEach(item => {
                if (this.form.type == item.type) {
                    this.dialog = item.dialog
                    this.button = item.button
                    this.form.icon = item.icon
                    this.form.button_color = item.button_color
                    this.form.name = item.name
                    this.form.action_name = item.action_name
                }
            })
        },
        getTableFields(i) {
            axios.post(base_url + '/Sys.Base/getTableFields', {controller_name: this.form.with_join[i].relative_table}).then(res => {
                this.table_fields = res.data.filedList
            })
        },
        closeForm() {
            this.$emit('update:show', false)
            this.loading = false
            this.$nextTick(() => {
                this.$refs['form'].resetFields()
                this.form.dialog_size = ''
                this.form.icon = ''
                this.form.sql = ''
                this.beforHookChecked = false
                this.afterHookChecked = false
            })
        },
        initAction() {
            this.action.forEach(item => {
                if (this.form.type == item.type) {
                    this.dialog = item.dialog
                    this.button = item.button
                }
            })
        },
        setDefaultVal(key) {
            if (this.form[key] == null || this.form[key] == '') {
                this.form[key] = []
            }
        },
        addItem(key) {
            this.form[key].push({})
        },
        deleteItem(key, index) {
            this.form[key].splice(index, 1)
        },
        clearItem(key) {
            this.form[key] = []
        },
        querySearch(queryString, cb) {
            var restaurants = this.restaurants;
            cb(restaurants);
        },
        setBeforHook(val) {
            if (val) {
                axios.post(base_url + '/Sys.Base/getHookPath', {
                    'menu_id': this.form.menu_id,
                    'actionName': this.form.action_name,
                    'type': 1
                }).then(res => {
                    this.form.other_config.befor_hook = res.data.data
                })
            } else {
                this.form.other_config.befor_hook = ''
            }
        },
        setAfterHook(val) {
            if (val) {
                axios.post(base_url + '/Sys.Base/getHookPath', {
                    'menu_id': this.form.menu_id,
                    'actionName': this.form.action_name,
                    'type': 2
                }).then(res => {
                    this.form.other_config.after_hook = res.data.data
                })
            } else {
                this.form.other_config.after_hook = ''
            }
        },
    },
});
