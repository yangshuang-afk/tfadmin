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


`;
document.head.appendChild(style);
//添加admin字段 TODO
Vue.component('AdminAdd', {
    template: `
		<el-dialog title="创建字段" width="95%" class="icon-dialog" :visible.sync="show" :before-close="closeForm" @open="open"  append-to-body>
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="120px">
        	<div class="field-container">
        		<!-- 左侧 - 字段类型选择 -->
				<div class="field-type-sidebar">
					<div class="sidebar-header">
						<h3>字段类型</h3>
					</div>
					<div class="type-list-container">
						<el-radio-group v-model="form.type" @change="selectType" class="type-radio-group">
							<div v-for="(item,i) in field" :key="i" class="type-item">
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
								<el-form-item label="字段标题" prop="title">
									<el-input v-model="form.title" clearable placeholder="字段中文描述"  />
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="字段名称" prop="field">
									<el-input v-model="form.field" clearable placeholder="字段英文名称"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row>
							<el-col :span="12">
								<el-form-item label="字段类型" prop="type">
									<el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
										<el-option v-for="(item,i) in field" :key="i" :label="item.name" :value="item.type"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item v-if="form.type == 9 || form.type==11 || form.type == 12" label="时间模板" prop="time_search_tempate">
								   <el-checkbox v-model="form.other_config.time_search_tempate">启用时间范围搜索模板</el-checkbox>
								   <el-checkbox v-if="form.type == 9" v-model="form.other_config.now_time">默认当前时间</el-checkbox>
								</el-form-item>
								<el-form-item v-else-if="form.type == 22" label="层级" prop="address_type">
									<el-select style="width:100%" v-model="form.other_config.address_type"  placeholder="请选择级层">
										<el-option  key="0" label="省市区" value="1"></el-option>
										<el-option  key="1" label="省市" value="2"></el-option>
										<el-option  key="2" label="省" value="3"></el-option>
									</el-select>
								</el-form-item>
								<el-form-item v-else-if="form.type == 13" label="上传样式" prop="upload_type">
									<el-select style="width:100%" v-model="form.other_config.upload_type"  placeholder="上传样式">
										<el-option key="0" label="样式1(带缩略图)" value="1"></el-option>
										<el-option key="1" label="样式2(带输入框)" value="2"></el-option>
									</el-select>
								</el-form-item>
								<el-form-item v-else-if="form.type == 38" label="所属应用" prop="application_id">
									<el-select style="width:100%" v-model="form.other_config.application_id"  placeholder="所属应用">
										<el-option v-for="(item,i) in application_list" :key="i" :label="item.application_name" :value="item.app_id"></el-option>
									</el-select>
								</el-form-item>
								<el-form-item v-else-if="form.type == 31" label="长度" prop="rand_length">
									<el-input v-model="form.other_config.rand_length" clearable placeholder="随机数长度"/>
								</el-form-item>
								<el-form-item v-else label="默认值" prop="default_value">
									<el-input v-model="form.default_value" clearable placeholder="同步数据表默认值"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 8">
							<el-col :span="24">
								<el-form-item label="文本框高度" prop="maxrows">
									<el-input v-model="form.other_config.maxrows" clearable placeholder="如4 代表4行文本框高度"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 9 || form.type == 11 || form.type == 12">
							<el-form-item label="日期格式" prop="datetime_config">
								<el-select style="width:100%" :size="size" @change="selectDate" v-model="form.datetime_config" clearable  filterable placeholder="请选择日期格式">
										<el-option key="1" label="年月日时分秒" value="datetime"></el-option>
										<el-option key="2" label="年月日" value="date"></el-option>
										<el-option key="5" label="年月" value="yearmonth"></el-option>
										<el-option key="3" label="年" value="year"></el-option>
										<el-option key="4" label="月" value="month"></el-option>
										<el-option key="6" label="时分秒" value="time"></el-option>
										<el-option key="7" label="多日期" value="dates"></el-option>
										<el-option key="8" label="月/日/年" value="mdy"></el-option>
										<el-option key="9" label="月/日/年时分秒" value="mdyhis"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 10">
							<el-form-item label="日期范围格式" prop="rangetime_type">
								<el-select style="width:100%" :size="size" v-model="form.other_config.rangetime_type" clearable  filterable placeholder="请选择日期格式">
										<el-option key="1" label="年月日时分秒" value="datetime"></el-option>
										<el-option key="2" label="年月日" value="date"></el-option>
										<el-option key="3" label="时分秒" value="time"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="[13,14,15,16].includes(form.type)">
							<el-col :span="24">
								<el-form-item label="后缀格式" prop="filetype">
									<el-input v-model="form.other_config.filetype" clearable placeholder="允许上传的格式 多个逗号隔开 doc,xls,xlsx"/>
								</el-form-item>
							</el-col>
							<el-col :span="24">
								<el-form-item label="重命名" prop="rename_status">
									<el-select style="width:100%" v-model="form.other_config.rename_status" clearable placeholder="文件上传命名方式">
										<el-option key="0" label="重命名文件" value="1"></el-option>
										<el-option key="1" label="保持原名" value="2"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 13">
							<el-col :span="24">
								<el-form-item label="图片裁剪" prop="crop">
									<el-input v-model="form.other_config.crop" clearable placeholder="裁剪框大小 格式如 500,500 不填表示不裁剪"/>
								</el-form-item>
							</el-col>
						</el-row>

						<!-- 图片预览 -->
                        <el-row>
                            <el-col v-if="[13,14].includes(form.type)" :span="24">
                                <el-form-item label="图片预览" prop="previewImage">
                                    <el-radio-group v-model="form.other_config.previewImage">
                                        <el-radio :label="1">是</el-radio>
                                        <el-radio :label="0">否</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        
						<el-row v-if="form.type == 19">
							<el-col :span="24">
								<el-form-item label="进度条样式" prop="jdt">
									<el-select style="width:100%" :size="size" v-model="form.other_config.jdt" clearable  filterable placeholder="请选择进度条显示样式">
										<el-option key="1" label="长条" value="changtiao"></el-option>
										<el-option key="2" label="圆形" value="cicle"></el-option>
								</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						
                        <el-row v-if="form.type == 31">
							<el-form-item label="随机数格式" prop="form.other_config.rand_config">
								<el-select style="width:100%" :size="size" v-model="form.other_config.rand_config" clearable  filterable placeholder="请选择随机数格式">
										<el-option key="1" label="字母大小写数字组合" value="all"></el-option>
										<el-option key="2" label="字母大小写组合" value="letter"></el-option>
										<el-option key="3" label="纯数字组合" value="number"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						
						<el-row v-if="form.type == 39">
							<el-form-item label="规格配置" prop="guige">
								<draggable v-model="form.other_config.guige" v-bind="{group:'item'}" handle=".jzd-handle">
								<el-row v-for="(item,i) in form.other_config.guige" :key="i">
									<el-col :span="4">
										<el-form-item style="margin-bottom:3px !important">
											<el-input  v-model="item.title" placeholder="规格名称"/>
										</el-form-item>
									</el-col>
									<el-col :span="4">
										<el-form-item style="margin-bottom:3px !important">
											<el-input style="position:relative;left:5px;" v-model="item.field" placeholder="规格字段名"/>
										</el-form-item>
									</el-col>
									<el-col :span="5">
										<el-form-item style="margin-bottom:3px !important">
											<el-select style="position:relative;left:10px;" v-model="item.type" size="small" clearable placeholder="字段类型">
												<el-option key="1" label="文本框" value="1"></el-option>
												<el-option key="2" label="下拉框" value="2"></el-option>
												<el-option key="3" label="图片" value="13"></el-option>
												<el-option key="4" label="开关按钮" value="6"></el-option>
												<el-option key="5" label="日期框" value="9"></el-option>
												<el-option key="6" label="计数器" value="17"></el-option>
												<el-option key="7" label="标签" value="18"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :span="7">
										<el-form-item style="margin-bottom:3px !important">
											<el-input style="position:relative;left:15px;" v-model="item.item" placeholder="选项配置,多个逗号隔开"/>
										</el-form-item>
									</el-col>
									<el-col :span="4">
										<el-button type="danger" size="mini" style="position:relative;left:16px"  icon="el-icon-close" @click="deleteGuigeItem('item_config',i)"></el-button>
										<el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:12px" icon="el-icon-rank"></el-button>
									</el-col>
								</el-row>
								</draggable>
								<div>
									<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addGuigeItem('item_config')">追加</el-button>
									<el-button v-if="form.item_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearGuigeItem('item_config')">清空</el-button>
								</div>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 40">
							<el-col :span="24">
								<el-form-item label="计算公式" prop="jisuan">
									<el-input v-model="form.other_config.jisuan" clearable placeholder="原生php语法计算公式，如 num * price"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="list_item">
							<el-form-item label="选项配置" prop="item_config">
								<draggable v-model="form.item_config" v-bind="{group:'item'}" handle=".jzd-handle">
								<el-row v-for="(item,i) in form.item_config" :key="i">
									<el-col :span="7">
										<el-form-item style="margin-bottom:3px !important">
											<el-input  v-model="item.key" placeholder="选项名称"/>
										</el-form-item>
									</el-col>
									<el-col :span="6">
										<el-form-item style="margin-bottom:3px !important">
											<el-input style="position:relative;left:5px;" v-model="item.val" placeholder="选项值"/>
										</el-form-item>
									</el-col>
									<el-col :span="6">
										<el-form-item style="margin-bottom:3px !important">
											<el-select style="position:relative;left:10px;" v-model="item.label_color" size="small" clearable placeholder="请选择背景色">
												<el-option key="1" style="background:#409eff" label="primary" value="primary"></el-option>
												<el-option key="2" style="background:#67c23a" label="success" value="success"></el-option>
												<el-option key="3" style="background:#909399" label="info" value="info"></el-option>
												<el-option key="4" style="background:#e6a23c" label="warning" value="warning"></el-option>
												<el-option key="5" style="background:#f56c6c" label="danger" value="danger"></el-option>
												<el-option key="6" style="background:#5ed84f" label="fresh-green" value="fresh-green"></el-option>
												<el-option key="7" style="background:#353640" label="deep-space" value="deep-space"></el-option>
												<el-option key="8" style="background:#6b6f80" label="cool-gray" value="cool-gray"></el-option>
												<el-option key="9" style="background:#6967ce" label="violet" value="violet"></el-option>
												<el-option key="10" style="background:#28afd0" label="aqua" value="aqua"></el-option>
												<el-option key="11" style="background:#fdb901" label="golden" value="golden"></el-option>
												<el-option key="12" style="background:#fa626b" label="coral" value="coral"></el-option>
												<el-option key="13" style="background:#babfc7" label="silver" value="silver"></el-option>
												<el-option key="14" style="background:#53a8ff" label="sky-blue" value="sky-blue"></el-option>
												<el-option key="15" style="background:#85ce61" label="leaf-green" value="leaf-green"></el-option>
												<el-option key="16" style="background:#a6a9ad" label="mist-gray" value="mist-gray"></el-option>
												<el-option key="17" style="background:#eebe77" label="honey" value="honey"></el-option>
												<el-option key="18" style="background:#f78989" label="blossom" value="blossom"></el-option>
												<el-option key="19" style="background:#3d4b5c" label="steel" value="steel"></el-option>
												<el-option key="20" style="background:#7e57c2" label="amethyst" value="amethyst"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :span="4">
										<el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem('item_config',i)"></el-button>
										<el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:12px" icon="el-icon-rank"></el-button>
									</el-col>
								</el-row>
								</draggable>
								<div>
									<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('item_config')">追加</el-button>
									<el-button v-if="form.item_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('item_config')">清空</el-button>
									<el-select v-if="form.item_config.length === 0"  :size="size" style="height:25px; light:25px; margin-left:20px;" v-model="default_config" @change="setDefaultItem"  placeholder="请选择默认配置">
										<el-option v-for="(item,i) in item_field" :key="i" :label="item.name" :value="item.item"></el-option>
									</el-select>
								</div>
							</el-form-item>
						</el-row>
						<el-row v-if="(list_item || [2,3,4,5,30,33,41].includes(form.type)) && dbtype !== 'mongo'">
							<el-form-item label="sql数据源" prop="sql">
								<el-input type="textarea" v-model="form.sql" clearable placeholder="单选/下拉/多选选项/session/隐藏域, sql数据源"/>
							</el-form-item>
						</el-row>
						<el-row v-if="(list_item || [41].includes(form.type)) && dbtype !== 'mongo'">
                            <el-form-item label="记已读的session" prop="crop">
                                <el-input v-model="form.other_config.yidufield" clearable placeholder="填写要记已读的session字段"/>
                            </el-form-item>
						</el-row>
					    <el-row v-if="[2,4].includes(form.type) && dbtype !== 'mongo'">
							<el-form-item label="联动字段" prop="liandong_field">
								<el-input v-model="form.other_config.liandong_field" clearable placeholder="二级联动字段名"/>
							</el-form-item>
						</el-row>
						<el-row>
							<el-col v-if="dbtype !== 'mongo'" :span="12">
								<el-form-item label="创建字段" prop="create_table_field">
									<el-radio-group v-model="form.create_table_field">
										<el-radio :label="1">是</el-radio>
										<el-radio :label="0">否</el-radio>
									</el-radio-group>
								</el-form-item>
							</el-col>
							<el-col :span="dbtype !== 'mongo' ? 12:24">
								 <el-form-item label="显示状态" prop="list_show">
									<el-select style="width:100%" v-model="form.list_show" :size="size" filterable placeholder="请选择">
										<el-option-group label="显示状态">
											<el-option key="1" label="不显示" :value="0"></el-option>
										</el-option-group>
										<el-option-group label="显示位置">
											<el-option key="3" label="居中" :value="2"></el-option>
											<el-option key="4" label="居左" :value="3"></el-option>
											<el-option key="5" label="居右" :value="4"></el-option>
										</el-option-group>
									</el-select>
								 </el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.create_table_field == 0 && dbtype !== 'mongo' && form.type != 40">
							<el-col :span="24">
								<el-form-item label="所属表" prop="belong_table">
									<el-select @focus="getTablesByMenuId" @change="setPostStatus" style="width:100%" clearable v-model="form.belong_table" filterable  placeholder="关联字段所属表（配置多表专属，其它勿设置）">
										<el-option v-for="(item,i) in tableList" :key="i" :value="item.table_name">{{item.table_name}}({{item.title}})</el-option>
									</el-select>
									<label style="color:red;font-size:13px;display:block;">
                                        关联字段规则：关联字段小驼峰__需要反显字段<br>
                                        示例：memberId__member_name
									</label>
								</el-form-item>
							</el-col>
						</el-row>
						 <el-row v-if="form.type != 40">
							<el-col :span="12">
								<el-form-item label="验证方式" prop="validate">
									<el-checkbox-group v-model="form.validate">
										<el-checkbox label="notempty">不为空</el-checkbox>
										<el-checkbox label="unique">唯一</el-checkbox>
									</el-checkbox-group>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="可录入" prop="post_status">
									<el-radio v-model="form.post_status" :label="1">是</el-radio>
									<el-radio v-model="form.post_status" :label="0">否</el-radio>
								</el-form-item>
							</el-col>
						</el-row>
                        
                        
                        <!-- 在.extended-info-section部分添加 -->
                        <el-row v-if="form.type == 21">
                          <el-col :span="12">
                            <el-form-item label="键占位文本" prop="key_placeholder">
                              <el-input v-model="form.other_config.key_placeholder" clearable placeholder="键输入框的占位文本"/>
                            </el-form-item>
                          </el-col>
                          <el-col :span="12">
                            <el-form-item label="值占位文本" prop="value_placeholder">
                              <el-input v-model="form.other_config.value_placeholder" clearable placeholder="值输入框的占位文本"/>
                            </el-form-item>
                          </el-col>
                        </el-row>
                        
                        
                        
						<el-row v-if="form.type != 40">
							<el-col :span="20">
								<el-form-item label="验证规则" prop="rule">
									<el-input v-model="form.rule" clearable placeholder="输入框验证规则"/>
								</el-form-item>
							</el-col>
							<el-col :span="4">
								<el-select :size="size" v-model="default_rules" @change="setDefaultRule" prop="default_rules" clearable filterable placeholder="请选择">
									<el-option v-for="(item,index) in ruleList" :key="index" :label="index" :value="item"></el-option>
								</el-select>
							</el-col>
						</el-row>
						<el-row v-if="form.type != 40">
							<el-col :span="8">
								<el-form-item label="数据结构" prop="datatype">
									<el-select v-model="form.datatype" filterable @change="setFieldLength"  placeholder="请选择">
										<el-option v-for="(item,i) in propertyField" :key="i" :label="item.name" :value="item.name"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
							<el-col v-if="dbtype !== 'mongo'" :span="8">
								<el-form-item label="字段长度" prop="length">
									<el-input v-model="form.length" placeholder="字段长度"/>
								</el-form-item>
							</el-col>
							<el-col :span="8">
								<el-form-item label="字段索引" prop="indexdata">
									<el-select v-model="form.indexdata" filterable placeholder="请选择">
										<el-option key="1" label="普通索引" value="index"></el-option>
										<el-option key="2" label="唯一索引" value="unique"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
					</div>
				</div>
				<!-- 右侧 - 拓展信息 -->
				<div class="extended-info-section">
					<div class="section-header">
						<h3>拓展信息</h3>
					</div>
					<div class="section-content">
						<el-row v-if="form.type ==1 || form.type == 17">
							<el-col :span="12">
								<el-form-item label="最大输入" prop="input_length">
									<el-input v-model="form.other_config.input_length" placeholder="请输入长度">
										<el-button type="success" slot="append">个字符</el-button>
									</el-input>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="背景色" prop="label_color">
									<el-select style="width:100%" v-model="form.other_config.label_color" :size="size" clearable filterable placeholder="请选择">
										<el-option key="1" style="background:#409eff" label="primary" value="primary"></el-option>
										<el-option key="2" style="background:#67c23a" label="success" value="success"></el-option>
										<el-option key="3" style="background:#909399" label="info" value="info"></el-option>
										<el-option key="4" style="background:#e6a23c" label="warning" value="warning"></el-option>
										<el-option key="5" style="background:#f56c6c" label="danger" value="danger"></el-option>
										<el-option key="6" style="background:#5ed84f" label="fresh-green" value="fresh-green"></el-option>
										<el-option key="7" style="background:#353640" label="deep-space" value="deep-space"></el-option>
										<el-option key="8" style="background:#6b6f80" label="cool-gray" value="cool-gray"></el-option>
										<el-option key="9" style="background:#6967ce" label="violet" value="violet"></el-option>
										<el-option key="10" style="background:#28afd0" label="aqua" value="aqua"></el-option>
										<el-option key="11" style="background:#fdb901" label="golden" value="golden"></el-option>
										<el-option key="12" style="background:#fa626b" label="coral" value="coral"></el-option>
										<el-option key="13" style="background:#babfc7" label="silver" value="silver"></el-option>
										<el-option key="14" style="background:#53a8ff" label="sky-blue" value="sky-blue"></el-option>
										<el-option key="15" style="background:#85ce61" label="leaf-green" value="leaf-green"></el-option>
										<el-option key="16" style="background:#a6a9ad" label="mist-gray" value="mist-gray"></el-option>
										<el-option key="17" style="background:#eebe77" label="honey" value="honey"></el-option>
										<el-option key="18" style="background:#f78989" label="blossom" value="blossom"></el-option>
										<el-option key="19" style="background:#3d4b5c" label="steel" value="steel"></el-option>
										<el-option key="20" style="background:#7e57c2" label="amethyst" value="amethyst"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type ==1">
							<el-col :span="12">
								<el-form-item label="输入前缀" prop="prefix">
									<el-input v-model="form.other_config.prefix" placeholder="请输入前缀"/>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="输入后缀" prop="afterfix">
									<el-input v-model="form.other_config.afterfix" placeholder="请输入后缀"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type ==1 || form.type == 8 || form.type == 17">
							<el-form-item label="前置图标" prop="pre_icon">
								<el-input v-model="form.icon" placeholder="点击选择图标" clearable>
									<el-button type="success" slot="append" icon="el-icon-thumb"  @click="iconDialogStatus = true">请选择</el-button>
								</el-input>
							</el-form-item>
						</el-row>
						<el-row>
							<el-form-item label="提示文本" prop="placeholder">
								<el-input v-model="form.other_config.placeholder" clearable placeholder="请输入文本框placeholder"/>
							</el-form-item>
						</el-row>
						<el-row>
							<el-form-item label="备注说明" prop="inputRemark">
								<el-input v-model="form.other_config.inputRemark" clearable placeholder="输入框下方的备注说明文字"/>
							</el-form-item>
						</el-row>
						<el-row v-if="(form.type == 2 || form.type == 3) && form.sql !== ''">
							<el-form-item label="远程搜索字段" prop="remote_research_field">
								<el-input v-model="form.other_config.remote_research_field" clearable placeholder="设置目标数据表需要远程搜索的字段名"/>
							</el-form-item>
						</el-row>
						<el-row>
							<el-form-item label="显示条件" prop="show_condition">
								<el-input v-model="form.show_condition" clearable placeholder="输入框显示条件 如 form.status == 1"/>
							</el-form-item>
						</el-row>
					
					 
						
										 <!-- 新增的列表文字变色设置 -->
					    <el-row>
							<el-form-item label="列表文字变色" prop="tx_config" v-if="Array.isArray(form.tx_config)">
                                    <el-row v-for="(item,i) in form.tx_config" :key="i">
                                    <el-col :span="5">
                                            <el-form-item prop="tx_tiaojian">
                                                <el-select v-model="item.tx_tiaojian" clearable filterable placeholder="条件类型" style="width: 100%;">
                                                    <el-option
                                                        v-for="opt in txOptions"
                                                        :key="opt.value"
                                                        :label="opt.label"
                                                        :value="opt.value">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="10">
                                            <el-form-item prop="tx_zhi">
                                                <el-input v-model="item.tx_zhi" clearable placeholder="输入条件值，如 1 或 “{:session('admin.yuangong_id')}”"/>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="5">
                                            <el-form-item prop="tx_color" >
                                                <el-select style="width:100%" v-model="item.tx_color" :size="size" clearable filterable placeholder="颜色">
                                                    <el-option key="1" style="background:#409eff" label="primary" value="#409eff"></el-option>
                                                    <el-option key="2" style="background:#67c23a" label="success" value="#67c23a"></el-option>
                                                    <el-option key="3" style="background:#909399" label="info" value="#909399"></el-option>
                                                    <el-option key="4" style="background:#e6a23c" label="warning" value="#e6a23c"></el-option>
                                                    <el-option key="5" style="background:#f56c6c" label="danger" value="#f56c6c"></el-option>
                                                    <el-option key="6" style="background:#5ed84f" label="fresh-green" value="#5ed84f"></el-option>
                                                    <el-option key="7" style="background:#353640" label="deep-space" value="#353640"></el-option>
                                                    <el-option key="8" style="background:#6b6f80" label="cool-gray" value="#6b6f80"></el-option>
                                                    <el-option key="9" style="background:#6967ce" label="violet" value="#6967ce"></el-option>
                                                    <el-option key="10" style="background:#28afd0" label="aqua" value="#28afd0"></el-option>
                                                    <el-option key="11" style="background:#fdb901" label="golden" value="#fdb901"></el-option>
                                                    <el-option key="12" style="background:#fa626b" label="coral" value="#fa626b"></el-option>
                                                    <el-option key="13" style="background:#babfc7" label="silver" value="#babfc7"></el-option>
                                                    <el-option key="14" style="background:#53a8ff" label="sky-blue" value="#53a8ff"></el-option>
                                                    <el-option key="15" style="background:#85ce61" label="leaf-green" value="#85ce61"></el-option>
                                                    <el-option key="16" style="background:#a6a9ad" label="mist-gray" value="#a6a9ad"></el-option>
                                                    <el-option key="17" style="background:#eebe77" label="honey" value="#eebe77"></el-option>
                                                    <el-option key="18" style="background:#f78989" label="blossom" value="#f78989"></el-option>
                                                    <el-option key="19" style="background:#3d4b5c" label="steel" value="#3d4b5c"></el-option>
                                                    <el-option key="20" style="background:#7e57c2" label="amethyst" value="#7e57c2"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="2">
                                            <el-button type="danger" size="mini" style="position:relative;left:10px"  icon="el-icon-close" @click="deleteItem('tx_config',i)"></el-button>
                                        </el-col>
                                    </el-row>
								<div>
									<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('tx_config')">追加</el-button>
									<el-button v-if="form.tx_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('tx_config')">清空</el-button>
								</div>
							</el-form-item>
						</el-row>
					
				
						    <el-row>
							<el-form-item label="列表背景变色" prop="list_background_config" v-if="Array.isArray(form.list_background_config)">
                                    <el-row v-for="(background_config_item,i) in form.list_background_config" :key="i + 10086">
                                    <el-col :span="5">
                                            <el-form-item prop="tiaojian">
                                                <el-select v-model="background_config_item.tiaojian" clearable filterable placeholder="条件类型" style="width: 100%;">
                                                    <el-option
                                                        v-for="opt in txOptions"
                                                        :key="opt.value"
                                                        :label="opt.label"
                                                        :value="opt.value">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="10">
                                            <el-form-item prop="zhi">
                                                <el-input v-model="background_config_item.zhi" clearable placeholder="输入条件值，如 1 或 “{:session('admin.yuangong_id')}”"/>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="5">
                                            <el-form-item prop="color" >
                                                <el-select style="width:100%" v-model="background_config_item.color" :size="size" clearable filterable placeholder="颜色">
                                                    <el-option key="1" style="background:#409eff" label="primary" value="#409eff"></el-option>
                                                    <el-option key="2" style="background:#67c23a" label="success" value="#67c23a"></el-option>
                                                    <el-option key="3" style="background:#909399" label="info" value="#909399"></el-option>
                                                    <el-option key="4" style="background:#e6a23c" label="warning" value="#e6a23c"></el-option>
                                                    <el-option key="5" style="background:#f56c6c" label="danger" value="#f56c6c"></el-option>
                                                    <el-option key="6" style="background:#5ed84f" label="fresh-green" value="#5ed84f"></el-option>
                                                    <el-option key="7" style="background:#353640" label="deep-space" value="#353640"></el-option>
                                                    <el-option key="8" style="background:#6b6f80" label="cool-gray" value="#6b6f80"></el-option>
                                                    <el-option key="9" style="background:#6967ce" label="violet" value="#6967ce"></el-option>
                                                    <el-option key="10" style="background:#28afd0" label="aqua" value="#28afd0"></el-option>
                                                    <el-option key="11" style="background:#fdb901" label="golden" value="#fdb901"></el-option>
                                                    <el-option key="12" style="background:#fa626b" label="coral" value="#fa626b"></el-option>
                                                    <el-option key="13" style="background:#babfc7" label="silver" value="#babfc7"></el-option>
                                                    <el-option key="14" style="background:#53a8ff" label="sky-blue" value="#53a8ff"></el-option>
                                                    <el-option key="15" style="background:#85ce61" label="leaf-green" value="#85ce61"></el-option>
                                                    <el-option key="16" style="background:#a6a9ad" label="mist-gray" value="#a6a9ad"></el-option>
                                                    <el-option key="17" style="background:#eebe77" label="honey" value="#eebe77"></el-option>
                                                    <el-option key="18" style="background:#f78989" label="blossom" value="#f78989"></el-option>
                                                    <el-option key="19" style="background:#3d4b5c" label="steel" value="#3d4b5c"></el-option>
                                                    <el-option key="20" style="background:#7e57c2" label="amethyst" value="#7e57c2"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="2">
                                            <el-button type="danger" size="mini" style="position:relative;left:10px"  icon="el-icon-close" @click="deleteItem('list_background_config',i)"></el-button>
                                        </el-col>
                                    </el-row>
								<div>
									<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('list_background_config')">追加</el-button>
									<el-button v-if="form.list_background_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('list_background_config')">清空</el-button>
								</div>
							</el-form-item>
						</el-row>
					
						
						
                        <!-- 新增的字段完善提醒设置 -->
						<el-row>
							<el-form-item label="字段完善提醒">
								<el-col :span="6">
									<el-form-item prop="improve_tiaojian">
										<el-select v-model="form.improve_tiaojian" placeholder="选择条件类型" style="width: 100%;">
                                             <el-option
                                                v-for="opt in improveOptions"
                                                :key="opt.value"
                                                :label="opt.label"
                                                :value="opt.value">
                                            </el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="18">
									<el-form-item prop="improve_zhi">
										<el-input v-model="form.improve_zhi" clearable placeholder="输入条件值，如 1 或 session('admin.yuangong_id')"/>
									</el-form-item>
								</el-col>
							</el-form-item>
						</el-row>
						
						<el-row>
							<el-form-item label="列表字段" prop="list_feild">
								<el-input v-model="form.other_config.list_feild" clearable placeholder="请输入列表显示在哪个字段中"/>
							</el-form-item>
						</el-row>

						<el-row>
							<el-form-item label="其他属性" prop="shuxing">
								<el-checkbox-group v-model="form.other_config.shuxing">
									<el-checkbox key="0" label="tooltip">悬停提示</el-checkbox>
									<el-checkbox key="1" label="sort">表头排序</el-checkbox>
									<el-checkbox key="2" label="sum">字段汇总求和</el-checkbox>
									<el-checkbox v-if="[2,3,4,5].includes(form.type)" key="3" label="tabs">列表选项卡</el-checkbox>
									<el-checkbox v-if="[2,3,4,5,35,30,33,41].includes(form.type) && form.sql != ''" key="4" label="fanzhuan">数据显示反转</el-checkbox>
									<el-checkbox v-if="![13,14,15,16,21,39,25,26,27,38,30].includes(form.type)" key="5" label="readonly">编辑只读</el-checkbox>
								</el-checkbox-group>
							</el-form-item>
						</el-row>
						
						<el-row>
							<el-form-item label="列表方法" prop="list_action">
							    <el-select v-model="form.other_config.list_action" clearable placeholder="选择在列表触发的方法" style="width: 100%;">
                                    <el-option
                                    v-for="(item,index) in my_actions"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.action_name"
                                    ></el-option>
                                </el-select>
							</el-form-item>
						</el-row>
						
						<el-row v-if="form.other_config.shuxing.includes('tabs')">
							<el-form-item label="默认值" prop="default_tabs_value">
								<el-input v-model="form.other_config.default_tabs_value" clearable placeholder="选项卡选中默认值"/>
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
            default: 'small'
        },
        menuid: {
            type: String,
        },
        field: {
            type: Array,
        },
        item_field: {
            type: Array,
        }
    },
    data() {
        return {
            form: {
                title: '',
                field: '',
                type: '',
                post_status: 1,
                create_table_field: 1,
                list_show: 2,
                validate: [],
                item_config: [],
                other_config: {
                    address_type: '1',
                    now_time: false,
                    placeholder: '',
                    rand_config: '',
                    filetype: '',
                    liandong_field: '',
                    shuxing: ['tooltip'],
                    jdt: 'changtiao',
                    remote_research_field: '',
                    rename_status: '',
                    default_tabs_value: '',
                    application_id: '',
                    crop: '',
                    time_search_tempate: true,
                    guige: [{}],
                    maxrows: 4,
                    inputRemark: '',
                    list_feild: '',
                    rangetime_type: 'date',
                    key_placeholder: '',   // 新增key-placeholder字段
                    value_placeholder: '', // 新增value-placeholder字段
                    previewImage: 0,   // 图片预览
                },
                sql: '',
                datatype: '',
                length: '',
                belong_table: '',
                default_value: '',
                menu_id: this.menuid,

                tx_tiaojian: '', // 新增字段提醒条件类型
                tx_zhi: '',      // 新增字段提醒条件值
                tx_color: '',      // 新增字段提醒条件值
                improve_tiaojian: '',      // 完善条件
                improve_zhi: '',      // 完善条件校验值

                // 文字变色配置数组，每项包含条件类型、条件值和颜色
                tx_config: [],
                // 背景变色配置数组，每项包含条件类型、条件值和颜色
                list_background_config: [],
            },
            iconDialogStatus: false,
            activeName: 'first',
            list_item: false,
            loading: false,
            propertyField: [],
            default_config: '',
            default_rules: '',
            ruleList: [],
            tableList: [],
            application_list: [],
            dbtype: '',
            my_actions: [],
            txOptions: [
                {label: "大于", value: 1},
                {label: "小于", value: 2},
                {label: "等于", value: 3},
                {label: "大于等于", value: 4},
                {label: "小于等于", value: 5},
                {label: "包含", value: 6},
                {label: "不包含", value: 7},
                {label: "不等于", value: 8},
                {label: "为空", value: 9},
                {label: "不为空", value: 10},
            ],
            improveOptions: [
                {label: "大于", value: 1},
                {label: "小于", value: 2},
                {label: "等于", value: 3},
                {label: "大于等于", value: 4},
                {label: "小于等于", value: 5},
                {label: "不等于", value: 8},
                {label: "为空", value: 9},
                {label: "不为空", value: 10},
            ],
            rules: {
                title: [{required: true, message: '字段中文名不能为空', trigger: 'blur'}],
                field: [
                    {required: true, message: '字段英文名不能为空', trigger: 'blur'},
                    {pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许输入字母、数字和下划线', trigger: 'blur'}
                ],
                type: [{required: true, message: '字段类型不能为空', trigger: 'blur'}],
                login_fields: [{required: true, message: '请配置登录账号密码字段', trigger: 'blur'}],

                'other_config.key_placeholder': [{max: 50, message: '长度不能超过50个字符', trigger: 'blur'}],
                'other_config.value_placeholder': [{max: 50, message: '长度不能超过50个字符', trigger: 'blur'}]
            },
            tx_tiaojian: [{required: false, message: '请选择条件类型', trigger: 'change'}],
            // 'other_config.tx_zhi': [{ required: false, message: '请输入条件值', trigger: 'blur' }]
        }
    },
    methods: {
        submit() {
            // 多表反显规则
            if (this.form.create_table_field == 0 && this.form.belong_table) {
                const str = this.form.field;
                const regex = /^[a-z][a-zA-Z0-9]*(?:[A-Z][a-z0-9]*)*__.+$/;
                if (!regex.test(str)) {
                    this.$message.error(this.form.title + "的格式有误，请重新修改");
                    return;
                }
            }

            if (!this.form.tx_config) {
                this.form.tx_config = [];
            }
            if (!this.form.list_background_config) {
                this.form.list_background_config = [];
            }

            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/createField', this.form).then(res => {
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
            axios.post(base_url + '/Sys.Base/configList', {menu_id: this.menuid}).then(res => {
                this.ruleList = res.data.ruleList
                this.propertyField = res.data.propertyField
                this.dbtype = res.data.dbtype
                this.application_list = res.data.applist
                this.my_actions = res.data.my_actions
            })
        },
        selectType() {
            if (this.dbtype !== 'mongo') {
                this.field.forEach(item => {
                    if (this.form.type == item.type) {

                        if (this.form.type == 21) {
                            this.list_item = true; // 确保选项配置部分显示
                            // 可以设置键值对特定的默认值
                            this.form.other_config.key_placeholder = '请输入键名';
                            this.form.other_config.value_placeholder = '请输入值';
                        }


                        if (this.form.type == 40) {
                            this.form.create_table_field = 0
                            this.form.post_status = 0
                        }
                        if(this.form.type == 41){
                            this.form.post_status = 0
                        }
                        this.propertyField.forEach(vo => {
                            if (item.property == vo.type) {
                                this.form.datatype = vo.name
                                this.form.length = vo.decimal ? vo.maxlen + ',' + vo.decimal : vo.maxlen
                            }
                        })
                        this.list_item = item.item
                        // 添加以下代码，使得特定类型也能显示SQL配置
                        if ([2, 3, 4, 5, 30].includes(this.form.type)) { // 这些是下拉框、单选框等类型
                            this.list_item = true
                        }
                        if (!item.item && ![2, 3, 4, 5, 30].includes(this.form.type)) {
                            this.form.item_config = []
                        }
                        // 		if(!item.item){
                        // 			this.form.item_config = []
                        // 		}
                    }
                })
            } else {
                this.field.forEach(item => {
                    if (this.form.type == item.type) {
                        if (this.form.type == 40) {
                            this.form.create_table_field = 0
                            this.form.post_status = 0
                        }
                        if(this.form.type == 41){
                            this.form.post_status = 0
                        }
                        this.propertyField.forEach(vo => {
                            if (item.mongoProperty == vo.type) {
                                this.form.datatype = vo.name
                            }
                        })
                        this.list_item = item.item
                        if (!item.item) {
                            this.form.item_config = []
                        }
                    }
                })
            }
        },
        setDefaultRule() {
            this.form.rule = this.default_rules
        },
        setFieldLength() {
            if (this.dbtype !== 'mongo') {
                this.propertyField.forEach(item => {
                    if (this.form.datatype == item.name) {
                        this.form.length = item.decimal ? item.maxlen + ',' + item.decimal : item.maxlen
                    }
                })
            }
        },

        addItem(key) {
            if (!Array.isArray(this.form[key])) {
                this.$set(this.form, key, []);
            }
            // 根据不同的key初始化不同的默认配置
            const defaultConfig = {
                'tx_config': {  // 文字变色配置
                    tx_tiaojian: '',
                    tx_zhi: '',
                    tx_color: ''
                },
                'list_background_config': {  // 背景变色配置
                    tiaojian: '',
                    zhi: '',
                    color: ''
                },
                'item_config': {}  // 其他配置
            };

            const newItem = defaultConfig[key] || {};
            this.form[key].push({...newItem});
            // 使用$set添加新元素
            // this.$set(this.form[key], this.form[key].length, {});

            this.$nextTick(() => {
                this.$forceUpdate();
            });
        },
        deleteItem(key, index) {
            if (Array.isArray(this.form[key])) {
                // 使用splice删除元素会触发响应式更新
                this.form[key].splice(index, 1);
                this.$nextTick(() => {
                    this.$forceUpdate();
                });
            }
        },
        clearItem(key) {
            this.form[key] = [];
            this.$nextTick(() => {
                this.$forceUpdate();
            });
        },
        setDefaultItem(val) {
            this.form['item_config'] = val
        },
        addGuigeItem(key) {
            this.form.other_config.guige.push({})
        },
        deleteGuigeItem(key, index) {
            this.form.other_config.guige.splice(index, 1)
        },
        clearGuigeItem(key) {
            this.form.other_config.guige = []
        },
        getTablesByMenuId() {
            axios.post(base_url + '/Sys.Base/getTablesByMenuId', {menu_id: this.menuid}).then(res => {
                this.tableList = res.data.data
            })
        },
        setPostStatus() {
            this.form.post_status = 0
        },
        selectDate(val) {
            if (this.dbtype !== 'mongo') {
                if (['year', 'month', 'yearmonth', 'time', 'dates'].includes(val)) {
                    if (val == 'dates') {
                        this.form.datatype = 'text'
                        this.form.length = 0
                    } else {
                        this.form.datatype = 'varchar'
                        this.form.length = 250
                    }
                } else {
                    this.form.datatype = 'int'
                    this.form.length = 10
                }
            } else {
                if (['year', 'month', 'yearmonth', 'time', 'dates'].includes(val)) {
                    this.form.datatype = 'string'
                    this.form.length = 250
                } else {
                    this.form.datatype = 'int'
                    this.form.length = 10
                }
            }
        },
        closeForm() {
            this.$emit('update:show', false);
            this.loading = false;

            this.$nextTick(() => {
                // 先判断表单实例是否存在再重置
                if (this.$refs['form']) {
                    this.$refs['form'].resetFields();
                }

                this.default_rules = '';
                this.list_item = false;
                this.activeName = 'first';

                // 重置表单数据，确保所有数组字段正确初始化
                this.form = {
                    title: '',
                    field: '',
                    type: '',
                    post_status: 1,
                    create_table_field: 1,
                    list_show: 2,
                    validate: [],  // 确保是数组
                    item_config: [],  // 确保是数组
                    tx_config: [],  // 确保是数组
                    list_background_config: [],  // 确保是数组
                    other_config: {
                        shuxing: ['tooltip'],  // 确保是数组
                        guige: [],  // 修改为[]而非[{}]
                        key_placeholder: '',
                        value_placeholder: '',
                        // 补充其他必要的默认属性
                        address_type: 1,
                        placeholder: '',
                        liandong_field: ''
                    },
                    sql: '',
                    datatype: '',
                    length: '',
                    belong_table: '',
                    default_value: '',
                    tx_tiaojian: '',
                    tx_zhi: '',
                    tx_color: '',
                    improve_tiaojian: '',
                    improve_zhi: '',
                    menu_id: this.menuid || this.menu_id
                };
            });
        }

    },
});


//修改admin字段 TODO
Vue.component('AdminUpdate', {
    template: `
		<el-dialog title="更新字段" width="95%" class="icon-dialog" :visible.sync="show" :before-close="closeForm" @open="open"  append-to-body>
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="120px">
            <div class="field-container">
				<!-- 左侧 - 字段类型选择 -->
				<div class="field-type-sidebar">
					<div class="sidebar-header">
						<h3>字段类型</h3>
					</div>
					<div class="type-list-container">
						<el-radio-group v-model="form.type" @change="selectType" class="type-radio-group">
							<div v-for="(item,i) in field" :key="i" class="type-item">
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
								<el-form-item label="字段标题" prop="title">
									<el-input v-model="form.title" clearable placeholder="字段中文描述"  />
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="字段名称" prop="field">
									<el-input v-model="form.field" clearable placeholder="字段英文名称"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row>
							<el-col :span="12">
								<el-form-item label="字段类型" prop="type">
									<el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
										<el-option v-for="(item,i) in field" :key="i" :label="item.name" :value="item.type"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item v-if="form.type == 9 || form.type==11 || form.type == 12" label="时间模板" prop="time_search_tempate">
								   <el-checkbox v-model="form.other_config.time_search_tempate">启用时间范围搜索模板</el-checkbox>
								   <el-checkbox v-if="form.type == 9" v-model="form.other_config.now_time">默认当前时间</el-checkbox>
								</el-form-item>
								<el-form-item v-else-if="form.type == 22" label="层级" prop="address_type">
									<el-select style="width:100%" v-model="form.other_config.address_type"  placeholder="请选择级层">
										<el-option  key="0" label="省市区" value="1"></el-option>
										<el-option  key="1" label="省市" value="2"></el-option>
										<el-option  key="2" label="省" value="3"></el-option>
									</el-select>
								</el-form-item>
								<el-form-item v-else-if="form.type == 13" label="上传样式" prop="upload_type">
									<el-select style="width:100%" v-model="form.other_config.upload_type"  placeholder="上传样式">
										<el-option key="0" label="样式1(带缩略图)" value="1"></el-option>
										<el-option key="1" label="样式2(带输入框)" value="2"></el-option>
									</el-select>
								</el-form-item>
								<el-form-item v-else-if="form.type == 38" label="所属应用" prop="application_id">
									<el-select style="width:100%" v-model="form.other_config.application_id"  placeholder="所属应用">
										<el-option v-for="(item,i) in application_list" :key="i" :label="item.application_name" :value="item.app_id"></el-option>
									</el-select>
								</el-form-item>
								<el-form-item v-else-if="form.type == 31" label="长度" prop="rand_length">
									<el-input v-model="form.other_config.rand_length" clearable placeholder="随机数长度"/>
								</el-form-item>
								<el-form-item v-else label="默认值" prop="default_value">
									<el-input v-model="form.default_value" clearable placeholder="同步数据表默认值"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 8">
							<el-col :span="24">
								<el-form-item label="文本框高度" prop="maxrows">
									<el-input v-model="form.other_config.maxrows" clearable placeholder="如4 代表4行文本框高度"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 9 || form.type == 11 || form.type == 12">
							<el-form-item label="日期格式" prop="datetime_config">
								<el-select style="width:100%" :size="size" @change="selectDate" v-model="form.datetime_config" clearable  filterable placeholder="请选择日期格式">
										<el-option key="1" label="年月日时分秒" value="datetime"></el-option>
										<el-option key="2" label="年月日" value="date"></el-option>
										<el-option key="5" label="年月" value="yearmonth"></el-option>
										<el-option key="3" label="年" value="year"></el-option>
										<el-option key="4" label="月" value="month"></el-option>
										<el-option key="6" label="时分秒" value="time"></el-option>
										<el-option key="7" label="多日期" value="dates"></el-option>
										<el-option key="8" label="月/日/年" value="mdy"></el-option>
										<el-option key="9" label="月/日/年时分秒" value="mdyhis"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 10">
							<el-form-item label="日期范围格式" prop="rangetime_type">
								<el-select style="width:100%" :size="size" v-model="form.other_config.rangetime_type" clearable  filterable placeholder="请选择日期格式">
										<el-option key="1" label="年月日时分秒" value="datetime"></el-option>
										<el-option key="2" label="年月日" value="date"></el-option>
										<el-option key="3" label="时分秒" value="time"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="[13,14,15,16].includes(form.type)">
							<el-col :span="24">
								<el-form-item label="后缀格式" prop="filetype">
									<el-input v-model="form.other_config.filetype" clearable placeholder="允许上传的格式 多个逗号隔开 doc,xls,xlsx"/>
								</el-form-item>
							</el-col>
							<el-col :span="24">
								<el-form-item label="重命名" prop="rename_status">
									<el-select style="width:100%" v-model="form.other_config.rename_status" clearable placeholder="文件上传命名方式">
										<el-option key="0" label="重命名文件" value="1"></el-option>
										<el-option key="1" label="保持原名" value="2"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type == 13">
							<el-col :span="24">
								<el-form-item label="图片裁剪" prop="crop">
									<el-input v-model="form.other_config.crop" clearable placeholder="裁剪框大小 格式如 500,500 不填表示不裁剪"/>
								</el-form-item>
							</el-col>
						</el-row>
						
                        <!-- 图片预览 -->
                        <el-row>
                            <el-col v-if="[13,14].includes(form.type)" :span="24">
                                <el-form-item label="图片预览" prop="previewImage">
                                    <el-radio-group v-model="form.other_config.previewImage">
                                        <el-radio :label="1">是</el-radio>
                                        <el-radio :label="0">否</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        
						<el-row v-if="form.type == 19">
							<el-col :span="24">
								<el-form-item label="进度条样式" prop="jdt">
									<el-select style="width:100%" :size="size" v-model="form.other_config.jdt" clearable  filterable placeholder="请选择进度条显示样式">
										<el-option key="1" label="长条" value="changtiao"></el-option>
										<el-option key="2" label="圆形" value="cicle"></el-option>
								</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						
						<el-row v-if="form.type == 31">
							<el-form-item label="随机数格式" prop="form.other_config.rand_config">
								<el-select style="width:100%" :size="size" v-model="form.other_config.rand_config" clearable  filterable placeholder="请选择随机数格式">
										<el-option key="1" label="字母大小写数字组合" value="all"></el-option>
										<el-option key="2" label="字母大小写组合" value="letter"></el-option>
										<el-option key="3" label="纯数字组合" value="number"></el-option>
								</el-select>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 39">
							<el-form-item label="规格配置" prop="guige">
								<draggable v-model="form.other_config.guige" v-bind="{group:'item'}" handle=".jzd-handle">
								<el-row v-for="(item,i) in form.other_config.guige" :key="i">
									<el-col :span="4">
										<el-form-item style="margin-bottom:3px !important">
											<el-input  v-model="item.title" placeholder="规格名称"/>
										</el-form-item>
									</el-col>
									<el-col :span="4">
										<el-form-item style="margin-bottom:3px !important">
											<el-input style="position:relative;left:5px;" v-model="item.field" placeholder="规格字段名"/>
										</el-form-item>
									</el-col>
									<el-col :span="5">
										<el-form-item style="margin-bottom:3px !important">
											<el-select style="position:relative;left:10px;" v-model="item.type" size="small" clearable placeholder="字段类型">
												<el-option key="1" label="文本框" value="1"></el-option>
												<el-option key="2" label="下拉框" value="2"></el-option>
												<el-option key="3" label="图片" value="13"></el-option>
												<el-option key="4" label="开关按钮" value="6"></el-option>
												<el-option key="5" label="日期框" value="9"></el-option>
												<el-option key="6" label="计数器" value="17"></el-option>
												<el-option key="7" label="标签" value="18"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :span="7">
										<el-form-item style="margin-bottom:3px !important">
											<el-input style="position:relative;left:15px;" v-model="item.item" placeholder="选项配置,多个逗号隔开"/>
										</el-form-item>
									</el-col>
									<el-col :span="4">
										<el-button type="danger" size="mini" style="position:relative;left:16px"  icon="el-icon-close" @click="deleteGuigeItem('item_config',i)"></el-button>
										<el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:12px" icon="el-icon-rank"></el-button>
									</el-col>
								</el-row>
								</draggable>
								<div>
									<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addGuigeItem('item_config')">追加</el-button>
									<el-button v-if="form.item_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearGuigeItem('item_config')">清空</el-button>
								</div>
							</el-form-item>
						</el-row>
						<el-row v-if="form.type == 40">
							<el-col :span="24">
								<el-form-item label="计算公式" prop="jisuan">
									<el-input v-model="form.other_config.jisuan" clearable placeholder="原生php语法计算公式，如 num * price"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="list_item">
							<el-form-item label="选项配置" prop="item_config">
								<draggable v-model="form.item_config" v-bind="{group:'item'}" handle=".jzd-handle">
								<el-row v-for="(item,i) in form.item_config" :key="i">
									<el-col :span="7">
										<el-form-item style="margin-bottom:3px !important">
											<el-input  v-model="item.key" placeholder="选项名称"/>
										</el-form-item>
									</el-col>
									<el-col :span="6">
										<el-form-item style="margin-bottom:3px !important">
											<el-input style="position:relative;left:5px;" v-model="item.val" placeholder="选项值"/>
										</el-form-item>
									</el-col>
									<el-col :span="6">
										<el-form-item style="margin-bottom:3px !important">
											<el-select style="position:relative;left:10px;" v-model="item.label_color" size="small" clearable placeholder="请选择背景色">
												<el-option key="1" style="background:#409eff" label="primary" value="primary"></el-option>
												<el-option key="2" style="background:#67c23a" label="success" value="success"></el-option>
												<el-option key="3" style="background:#909399" label="info" value="info"></el-option>
												<el-option key="4" style="background:#e6a23c" label="warning" value="warning"></el-option>
												<el-option key="5" style="background:#f56c6c" label="danger" value="danger"></el-option>
												<el-option key="6" style="background:#5ed84f" label="fresh-green" value="fresh-green"></el-option>
												<el-option key="7" style="background:#353640" label="deep-space" value="deep-space"></el-option>
												<el-option key="8" style="background:#6b6f80" label="cool-gray" value="cool-gray"></el-option>
												<el-option key="9" style="background:#6967ce" label="violet" value="violet"></el-option>
												<el-option key="10" style="background:#28afd0" label="aqua" value="aqua"></el-option>
												<el-option key="11" style="background:#fdb901" label="golden" value="golden"></el-option>
												<el-option key="12" style="background:#fa626b" label="coral" value="coral"></el-option>
												<el-option key="13" style="background:#babfc7" label="silver" value="silver"></el-option>
												<el-option key="14" style="background:#53a8ff" label="sky-blue" value="sky-blue"></el-option>
												<el-option key="15" style="background:#85ce61" label="leaf-green" value="leaf-green"></el-option>
												<el-option key="16" style="background:#a6a9ad" label="mist-gray" value="mist-gray"></el-option>
												<el-option key="17" style="background:#eebe77" label="honey" value="honey"></el-option>
												<el-option key="18" style="background:#f78989" label="blossom" value="blossom"></el-option>
												<el-option key="19" style="background:#3d4b5c" label="steel" value="steel"></el-option>
												<el-option key="20" style="background:#7e57c2" label="amethyst" value="amethyst"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :span="4">
										<el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem('item_config',i)"></el-button>
										<el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:12px" icon="el-icon-rank"></el-button>
									</el-col>
								</el-row>
								</draggable>
								<div>
									<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('item_config')">追加</el-button>
									<el-button v-if="form.item_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('item_config')">清空</el-button>
									<el-select v-if="form.item_config.length === 0"  :size="size" style="height:25px; light:25px; margin-left:20px;" v-model="default_config" @change="setDefaultItem"  placeholder="请选择默认配置">
										<el-option v-for="(item,i) in item_field" :key="i" :label="item.name" :value="item.item"></el-option>
									</el-select>
								</div>
							</el-form-item>
						</el-row>
						
						<el-row v-if="(list_item || [2,3,4,5,30,33,41].includes(form.type)) && dbtype !== 'mongo'">
							<el-form-item label="sql数据源" prop="sql">
								<el-input type="textarea" v-model="form.sql" clearable placeholder="单选/下拉/多选选项 sql数据源"/>
							</el-form-item>
						</el-row>
						<el-row v-if="(list_item || [41].includes(form.type)) && dbtype !== 'mongo'">
                            <el-form-item label="记已读的session" prop="crop">
                                <el-input v-model="form.other_config.yidufield" clearable placeholder="填写要记已读的session字段"/>
                            </el-form-item>
						</el-row>
						<el-row v-if="[2,4].includes(form.type) && dbtype !== 'mongo'">
							<el-form-item label="联动字段" prop="liandong_field">
								<el-input v-model="form.other_config.liandong_field" clearable placeholder="二级联动字段名"/>
							</el-form-item>
						</el-row>
						<el-row>
							<el-col v-if="dbtype !== 'mongo'" :span="12">
								<el-form-item label="更新字段" prop="create_table_field">
									<el-radio-group v-model="form.create_table_field">
										<el-radio :label="1">是</el-radio>
										<el-radio :label="0">否</el-radio>
									</el-radio-group>
								</el-form-item>
							</el-col>
							<el-col :span="dbtype !== 'mongo' ? 12:24">
								 <el-form-item label="显示状态" prop="list_show">
									<el-select style="width:100%" v-model="form.list_show" :size="size" filterable placeholder="请选择">
										<el-option-group label="显示状态">
											<el-option key="1" label="不显示" :value="0"></el-option>
										</el-option-group>
										<el-option-group label="显示位置">
											<el-option key="3" label="居中" :value="2"></el-option>
											<el-option key="4" label="居左" :value="3"></el-option>
											<el-option key="5" label="居右" :value="4"></el-option>
										</el-option-group>
									</el-select>
								 </el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.create_table_field == 0 && dbtype !== 'mongo' && form.type != 40">
							<el-col :span="24">
								<el-form-item label="所属表" prop="belong_table">
									<el-select @focus="getTablesByMenuId" @change="setPostStatus" style="width:100%" clearable v-model="form.belong_table" filterable  placeholder="关联字段所属表（配置多表专属，其它勿设置）">
										<el-option v-for="(item,i) in tableList" :key="i" :value="item.table_name">{{item.table_name}}({{item.title}})</el-option>
									</el-select>
									<label style="color:red;font-size:13px;display:block;">
                                        关联字段规则：关联字段小驼峰__需要反显字段<br>
                                        示例：memberId__member_name
									</label>
								</el-form-item>
							</el-col>
						</el-row>
						 <el-row v-if="form.type != 40">
							<el-col :span="12">
								<el-form-item label="验证方式" prop="validate">
									<el-checkbox-group v-model="form.validate">
										<el-checkbox label="notempty">不为空</el-checkbox>
										<el-checkbox label="unique">唯一</el-checkbox>
									</el-checkbox-group>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="可录入" prop="post_status">
									<el-radio v-model="form.post_status" :label="1">是</el-radio>
									<el-radio v-model="form.post_status" :label="0">否</el-radio>
								</el-form-item>
							</el-col>
						</el-row>
						
                        <el-row v-if="form.type == 21">
                          <el-col :span="12">
                            <el-form-item label="键占位文本" prop="key_placeholder">
                              <el-input v-model="form.other_config.key_placeholder" clearable placeholder="键输入框的占位文本"/>
                            </el-form-item>
                          </el-col>
                          <el-col :span="12">
                            <el-form-item label="值占位文本" prop="value_placeholder">
                              <el-input v-model="form.other_config.value_placeholder" clearable placeholder="值输入框的占位文本"/>
                            </el-form-item>
                          </el-col>
                        </el-row>
                        
						<el-row v-if="form.type != 40">
							<el-col :span="20">
								<el-form-item label="验证规则" prop="rule">
									<el-input v-model="form.rule" clearable placeholder="输入框验证规则"/>
								</el-form-item>
							</el-col>
							<el-col :span="4">
								<el-select :size="size" v-model="default_rules" @change="setDefaultRule" prop="default_rules" clearable filterable placeholder="请选择">
									<el-option v-for="(item,index) in ruleList" :key="index" :label="index" :value="item"></el-option>
								</el-select>
							</el-col>
						</el-row>
						<el-row v-if="form.type != 40">
							<el-col :span="8">
								<el-form-item label="数据结构" prop="datatype">
									<el-select v-model="form.datatype" filterable @change="setFieldLength"  placeholder="请选择">
										<el-option v-for="(item,i) in propertyField" :key="i" :label="item.name" :value="item.name"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
							<el-col v-if="dbtype !== 'mongo'" :span="8">
								<el-form-item label="字段长度" prop="length">
									<el-input v-model="form.length" placeholder="字段长度"/>
								</el-form-item>
							</el-col>
							<el-col :span="8">
								<el-form-item label="字段索引" prop="indexdata">
									<el-select v-model="form.indexdata" filterable placeholder="请选择">
										<el-option key="1" label="普通索引" value="index"></el-option>
										<el-option key="2" label="唯一索引" value="unique"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
					</div>
				</div>
				<!-- 右侧 - 拓展信息 -->
				<div class="extended-info-section">
					<div class="section-header">
						<h3>拓展信息</h3>
					</div>
					<div class="section-content">
						<el-row v-if="form.type ==1 || form.type == 17">
							<el-col :span="12">
								<el-form-item label="最大输入" prop="input_length">
									<el-input v-model="form.other_config.input_length" placeholder="请输入长度">
										<el-button type="success" slot="append">个字符</el-button>
									</el-input>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="背景色" prop="label_color">
									<el-select style="width:100%" v-model="form.other_config.label_color" :size="size" clearable filterable placeholder="请选择">
										<el-option key="1" style="background:#409eff" label="primary" value="primary"></el-option>
										<el-option key="2" style="background:#67c23a" label="success" value="success"></el-option>
										<el-option key="3" style="background:#909399" label="info" value="info"></el-option>
										<el-option key="4" style="background:#e6a23c" label="warning" value="warning"></el-option>
										<el-option key="5" style="background:#f56c6c" label="danger" value="danger"></el-option>
										<el-option key="6" style="background:#5ed84f" label="fresh-green" value="fresh-green"></el-option>
										<el-option key="7" style="background:#353640" label="deep-space" value="deep-space"></el-option>
										<el-option key="8" style="background:#6b6f80" label="cool-gray" value="cool-gray"></el-option>
										<el-option key="9" style="background:#6967ce" label="violet" value="violet"></el-option>
										<el-option key="10" style="background:#28afd0" label="aqua" value="aqua"></el-option>
										<el-option key="11" style="background:#fdb901" label="golden" value="golden"></el-option>
										<el-option key="12" style="background:#fa626b" label="coral" value="coral"></el-option>
										<el-option key="13" style="background:#babfc7" label="silver" value="silver"></el-option>
										<el-option key="14" style="background:#53a8ff" label="sky-blue" value="sky-blue"></el-option>
										<el-option key="15" style="background:#85ce61" label="leaf-green" value="leaf-green"></el-option>
										<el-option key="16" style="background:#a6a9ad" label="mist-gray" value="mist-gray"></el-option>
										<el-option key="17" style="background:#eebe77" label="honey" value="honey"></el-option>
										<el-option key="18" style="background:#f78989" label="blossom" value="blossom"></el-option>
										<el-option key="19" style="background:#3d4b5c" label="steel" value="steel"></el-option>
										<el-option key="20" style="background:#7e57c2" label="amethyst" value="amethyst"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type ==1">
							<el-col :span="12">
								<el-form-item label="输入前缀" prop="prefix">
									<el-input v-model="form.other_config.prefix" placeholder="请输入前缀"/>
								</el-form-item>
							</el-col>
							<el-col :span="12">
								<el-form-item label="输入后缀" prop="afterfix">
									<el-input v-model="form.other_config.afterfix" placeholder="请输入后缀"/>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row v-if="form.type ==1 || form.type == 8 || form.type == 17">
							<el-form-item label="前置图标" prop="pre_icon">
								<el-input v-model="form.icon" placeholder="点击选择图标" clearable>
									<el-button type="success" slot="append" icon="el-icon-thumb"  @click="iconDialogStatus = true">请选择</el-button>
								</el-input>
							</el-form-item>
						</el-row>
						<el-row>
							<el-form-item label="提示文本" prop="placeholder">
								<el-input v-model="form.other_config.placeholder" clearable placeholder="请输入文本框placeholder"/>
							</el-form-item>
						</el-row>
						<el-row>
							<el-form-item label="备注说明" prop="inputRemark">
								<el-input v-model="form.other_config.inputRemark" clearable placeholder="输入框下方的备注说明文字"/>
							</el-form-item>
						</el-row>
						<el-row v-if="(form.type == 2 || form.type == 3) && form.sql !== ''">
							<el-form-item label="远程搜索字段" prop="remote_research_field">
								<el-input v-model="form.other_config.remote_research_field" clearable placeholder="设置目标数据表需要远程搜索的字段名"/>
							</el-form-item>
						</el-row>
						<el-row>
							<el-form-item label="显示条件" prop="show_condition">
								<el-input v-model="form.show_condition" clearable placeholder="输入框显示条件 如 form.status == 1"/>
							</el-form-item>
						</el-row>
						
					 <!-- 新增的列表文字变色设置 -->
					    <el-row>
							<el-form-item label="列表文字变色" prop="tx_config" v-if="Array.isArray(form.tx_config)">
                                    <el-row v-for="(item,i) in form.tx_config" :key="i">
                                    <el-col :span="5">
                                            <el-form-item prop="tx_tiaojian">
                                                <el-select v-model="item.tx_tiaojian" clearable filterable placeholder="条件类型" style="width: 100%;">
                                                    <el-option
                                                        v-for="opt in txOptions"
                                                        :key="opt.value"
                                                        :label="opt.label"
                                                        :value="opt.value">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="10">
                                            <el-form-item prop="tx_zhi">
                                                <el-input v-model="item.tx_zhi" clearable placeholder="输入条件值，如 1 或 “{:session('admin.yuangong_id')}”"/>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="5">
                                            <el-form-item prop="tx_color" >
                                                <el-select style="width:100%" v-model="item.tx_color" :size="size" clearable filterable placeholder="颜色">
                                                    <el-option key="1" style="background:#409eff" label="primary" value="#409eff"></el-option>
                                                    <el-option key="2" style="background:#67c23a" label="success" value="#67c23a"></el-option>
                                                    <el-option key="3" style="background:#909399" label="info" value="#909399"></el-option>
                                                    <el-option key="4" style="background:#e6a23c" label="warning" value="#e6a23c"></el-option>
                                                    <el-option key="5" style="background:#f56c6c" label="danger" value="#f56c6c"></el-option>
                                                    <el-option key="6" style="background:#5ed84f" label="fresh-green" value="#5ed84f"></el-option>
                                                    <el-option key="7" style="background:#353640" label="deep-space" value="#353640"></el-option>
                                                    <el-option key="8" style="background:#6b6f80" label="cool-gray" value="#6b6f80"></el-option>
                                                    <el-option key="9" style="background:#6967ce" label="violet" value="#6967ce"></el-option>
                                                    <el-option key="10" style="background:#28afd0" label="aqua" value="#28afd0"></el-option>
                                                    <el-option key="11" style="background:#fdb901" label="golden" value="#fdb901"></el-option>
                                                    <el-option key="12" style="background:#fa626b" label="coral" value="#fa626b"></el-option>
                                                    <el-option key="13" style="background:#babfc7" label="silver" value="#babfc7"></el-option>
                                                    <el-option key="14" style="background:#53a8ff" label="sky-blue" value="#53a8ff"></el-option>
                                                    <el-option key="15" style="background:#85ce61" label="leaf-green" value="#85ce61"></el-option>
                                                    <el-option key="16" style="background:#a6a9ad" label="mist-gray" value="#a6a9ad"></el-option>
                                                    <el-option key="17" style="background:#eebe77" label="honey" value="#eebe77"></el-option>
                                                    <el-option key="18" style="background:#f78989" label="blossom" value="#f78989"></el-option>
                                                    <el-option key="19" style="background:#3d4b5c" label="steel" value="#3d4b5c"></el-option>
                                                    <el-option key="20" style="background:#7e57c2" label="amethyst" value="#7e57c2"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="2">
                                            <el-button type="danger" size="mini" style="position:relative;left:10px"  icon="el-icon-close" @click="deleteItem('tx_config',i)"></el-button>
                                        </el-col>
                                    </el-row>
								<div>
									<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('tx_config')">追加</el-button>
									<el-button v-if="form.tx_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('tx_config')">清空</el-button>
								</div>
							</el-form-item>
						</el-row>
					
				
						    <el-row>
							<el-form-item label="列表背景变色" prop="list_background_config" v-if="Array.isArray(form.list_background_config)">
                                    <el-row v-for="(background_config_item,i) in form.list_background_config" :key="i + 10086">
                                    <el-col :span="5">
                                            <el-form-item prop="tiaojian">
                                                <el-select v-model="background_config_item.tiaojian" clearable filterable placeholder="条件类型" style="width: 100%;">
                                                    <el-option
                                                        v-for="opt in txOptions"
                                                        :key="opt.value"
                                                        :label="opt.label"
                                                        :value="opt.value">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="10">
                                            <el-form-item prop="zhi">
                                                <el-input v-model="background_config_item.zhi" clearable placeholder="输入条件值，如 1 或 “{:session('admin.yuangong_id')}”"/>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="5">
                                            <el-form-item prop="color" >
                                                <el-select style="width:100%" v-model="background_config_item.color" :size="size" clearable filterable placeholder="颜色">
                                                    <el-option key="1" style="background:#409eff" label="primary" value="#409eff"></el-option>
                                                    <el-option key="2" style="background:#67c23a" label="success" value="#67c23a"></el-option>
                                                    <el-option key="3" style="background:#909399" label="info" value="#909399"></el-option>
                                                    <el-option key="4" style="background:#e6a23c" label="warning" value="#e6a23c"></el-option>
                                                    <el-option key="5" style="background:#f56c6c" label="danger" value="#f56c6c"></el-option>
                                                    <el-option key="6" style="background:#5ed84f" label="fresh-green" value="#5ed84f"></el-option>
                                                    <el-option key="7" style="background:#353640" label="deep-space" value="#353640"></el-option>
                                                    <el-option key="8" style="background:#6b6f80" label="cool-gray" value="#6b6f80"></el-option>
                                                    <el-option key="9" style="background:#6967ce" label="violet" value="#6967ce"></el-option>
                                                    <el-option key="10" style="background:#28afd0" label="aqua" value="#28afd0"></el-option>
                                                    <el-option key="11" style="background:#fdb901" label="golden" value="#fdb901"></el-option>
                                                    <el-option key="12" style="background:#fa626b" label="coral" value="#fa626b"></el-option>
                                                    <el-option key="13" style="background:#babfc7" label="silver" value="#babfc7"></el-option>
                                                    <el-option key="14" style="background:#53a8ff" label="sky-blue" value="#53a8ff"></el-option>
                                                    <el-option key="15" style="background:#85ce61" label="leaf-green" value="#85ce61"></el-option>
                                                    <el-option key="16" style="background:#a6a9ad" label="mist-gray" value="#a6a9ad"></el-option>
                                                    <el-option key="17" style="background:#eebe77" label="honey" value="#eebe77"></el-option>
                                                    <el-option key="18" style="background:#f78989" label="blossom" value="#f78989"></el-option>
                                                    <el-option key="19" style="background:#3d4b5c" label="steel" value="#3d4b5c"></el-option>
                                                    <el-option key="20" style="background:#7e57c2" label="amethyst" value="#7e57c2"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="2">
                                            <el-button type="danger" size="mini" style="position:relative;left:10px"  icon="el-icon-close" @click="deleteItem('list_background_config',i)"></el-button>
                                        </el-col>
                                    </el-row>
								<div>
									<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('list_background_config')">追加</el-button>
									<el-button v-if="form.list_background_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('list_background_config')">清空</el-button>
								</div>
							</el-form-item>
						</el-row>
					
						
						
                        <!-- 新增的字段完善提醒设置 -->
						<el-row>
							<el-form-item label="字段完善提醒">
								<el-col :span="6">
									<el-form-item prop="improve_tiaojian">
										<el-select v-model="form.improve_tiaojian" placeholder="选择条件类型" style="width: 100%;">
                                             <el-option
                                                v-for="opt in improveOptions"
                                                :key="opt.value"
                                                :label="opt.label"
                                                :value="opt.value">
                                            </el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :span="18">
									<el-form-item prop="improve_zhi">
										<el-input v-model="form.improve_zhi" clearable placeholder="输入条件值，如 1 或 session('admin.yuangong_id')"/>
									</el-form-item>
								</el-col>
							</el-form-item>
						</el-row>
						
						
						<el-row>
							<el-form-item label="列表字段" prop="list_feild">
								<el-input v-model="form.other_config.list_feild" clearable placeholder="请输入列表展示在哪个字段"/>
							</el-form-item>
						</el-row>
						
						<el-row>
							<el-form-item label="其他属性" prop="shuxing">
								<el-checkbox-group v-model="form.other_config.shuxing">
									<el-checkbox key="0" label="tooltip">悬停提示</el-checkbox>
									<el-checkbox key="1" label="sort">表头排序</el-checkbox>
									<el-checkbox key="2" label="sum">字段汇总求和</el-checkbox>
									<el-checkbox v-if="[2,3,4,5].includes(form.type)" key="3" label="tabs">列表选项卡</el-checkbox>
									<el-checkbox v-if="[2,3,4,5,35,30,33,41].includes(form.type) && form.sql != ''" key="4" label="fanzhuan">数据显示反转</el-checkbox>
									<el-checkbox v-if="![13,14,15,16,21,39,25,26,27,38,30].includes(form.type)" key="5" label="readonly">编辑只读</el-checkbox>
								</el-checkbox-group>
							</el-form-item>
						</el-row>
						
						
						<el-row>
							<el-form-item label="列表方法" prop="list_action">
							    <el-select v-model="form.other_config.list_action" clearable placeholder="选择在列表触发的方法" style="width: 100%;">
                                    <el-option
                                    v-for="(item,index) in my_actions"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.action_name"
                                    ></el-option>
                                </el-select>
							</el-form-item>
						</el-row>
						
						
						<el-row v-if="form.other_config.shuxing.includes('tabs')">
							<el-form-item label="默认值" prop="default_tabs_value">
								<el-input v-model="form.other_config.default_tabs_value" clearable placeholder="选项卡选中默认值"/>
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
            default: 'small'
        },
        menu_id: {
            type: String,
        },
        field: {
            type: Array,
        },
        item_field: {
            type: Array,
        },
        info: {
            type: Object,
        },
    },
    data() {
        return {
            form: {
                title: '',
                field: '',
                type: '',
                post_status: 1,
                create_table_field: 1,
                list_show: 2,
                validate: [],
                item_config: [],
                other_config: {
                    address_type: 1,
                    now_time: false,
                    placeholder: '',
                    liandong_field: '',
                    shuxing: [],
                    jdt: '',
                    remote_research_field: '',
                    rename_status: '',
                    default_tabs_value: '',
                    application_id: '',
                    crop: '',
                    time_search_tempate: true,
                    guige: [{}],
                    maxrows: 4,
                    inputRemark: '',
                    list_feild: '',
                    key_placeholder: '',   // 新增key-placeholder字段
                    value_placeholder: '', // 新增value-placeholder字段
                    previewImage: 0,   // 图片预览
                },
                sql: '',
                datatype: '',
                length: '',
                belong_table: '',
                default_value: '',

                tx_tiaojian: '', // 新增字段提醒条件类型
                tx_zhi: '',      // 新增字段提醒条件值
                tx_color: '',      // 新增字段提醒条件值
                improve_tiaojian: '',      // 完善条件
                improve_zhi: '',      // 完善条件校验值

                // 文字变色配置数组，每项包含条件类型、条件值和颜色
                tx_config: [],
                // 背景变色配置数组，每项包含条件类型、条件值和颜色
                list_background_config: [],
            },
            iconDialogStatus: false,
            activeName: 'first',
            list_item: false,
            loading: false,
            propertyField: [],
            default_config: '',
            default_rules: '',
            ruleList: [],
            tableList: [],
            application_list: [],
            my_actions: [],
            txOptions: [
                {label: "大于", value: 1},
                {label: "小于", value: 2},
                {label: "等于", value: 3},
                {label: "大于等于", value: 4},
                {label: "小于等于", value: 5},
                {label: "包含", value: 6},
                {label: "不包含", value: 7},
                {label: "不等于", value: 8},
                {label: "为空", value: 9},
                {label: "不为空", value: 10},
            ],
            improveOptions: [
                {label: "大于", value: 1},
                {label: "小于", value: 2},
                {label: "等于", value: 3},
                {label: "大于等于", value: 4},
                {label: "小于等于", value: 5},
                {label: "不等于", value: 8},
                {label: "为空", value: 9},
                {label: "不为空", value: 10},
            ],
            dbtype: '',
            rules: {
                title: [{required: true, message: '字段中文名不能为空', trigger: 'blur'}],
                field: [
                    {required: true, message: '字段英文名不能为空', trigger: 'blur'},
                    {pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许输入字母、数字和下划线', trigger: 'blur'}
                ],
                type: [{required: true, message: '字段类型不能为空', trigger: 'blur'}],
                login_fields: [{required: true, message: '请配置登录账号密码字段', trigger: 'blur'}],
                'other_config.key_placeholder': [{max: 50, message: '长度不能超过50个字符', trigger: 'blur'}],
                'other_config.value_placeholder': [{max: 50, message: '长度不能超过50个字符', trigger: 'blur'}],
                tx_tiaojian: [{required: false, message: '请选择条件类型', trigger: 'change'}],
                // 'other_config.tx_zhi': [{required: false, message: '请输入条件值', trigger: 'blur'}],
            },
        }
    },
    methods: {
        submit() {
            if (this.form.create_table_field == 0 && this.form.belong_table) {
                const str = this.form.field;
                const regex = /^[a-z][a-zA-Z0-9]*(?:[A-Z][a-z0-9]*)*__.+$/;
                if (!regex.test(str)) {
                    this.$message.error(this.form.title + "的格式有误，请重新修改");
                    return;
                }
            }

            if (!this.form.tx_config) {
                this.form.tx_config = [];
            }
            if (!this.form.list_background_config) {
                this.form.list_background_config = [];
            }

            this.$refs['form'].validate(valid => {
                if (valid) {
                    // 将键值对占位符保存到主表字段
                    this.form.key_placeholder = this.form.other_config.key_placeholder
                    this.form.value_placeholder = this.form.other_config.value_placeholder
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/updateField', this.form).then(res => {
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
            // 1. 初始化表单数据，深拷贝避免引用问题
            this.form = this.info ? JSON.parse(JSON.stringify(this.info)) : {};

            // 2. 处理数组字段的初始化和类型转换
            const processArrayField = (fieldName) => {
                // 如果字段不存在、为空字符串或空JSON表示，初始化为空数组
                if (!this.form[fieldName] || this.form[fieldName] === '[]' || this.form[fieldName] === '{}') {
                    this.form[fieldName] = [];
                }
                // 如果是字符串类型，尝试解析为JSON数组
                else if (typeof this.form[fieldName] === 'string') {
                    try {
                        this.form[fieldName] = JSON.parse(this.form[fieldName]);
                        // 确保解析后是数组类型
                        if (!Array.isArray(this.form[fieldName])) {
                            this.form[fieldName] = [];
                        }
                    } catch (e) {
                        console.warn(`解析${fieldName}失败，初始化为空数组`, e);
                        this.form[fieldName] = [];
                    }
                }
                // 如果不是数组类型，强制转换为空数组
                else if (!Array.isArray(this.form[fieldName])) {
                    this.form[fieldName] = [];
                }
            };

            // 处理所有需要初始化的数组字段
            ['item_config', 'tx_config', 'list_background_config'].forEach(processArrayField);

            // 3. 处理other_config对象
            if (!this.form.other_config || this.form.other_config === '[]' || this.form.other_config === '{}') {
                // 初始化空的other_config
                this.form.other_config = {
                    shuxing: ['tooltip'],
                    guige: [],
                    key_placeholder: this.info?.key_placeholder || '',
                    value_placeholder: this.info?.value_placeholder || '',
                };
            } else if (typeof this.form.other_config === 'string') {
                // 解析字符串类型的other_config
                try {
                    this.form.other_config = JSON.parse(this.form.other_config);
                } catch (e) {
                    console.warn('解析other_config失败，使用默认配置', e);
                    this.form.other_config = {
                        shuxing: ['tooltip'],
                        guige: [],
                        key_placeholder: '',
                        value_placeholder: '',
                    };
                }
            }

            // 确保other_config中的必要字段存在且类型正确
            this.form.other_config = {
                ...this.form.other_config,
                shuxing: Array.isArray(this.form.other_config.shuxing)
                    ? this.form.other_config.shuxing
                    : ['tooltip'],
                guige: Array.isArray(this.form.other_config.guige)
                    ? this.form.other_config.guige
                    : [],
                key_placeholder: this.form.other_config.key_placeholder || this.info?.key_placeholder || '',
                value_placeholder: this.form.other_config.value_placeholder || this.info?.value_placeholder || '',
            };

            // 4. 设置列表项状态
            this.list_item = this.field.some(item => item.type === this.form.type && item.item);

            // 5. 加载配置列表
            axios.post(base_url + '/Sys.Base/configList', {
                menu_id: this.menuid || this.menu_id
            }).then(res => {
                this.ruleList = res.data.ruleList || [];
                this.propertyField = res.data.propertyField || [];
                this.dbtype = res.data.dbtype || '';
                this.application_list = res.data.applist || [];
                this.my_actions = res.data.my_actions || [];
            }).catch(error => {
                console.error('加载配置列表失败', error);
                // 可以在这里添加错误处理，如显示错误提示
            });
        },
        selectType() {
            if (this.dbtype !== 'mongo') {
                this.field.forEach(item => {
                    if (this.form.type == item.type) {

                        if (this.form.type == 21) {
                            this.list_item = true; // 确保选项配置部分显示
                            if (!this.form.other_config.key_placeholder) {
                                this.form.other_config.key_placeholder = '请输入键名'
                            }
                            if (!this.form.other_config.value_placeholder) {
                                this.form.other_config.value_placeholder = '请输入值'
                            }
                        }


                        if (this.form.type == 40) {
                            this.form.create_table_field = 0
                            this.form.post_status = 0
                        }
                        if(this.form.type == 41){
                            this.form.post_status = 0
                        }
                        this.propertyField.forEach(vo => {
                            if (item.property == vo.type) {
                                this.form.datatype = vo.name
                                this.form.length = vo.decimal ? vo.maxlen + ',' + vo.decimal : vo.maxlen
                            }
                        })
                        this.list_item = item.item
                        // 添加以下代码，使得特定类型也能显示SQL配置
                        if ([2, 3, 4, 5, 30].includes(this.form.type)) { // 这些是下拉框、单选框等类型
                            this.list_item = true
                        }
                        if (!item.item && ![2, 3, 4, 5, 30].includes(this.form.type)) {
                            this.form.item_config = []
                        }
                        // 		if(!item.item){
                        // 			this.form.item_config = []
                        // 		}
                    }
                })
            } else {
                this.field.forEach(item => {
                    if (this.form.type == item.type) {
                        if (this.form.type == 40) {
                            this.form.create_table_field = 0
                            this.form.post_status = 0
                        }
                        if(this.form.type == 41){
                            this.form.post_status = 0
                        }
                        this.propertyField.forEach(vo => {
                            if (item.mongoProperty == vo.type) {
                                this.form.datatype = vo.name
                            }
                        })
                        this.list_item = item.item
                        if (!item.item) {
                            this.form.item_config = []
                        }
                    }
                })
            }
        },
        setDefaultVal(key) {
            if (this.form[key] == null || this.form[key] == '') {
                this.form[key] = []
            }
        },
        setDefaultRule() {
            this.form.rule = this.default_rules
        },
        setFieldLength() {
            if (this.dbtype !== 'mongo') {
                this.propertyField.forEach(item => {
                    if (this.form.datatype == item.name) {
                        this.form.length = item.decimal ? item.maxlen + ',' + item.decimal : item.maxlen
                    }
                })
            }
        },
        addItem(key) {
            if (!Array.isArray(this.form[key])) {
                this.$set(this.form, key, []);
            }
            // 根据不同的key初始化不同的默认配置
            const defaultConfig = {
                'tx_config': {  // 文字变色配置
                    tx_tiaojian: '',
                    tx_zhi: '',
                    tx_color: ''
                },
                'list_background_config': {  // 背景变色配置
                    tiaojian: '',
                    zhi: '',
                    color: ''
                },
                'item_config': {}  // 其他配置
            };

            const newItem = defaultConfig[key] || {};
            this.form[key].push({...newItem});
            // 使用$set添加新元素
            // this.$set(this.form[key], this.form[key].length, {});

            this.$nextTick(() => {
                this.$forceUpdate();
            });
        },
        deleteItem(key, index) {
            if (Array.isArray(this.form[key])) {
                // 使用splice删除元素会触发响应式更新
                this.form[key].splice(index, 1);
                this.$nextTick(() => {
                    this.$forceUpdate();
                });
            }
        },
        clearItem(key) {
            this.form[key] = [];
            this.$nextTick(() => {
                this.$forceUpdate();
            });
        },
        setDefaultItem(val) {
            this.form['item_config'] = val
        },
        addGuigeItem(key) {
            this.form.other_config.guige.push({})
        },
        deleteGuigeItem(key, index) {
            this.form.other_config.guige.splice(index, 1)
        },
        clearGuigeItem(key) {
            this.form.other_config.guige = []
        },
        getTablesByMenuId() {
            axios.post(base_url + '/Sys.Base/getTablesByMenuId', {menu_id: this.menu_id}).then(res => {
                this.tableList = res.data.data
            })
        },
        setPostStatus() {
            this.form.post_status = 0
        },
        selectDate(val) {
            if (this.dbtype !== 'mongo') {
                if (['year', 'month', 'yearmonth', 'time', 'dates'].includes(val)) {
                    if (val == 'dates') {
                        this.form.datatype = 'text'
                        this.form.length = 0
                    } else {
                        this.form.datatype = 'varchar'
                        this.form.length = 250
                    }
                } else {
                    this.form.datatype = 'int'
                    this.form.length = 10
                }
            } else {
                if (['year', 'month', 'yearmonth', 'time', 'dates'].includes(val)) {
                    this.form.datatype = 'string'
                    this.form.length = 250
                } else {
                    this.form.datatype = 'int'
                    this.form.length = 10
                }
            }
        },
        closeForm() {
            this.$emit('update:show', false);
            this.loading = false;

            this.$nextTick(() => {
                // 先判断表单实例是否存在再重置
                if (this.$refs['form']) {
                    this.$refs['form'].resetFields();
                }

                this.default_rules = '';
                this.list_item = false;
                this.activeName = 'first';

                // 重置表单数据，确保所有数组字段正确初始化
                this.form = {
                    title: '',
                    field: '',
                    type: '',
                    post_status: 1,
                    create_table_field: 1,
                    list_show: 2,
                    validate: [],  // 确保是数组
                    item_config: [],  // 确保是数组
                    tx_config: [],  // 确保是数组
                    list_background_config: [],  // 确保是数组
                    other_config: {
                        shuxing: ['tooltip'],  // 确保是数组
                        guige: [],  // 修改为[]而非[{}]
                        key_placeholder: '',
                        value_placeholder: '',
                        // 补充其他必要的默认属性
                        address_type: 1,
                        placeholder: '',
                        liandong_field: ''
                    },
                    sql: '',
                    datatype: '',
                    length: '',
                    belong_table: '',
                    default_value: '',
                    tx_tiaojian: '',
                    tx_zhi: '',
                    tx_color: '',
                    improve_tiaojian: '',
                    improve_zhi: '',
                    menu_id: this.menuid || this.menu_id
                };
            });
        }

    },
});

//添加api字段
Vue.component('ApiAdd', {
    template: `
		<el-dialog title="创建字段" width="600px" class="icon-dialog" :visible.sync="show" :before-close="closeForm" @open="open"  append-to-body>
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="90px">
            <el-row>
                <el-col :span="12">
                    <el-form-item label="字段标题" prop="title">
                        <el-input v-model="form.title" clearable placeholder="字段中文描述"  />
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="字段名称" prop="field">
                        <el-input v-model="form.field" clearable placeholder="字段英文名称"/>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="12">
                    <el-form-item label="字段类型" prop="type">
                        <el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
                            <el-option v-for="(item,i) in field" :key="i" :label="item.type == 30 ? 'token解码值':item.name" :value="item.type"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item v-if="form.type == 22" label="层级" prop="address_type">
                        <el-select style="width:100%" v-model="form.other_config.address_type"  placeholder="请选择级层">
                            <el-option  key="0" label="省市区" value="1"></el-option>
                            <el-option  key="1" label="省市" value="2"></el-option>
                            <el-option  key="2" label="省" value="3"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-else-if="form.type == 31" label="长度" prop="rand_length">
                        <el-input v-model="form.other_config.rand_length" clearable placeholder="随机数长度"/>
                    </el-form-item>
                    <el-form-item v-else label="默认值" prop="default_value">
                        <el-input v-model="form.default_value" clearable placeholder="同步数据表默认值"/>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row v-if="form.type == 9 || form.type == 11 || form.type == 12">
                <el-form-item label="日期格式" prop="datetime_config">
                    <el-select style="width:100%" :size="size" @change="selectDate" v-model="form.datetime_config" clearable  filterable placeholder="请选择日期格式">
                            <el-option key="1" label="年月日时分秒" value="datetime"></el-option>
                            <el-option key="2" label="年月日" value="date"></el-option>
                            <el-option key="3" label="年" value="year"></el-option>
                            <el-option key="4" label="月" value="month"></el-option>
                            <el-option key="5" label="多个日期" value="dates"></el-option>
                    </el-select>
                </el-form-item>
            </el-row>
			<el-row v-if="form.type == 31">
				<el-form-item label="随机数格式" prop="form.other_config.rand_config">
					<el-select style="width:100%" :size="size" v-model="form.other_config.rand_config" clearable  filterable placeholder="请选择随机数格式">
							<el-option key="1" label="字母大小写数字组合" value="all"></el-option>
							<el-option key="2" label="字母大小写组合" value="letter"></el-option>
							<el-option key="3" label="纯数字组合" value="number"></el-option>
					</el-select>
				</el-form-item>
			</el-row>
            <el-row v-if="list_item">
				<el-form-item label="选项配置" prop="item_config">
					<draggable v-model="form.item_config" v-bind="{group:'item'}" handle=".jzd-handle">
					<el-row v-for="(item,i) in form.item_config" :key="i">
						<el-col :span="7">
							<el-form-item style="margin-bottom:3px !important">
								<el-input  v-model="item.key" placeholder="选项名称"/>
							</el-form-item>
						</el-col>
						<el-col :span="6">
							<el-form-item style="margin-bottom:3px !important">
								<el-input style="position:relative;left:5px;" v-model="item.val" placeholder="选项值"/>
							</el-form-item>
						</el-col>
						<el-col :span="6">
							<el-form-item style="margin-bottom:3px !important">
								<el-select style="position:relative;left:10px;" v-model="item.label_color" size="small" clearable placeholder="请选择背景色">
									<el-option key="1" style="background:#409eff" label="primary" value="primary"></el-option>
									<el-option key="2" style="background:#67c23a" label="success" value="success"></el-option>
									<el-option key="3" style="background:#909399" label="info" value="info"></el-option>
									<el-option key="4" style="background:#e6a23c" label="warning" value="warning"></el-option>
									<el-option key="5" style="background:#f56c6c" label="danger" value="danger"></el-option>
									<el-option key="6" style="background:#5ed84f" label="fresh-green" value="fresh-green"></el-option>
									<el-option key="7" style="background:#353640" label="deep-space" value="deep-space"></el-option>
									<el-option key="8" style="background:#6b6f80" label="cool-gray" value="cool-gray"></el-option>
									<el-option key="9" style="background:#6967ce" label="violet" value="violet"></el-option>
									<el-option key="10" style="background:#28afd0" label="aqua" value="aqua"></el-option>
									<el-option key="11" style="background:#fdb901" label="golden" value="golden"></el-option>
									<el-option key="12" style="background:#fa626b" label="coral" value="coral"></el-option>
									<el-option key="13" style="background:#babfc7" label="silver" value="silver"></el-option>
									<el-option key="14" style="background:#53a8ff" label="sky-blue" value="sky-blue"></el-option>
									<el-option key="15" style="background:#85ce61" label="leaf-green" value="leaf-green"></el-option>
									<el-option key="16" style="background:#a6a9ad" label="mist-gray" value="mist-gray"></el-option>
									<el-option key="17" style="background:#eebe77" label="honey" value="honey"></el-option>
									<el-option key="18" style="background:#f78989" label="blossom" value="blossom"></el-option>
									<el-option key="19" style="background:#3d4b5c" label="steel" value="steel"></el-option>
									<el-option key="20" style="background:#7e57c2" label="amethyst" value="amethyst"></el-option>
								</el-select>
							</el-form-item>
						</el-col>
						<el-col :span="4">
							<el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem('item_config',i)"></el-button>
							<el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:12px" icon="el-icon-rank"></el-button>
						</el-col>
					</el-row>
					</draggable>
					<div>
						<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('item_config')">追加</el-button>
						<el-button v-if="form.item_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('item_config')">清空</el-button>
						<el-select v-if="form.item_config.length === 0"  :size="size" style="height:25px; light:25px; margin-left:20px;" v-model="default_config" @change="setDefaultItem"  placeholder="请选择默认配置">
							<el-option v-for="(item,i) in item_field" :key="i" :label="item.name" :value="item.item"></el-option>
						</el-select>
					</div>
				</el-form-item>
			</el-row>
			<el-row>
                <el-col v-if="dbtype !== 'mongo'" :span="24">
                    <el-form-item label="创建字段" prop="create_table_field">
                        <el-radio-group v-model="form.create_table_field">
                            <el-radio :label="1">是</el-radio>
                            <el-radio :label="0">否</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </el-col>
            </el-row>
			<el-row v-if="form.create_table_field == 0 && dbtype !== 'mongo'">
				<el-col :span="24">
					<el-form-item label="所属表" prop="belong_table">
						<el-select @focus="getTablesByMenuId" @change="setPostStatus" style="width:100%" clearable v-model="form.belong_table" filterable  placeholder="关联字段所属表（配置多表专属，其它勿设置）">
							<el-option v-for="(item,i) in tableList" :key="i" :value="item.table_name">{{item.table_name}}({{item.title}})</el-option>
						</el-select>
						<label style="color:red;font-size:13px;display:block;">
                            关联字段规则：关联字段小驼峰__需要反显字段<br>
                            示例：memberId__member_name
                        </label>
					</el-form-item>
				</el-col>
			</el-row>
            <el-row>
                <el-col :span="12">
                    <el-form-item label="验证方式" prop="validate">
                        <el-checkbox-group v-model="form.validate">
                            <el-checkbox label="notempty">不为空</el-checkbox>
                            <el-checkbox label="unique">唯一</el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="可录入" prop="post_status">
                        <el-radio v-model="form.post_status" :label="1">是</el-radio>
                        <el-radio v-model="form.post_status" :label="0">否</el-radio>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="20">
                    <el-form-item label="验证规则" prop="rule">
                        <el-input v-model="form.rule" clearable placeholder="输入框验证规则"/>
                    </el-form-item>
                </el-col>
                <el-col :span="4">
                    <el-select :size="size" v-model="default_rules" @change="setDefaultRule" prop="default_rules" clearable filterable placeholder="请选择">
                        <el-option v-for="(item,index) in ruleList" :key="index" :label="index" :value="item"></el-option>
                    </el-select>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="8">
                    <el-form-item label="数据结构" prop="datatype">
                        <el-select v-model="form.datatype" filterable @change="setFieldLength"  placeholder="请选择">
                            <el-option v-for="(item,i) in propertyField" :key="i" :label="item.name" :value="item.name"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col v-if="dbtype !== 'mongo'" :span="8">
                    <el-form-item label="字段长度" prop="length">
                        <el-input v-model="form.length" placeholder="字段长度"/>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="字段索引" prop="indexdata">
                        <el-select v-model="form.indexdata" filterable placeholder="请选择">
                            <el-option key="1" label="普通索引" value="index"></el-option>
                            <el-option key="2" label="唯一索引" value="unique"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
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
            default: 'small'
        },
        menuid: {
            type: String,
        },
        field: {
            type: Array,
        },
        item_field: {
            type: Array,
        }
    },
    data() {
        return {
            form: {
                title: '',
                field: '',
                post_status: 1,
                create_table_field: 1,
                validate: [],
                item_config: [],
                other_config: {
                    address_type: 1,
                },
                datatype: '',
                length: '',
                belong_table: '',
                default_value: '',
                menu_id: this.menuid,
            },
            activeName: 'first',
            list_item: false,
            loading: false,
            propertyField: [],
            default_config: '',
            default_rules: '',
            ruleList: [],
            tableList: [],
            dbtype: '',
            rules: {
                title: [{required: true, message: '字段中文名不能为空', trigger: 'blur'}],
                field: [
                    {required: true, message: '字段英文名不能为空', trigger: 'blur'},
                    {pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许输入字母、数字和下划线', trigger: 'blur'}
                ],
                type: [{required: true, message: '字段类型不能为空', trigger: 'blur'}],
                login_fields: [{required: true, message: '请配置登录账号密码字段', trigger: 'blur'}],
            },
        }
    },
    methods: {
        submit() {
            if (this.form.create_table_field == 0 && this.form.belong_table) {
                const str = this.form.field;
                const regex = /^[a-z][a-zA-Z0-9]*(?:[A-Z][a-z0-9]*)*__.+$/;
                if (!regex.test(str)) {
                    this.$message.error(this.form.title + "的格式有误，请重新修改");
                    return;
                }
            }
            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/createField', this.form).then(res => {
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
            axios.post(base_url + '/Sys.Base/configList', {menu_id: this.menuid}).then(res => {
                this.ruleList = res.data.ruleList
                this.propertyField = res.data.propertyField
                this.dbtype = res.data.dbtype
            })
        },
        selectType() {
            if (this.dbtype !== 'mongo') {
                this.field.forEach(item => {
                    if (this.form.type == item.type) {
                        this.propertyField.forEach(vo => {
                            if (item.property == vo.type) {
                                this.form.datatype = vo.name
                                this.form.length = vo.decimal ? vo.maxlen + ',' + vo.decimal : vo.maxlen
                            }
                        })
                        this.list_item = item.item
                        if (!item.item) {
                            this.form.item_config = []
                        }
                    }
                })
            } else {
                this.field.forEach(item => {
                    if (this.form.type == item.type) {
                        this.propertyField.forEach(vo => {
                            if (item.mongoProperty == vo.type) {
                                this.form.datatype = vo.name
                            }
                        })
                        this.list_item = item.item
                        if (!item.item) {
                            this.form.item_config = []
                        }
                    }
                })
            }
        },
        setDefaultRule() {
            this.form.rule = this.default_rules
        },
        setFieldLength() {
            if (this.dbtype !== 'mongo') {
                this.propertyField.forEach(item => {
                    if (this.form.datatype == item.name) {
                        this.form.length = item.decimal ? item.maxlen + ',' + item.decimal : item.maxlen
                    }
                })
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
        setDefaultItem(val) {
            this.form['item_config'] = val
        },
        selectDate(val) {
            if (this.dbtype !== 'mongo') {
                if (['year', 'month', 'time', 'dates'].includes(val)) {
                    this.form.datatype = 'varchar'
                    this.form.length = 250
                } else {
                    this.form.datatype = 'int'
                    this.form.length = 10
                }
            } else {
                if (['year', 'month', 'time', 'dates'].includes(val)) {
                    this.form.datatype = 'string'
                    this.form.length = 250
                } else {
                    this.form.datatype = 'int'
                    this.form.length = 10
                }
            }
        },
        getTablesByMenuId() {
            axios.post(base_url + '/Sys.Base/getTablesByMenuId', {menu_id: this.menuid}).then(res => {
                this.tableList = res.data.data
            })
        },
        closeForm() {
            this.$emit('update:show', false)
            this.loading = false
            this.$nextTick(() => {
                this.$refs['form'].resetFields();//清空表单
                this.default_rules = ''
                this.list_item = false
                this.form.other_config = {}
                this.activeName = 'first'
            })
        }
    },
});


//修改api字段
Vue.component('ApiUpdate', {
    template: `
		<el-dialog title="更新字段" width="600px" class="icon-dialog" :visible.sync="show" :before-close="closeForm" @open="open"  append-to-body>
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="90px">
            <el-row>
                <el-col :span="12">
                    <el-form-item label="字段标题" prop="title">
                        <el-input v-model="form.title" clearable placeholder="字段中文描述"  />
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="字段名称" prop="field">
                        <el-input v-model="form.field" clearable placeholder="字段英文名称"/>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="12">
                    <el-form-item label="字段类型" prop="type">
                        <el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
                            <el-option v-for="(item,i) in field" :key="i" :label="item.type == 30 ? 'token解码值':item.name" :value="item.type"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item v-if="form.type == 22" label="层级" prop="address_type">
                        <el-select style="width:100%" v-model="form.other_config.address_type"  placeholder="请选择级层">
                            <el-option  key="0" label="省市区" value="1"></el-option>
                            <el-option  key="1" label="省市" value="2"></el-option>
                            <el-option  key="2" label="省" value="3"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-else-if="form.type == 31" label="长度" prop="rand_length">
                        <el-input v-model="form.other_config.rand_length" clearable placeholder="随机数长度"/>
                    </el-form-item>
                    <el-form-item v-else label="默认值" prop="default_value">
                        <el-input v-model="form.default_value" clearable placeholder="同步数据表默认值"/>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row v-if="form.type == 9 || form.type == 11 || form.type == 12">
                <el-form-item label="日期格式" prop="datetime_config">
                    <el-select style="width:100%" :size="size" @change="selectDate" v-model="form.datetime_config" clearable  filterable placeholder="请选择日期格式">
                            <el-option key="1" label="年月日时分秒" value="datetime"></el-option>
                            <el-option key="2" label="年月日" value="date"></el-option>
                            <el-option key="3" label="年" value="year"></el-option>
                            <el-option key="4" label="月" value="month"></el-option>
                            <el-option key="5" label="多个日期" value="dates"></el-option>
                    </el-select>
                </el-form-item>
            </el-row>
			<el-row v-if="form.type == 31">
				<el-form-item label="随机数格式" prop="form.other_config.rand_config">
					<el-select style="width:100%" :size="size" v-model="form.other_config.rand_config" clearable  filterable placeholder="请选择随机数格式">
							<el-option key="1" label="字母大小写数字组合" value="all"></el-option>
							<el-option key="2" label="字母大小写组合" value="letter"></el-option>
							<el-option key="3" label="纯数字组合" value="number"></el-option>
					</el-select>
				</el-form-item>
			</el-row>
            <el-row v-if="list_item">
				<el-form-item label="选项配置" prop="item_config">
					<draggable v-model="form.item_config" v-bind="{group:'item'}" handle=".jzd-handle">
					<el-row v-for="(item,i) in form.item_config" :key="i">
						<el-col :span="7">
							<el-form-item style="margin-bottom:3px !important">
								<el-input  v-model="item.key" placeholder="选项名称"/>
							</el-form-item>
						</el-col>
						<el-col :span="6">
							<el-form-item style="margin-bottom:3px !important">
								<el-input style="position:relative;left:5px;" v-model="item.val" placeholder="选项值"/>
							</el-form-item>
						</el-col>
						<el-col :span="6">
							<el-form-item style="margin-bottom:3px !important">
								<el-select style="position:relative;left:10px;" v-model="item.label_color" size="small" clearable placeholder="请选择背景色">
									<el-option key="1" style="background:#409eff" label="primary" value="primary"></el-option>
									<el-option key="2" style="background:#67c23a" label="success" value="success"></el-option>
									<el-option key="3" style="background:#909399" label="info" value="info"></el-option>
									<el-option key="4" style="background:#e6a23c" label="warning" value="warning"></el-option>
									<el-option key="5" style="background:#f56c6c" label="danger" value="danger"></el-option>
									<el-option key="6" style="background:#5ed84f" label="fresh-green" value="fresh-green"></el-option>
									<el-option key="7" style="background:#353640" label="deep-space" value="deep-space"></el-option>
									<el-option key="8" style="background:#6b6f80" label="cool-gray" value="cool-gray"></el-option>
									<el-option key="9" style="background:#6967ce" label="violet" value="violet"></el-option>
									<el-option key="10" style="background:#28afd0" label="aqua" value="aqua"></el-option>
									<el-option key="11" style="background:#fdb901" label="golden" value="golden"></el-option>
									<el-option key="12" style="background:#fa626b" label="coral" value="coral"></el-option>
									<el-option key="13" style="background:#babfc7" label="silver" value="silver"></el-option>
									<el-option key="14" style="background:#53a8ff" label="sky-blue" value="sky-blue"></el-option>
									<el-option key="15" style="background:#85ce61" label="leaf-green" value="leaf-green"></el-option>
									<el-option key="16" style="background:#a6a9ad" label="mist-gray" value="mist-gray"></el-option>
									<el-option key="17" style="background:#eebe77" label="honey" value="honey"></el-option>
									<el-option key="18" style="background:#f78989" label="blossom" value="blossom"></el-option>
									<el-option key="19" style="background:#3d4b5c" label="steel" value="steel"></el-option>
									<el-option key="20" style="background:#7e57c2" label="amethyst" value="amethyst"></el-option>
								</el-select>
							</el-form-item>
						</el-col>
						<el-col :span="4">
							<el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem('item_config',i)"></el-button>
							<el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:12px" icon="el-icon-rank"></el-button>
						</el-col>
					</el-row>
					</draggable>
					<div>
						<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('item_config')">追加</el-button>
						<el-button v-if="form.item_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('item_config')">清空</el-button>
						<el-select v-if="form.item_config.length === 0"  :size="size" style="height:25px; light:25px; margin-left:20px;" v-model="default_config" @change="setDefaultItem"  placeholder="请选择默认配置">
							<el-option v-for="(item,i) in item_field" :key="i" :label="item.name" :value="item.item"></el-option>
						</el-select>
					</div>
				</el-form-item>
			</el-row>
            <el-row>
                <el-col v-if="dbtype !== 'mongo'" :span="24">
                    <el-form-item label="更新字段" prop="create_table_field">
                        <el-radio-group v-model="form.create_table_field">
                            <el-radio :label="1">是</el-radio>
                            <el-radio :label="0">否</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </el-col>
            </el-row>
			<el-row v-if="form.create_table_field == 0 && dbtype !== 'mongo'">
				<el-col :span="24">
					<el-form-item label="所属表" prop="belong_table">
						<el-select @focus="getTablesByMenuId" @change="setPostStatus" style="width:100%" clearable v-model="form.belong_table" filterable  placeholder="关联字段所属表（配置多表专属，其它勿设置）">
							<el-option v-for="(item,i) in tableList" :key="i" :value="item.table_name">{{item.table_name}}({{item.title}})</el-option>
						</el-select>
						<label style="color:red;font-size:13px;display:block;">
                            关联字段规则：关联字段小驼峰__需要反显字段<br>
                            示例：memberId__member_name
                        </label>
					</el-form-item>
				</el-col>
			</el-row>
            <el-row>
                <el-col :span="12">
                    <el-form-item label="验证方式" prop="validate">
                        <el-checkbox-group v-model="form.validate">
                            <el-checkbox label="notempty">不为空</el-checkbox>
                            <el-checkbox label="unique">唯一</el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="可录入" prop="post_status">
                        <el-radio v-model="form.post_status" :label="1">是</el-radio>
                        <el-radio v-model="form.post_status" :label="0">否</el-radio>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="20">
                    <el-form-item label="验证规则" prop="rule">
                        <el-input v-model="form.rule" clearable placeholder="输入框验证规则"/>
                    </el-form-item>
                </el-col>
                <el-col :span="4">
                    <el-select :size="size" v-model="default_rules" @change="setDefaultRule" prop="default_rules" clearable filterable placeholder="请选择">
                        <el-option v-for="(item,index) in ruleList" :key="index" :label="index" :value="item"></el-option>
                    </el-select>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="8">
                    <el-form-item label="数据结构" prop="datatype">
                        <el-select v-model="form.datatype" filterable @change="setFieldLength"  placeholder="请选择">
                            <el-option v-for="(item,i) in propertyField" :key="i" :label="item.name" :value="item.name"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col v-if="dbtype !== 'mongo'" :span="8">
                    <el-form-item label="字段长度" prop="length">
                        <el-input v-model="form.length" placeholder="字段长度"/>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="字段索引" prop="indexdata">
                        <el-select v-model="form.indexdata" filterable placeholder="请选择">
                            <el-option key="1" label="普通索引" value="index"></el-option>
                            <el-option key="2" label="唯一索引" value="unique"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
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
            default: 'small'
        },
        menu_id: {
            type: String,
        },
        field: {
            type: Array,
        },
        item_field: {
            type: Array,
        },
        info: {
            type: Object,
        },
    },
    data() {
        return {
            form: {
                title: '',
                post_status: 1,
                create_table_field: 1,
                list_show: 2,
                validate: [],
                item_config: [],
                other_config: {
                    address_type: 1,
                },
                datatype: '',
                length: '',
                belong_table: '',
                default_value: ''
            },
            activeName: 'first',
            list_item: false,
            loading: false,
            propertyField: [],
            default_config: '',
            default_rules: '',
            ruleList: [],
            tableList: [],
            dbtype: '',
            rules: {
                title: [{required: true, message: '字段中文名不能为空', trigger: 'blur'}],
                field: [
                    {required: true, message: '字段英文名不能为空', trigger: 'blur'},
                    {pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许输入字母、数字和下划线', trigger: 'blur'}
                ],
                type: [{required: true, message: '字段类型不能为空', trigger: 'blur'}],
                login_fields: [{required: true, message: '请配置登录账号密码字段', trigger: 'blur'}],
            },
        }
    },
    methods: {
        submit() {
            if (this.form.create_table_field == 0 && this.form.belong_table) {
                const str = this.form.field;
                const regex = /^[a-z][a-zA-Z0-9]*(?:[A-Z][a-z0-9]*)*__.+$/;
                if (!regex.test(str)) {
                    this.$message.error(this.form.title + "的格式有误，请重新修改");
                    return;
                }
            }
            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/updateField', this.form).then(res => {
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
            if (this.form.other_config == '' || this.form.other_config == '[]' || this.form.other_config == null) {
                this.form.other_config = {}
            } else {
                this.form.other_config = JSON.parse(this.info.other_config)
            }
            this.setDefaultVal('item_config')
            this.field.forEach(item => {
                if (this.form.type == item.type) {
                    this.list_item = item.item
                }
            })
            axios.post(base_url + '/Sys.Base/configList', {menu_id: this.menu_id}).then(res => {
                this.ruleList = res.data.ruleList
                this.propertyField = res.data.propertyField
                this.dbtype = res.data.dbtype
            })
        },
        selectType() {
            if (this.dbtype !== 'mongo') {
                this.field.forEach(item => {
                    if (this.form.type == item.type) {
                        this.propertyField.forEach(vo => {
                            if (item.property == vo.type) {
                                this.form.datatype = vo.name
                                this.form.length = vo.decimal ? vo.maxlen + ',' + vo.decimal : vo.maxlen
                            }
                        })
                        this.list_item = item.item
                        if (!item.item) {
                            this.form.item_config = []
                        }
                    }
                })
            } else {
                this.field.forEach(item => {
                    if (this.form.type == item.type) {
                        this.propertyField.forEach(vo => {
                            if (item.mongoProperty == vo.type) {
                                this.form.datatype = vo.name
                            }
                        })
                        this.list_item = item.item
                        if (!item.item) {
                            this.form.item_config = []
                        }
                    }
                })
            }
        },
        setDefaultVal(key) {
            if (this.form[key] == null || this.form[key] == '') {
                this.form[key] = []
            }
        },
        setDefaultRule() {
            this.form.rule = this.default_rules
        },
        setFieldLength() {
            if (this.dbtype !== 'mongo') {
                this.propertyField.forEach(item => {
                    if (this.form.datatype == item.name) {
                        this.form.length = item.decimal ? item.maxlen + ',' + item.decimal : item.maxlen
                    }
                })
            }
        },
        getTablesByMenuId() {
            axios.post(base_url + '/Sys.Base/getTablesByMenuId', {menu_id: this.menu_id}).then(res => {
                this.tableList = res.data.data
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
        setDefaultItem(val) {
            this.form['item_config'] = val
        },
        selectDate(val) {
            if (this.dbtype !== 'mongo') {
                if (['year', 'month', 'time', 'dates'].includes(val)) {
                    this.form.datatype = 'varchar'
                    this.form.length = 250
                } else {
                    this.form.datatype = 'int'
                    this.form.length = 10
                }
            } else {
                if (['year', 'month', 'time', 'dates'].includes(val)) {
                    this.form.datatype = 'string'
                    this.form.length = 250
                } else {
                    this.form.datatype = 'int'
                    this.form.length = 10
                }
            }
        },
        closeForm() {
            this.$emit('update:show', false)
            this.loading = false
            this.$nextTick(() => {
                this.$refs['form'].resetFields();//清空表单
                this.default_rules = ''
                this.list_item = false
                this.form.other_config = {}
                this.activeName = 'first'
            })
        }
    },
});


//cms添加字段
Vue.component('CmsAdd', {
    template: `
		<el-dialog title="创建字段" width="600px" class="icon-dialog" :visible.sync="show" :before-close="closeForm" @open="open"  append-to-body>
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="90px">
            <el-row>
                <el-col :span="24">
                    <el-form-item label="字段标题" prop="title">
                        <el-input v-model="form.title" clearable placeholder="字段中文描述"  />
                    </el-form-item>
                </el-col>
            </el-row>
             <el-row>
                <el-col :span="24">
                    <el-form-item label="字段名称" prop="field">
                        <el-input @blur="checkCmsField" v-model="form.field" clearable placeholder="字段英文名称"/>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="12">
                    <el-form-item label="字段类型" prop="type">
                        <el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
                            <el-option v-for="(item,i) in field" :key="i" :label="item.name" :value="item.type"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item v-if="form.type == 22" label="层级" prop="address_type">
                        <el-select style="width:100%" v-model="form.other_config.address_type"  placeholder="请选择级层">
                            <el-option  key="0" label="省市区" value="1"></el-option>
                            <el-option  key="1" label="省市" value="2"></el-option>
                            <el-option  key="2" label="省" value="3"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-else-if="form.type == 13" label="上传样式" prop="address_type">
                        <el-select style="width:100%" v-model="form.other_config.upload_type"  placeholder="上传样式">
                            <el-option key="0" label="样式1(带缩略图)" value="1"></el-option>
                            <el-option key="1" label="样式2(带输入框)" value="2"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-else-if="form.type == 31" label="长度" prop="rand_length">
                        <el-input v-model="form.other_config.rand_length" clearable placeholder="随机数长度"/>
                    </el-form-item>
                    <el-form-item v-else label="默认值" prop="default_value">
                        <el-input v-model="form.default_value" clearable placeholder="同步数据表默认值"/>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row v-if="form.type == 9 || form.type == 11 || form.type == 12">
                <el-form-item label="日期格式" prop="datetime_config">
                    <el-select style="width:100%" :size="size" v-model="form.datetime_config" clearable  filterable placeholder="请选择日期格式">
                            <el-option key="1" label="年月日时分秒" value="datetime"></el-option>
                            <el-option key="2" label="年月日" value="date"></el-option>
                            <el-option key="3" label="年" value="year"></el-option>
                            <el-option key="4" label="月" value="month"></el-option>
                            <el-option key="5" label="多个日期" value="dates"></el-option>
                    </el-select>
                </el-form-item>
            </el-row>
            <el-row v-if="list_item">
				<el-form-item label="选项配置" prop="item_config">
					<draggable v-model="form.item_config" v-bind="{group:'item'}" handle=".jzd-handle">
					<el-row v-for="(item,i) in form.item_config" :key="i">
						<el-col :span="7">
							<el-form-item style="margin-bottom:3px !important">
								<el-input  v-model="item.key" placeholder="选项名称"/>
							</el-form-item>
						</el-col>
						<el-col :span="6">
							<el-form-item style="margin-bottom:3px !important">
								<el-input style="position:relative;left:5px;" v-model="item.val" placeholder="选项值"/>
							</el-form-item>
						</el-col>
						<el-col :span="6">
							<el-form-item style="margin-bottom:3px !important">
								<el-select style="position:relative;left:10px;" v-model="item.label_color" size="small" clearable placeholder="请选择背景色">
									<el-option key="1" style="background:#409eff" label="primary" value="primary"></el-option>
									<el-option key="2" style="background:#67c23a" label="success" value="success"></el-option>
									<el-option key="3" style="background:#909399" label="info" value="info"></el-option>
									<el-option key="4" style="background:#e6a23c" label="warning" value="warning"></el-option>
									<el-option key="5" style="background:#f56c6c" label="danger" value="danger"></el-option>
									<el-option key="6" style="background:#5ed84f" label="fresh-green" value="fresh-green"></el-option>
									<el-option key="7" style="background:#353640" label="deep-space" value="deep-space"></el-option>
									<el-option key="8" style="background:#6b6f80" label="cool-gray" value="cool-gray"></el-option>
									<el-option key="9" style="background:#6967ce" label="violet" value="violet"></el-option>
									<el-option key="10" style="background:#28afd0" label="aqua" value="aqua"></el-option>
									<el-option key="11" style="background:#fdb901" label="golden" value="golden"></el-option>
									<el-option key="12" style="background:#fa626b" label="coral" value="coral"></el-option>
									<el-option key="13" style="background:#babfc7" label="silver" value="silver"></el-option>
									<el-option key="14" style="background:#53a8ff" label="sky-blue" value="sky-blue"></el-option>
									<el-option key="15" style="background:#85ce61" label="leaf-green" value="leaf-green"></el-option>
									<el-option key="16" style="background:#a6a9ad" label="mist-gray" value="mist-gray"></el-option>
									<el-option key="17" style="background:#eebe77" label="honey" value="honey"></el-option>
									<el-option key="18" style="background:#f78989" label="blossom" value="blossom"></el-option>
									<el-option key="19" style="background:#3d4b5c" label="steel" value="steel"></el-option>
									<el-option key="20" style="background:#7e57c2" label="amethyst" value="amethyst"></el-option>
								</el-select>
							</el-form-item>
						</el-col>
						<el-col :span="4">
							<el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem('item_config',i)"></el-button>
							<el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:12px" icon="el-icon-rank"></el-button>
						</el-col>
					</el-row>
					</draggable>
					<div>
						<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('item_config')">追加</el-button>
						<el-button v-if="form.item_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('item_config')">清空</el-button>
						<el-select v-if="form.item_config.length === 0"  :size="size" style="height:25px; light:25px; margin-left:20px;" v-model="default_config" @change="setDefaultItem"  placeholder="请选择默认配置">
							<el-option v-for="(item,i) in item_field" :key="i" :label="item.name" :value="item.item"></el-option>
						</el-select>
					</div>
				</el-form-item>
			</el-row>
            <el-row>
                <el-col :span="8">
                    <el-form-item label="数据结构" prop="datatype">
                        <el-select v-model="form.datatype" filterable @change="setFieldLength"  placeholder="请选择">
                            <el-option v-for="(item,i) in propertyField" :key="i" :label="item.name" :value="item.name"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="字段长度" prop="length">
                        <el-input v-model="form.length" placeholder="字段长度"/>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="字段索引" prop="indexdata">
                        <el-select v-model="form.indexdata" filterable placeholder="请选择">
                            <el-option key="1" label="普通索引" value="normal"></el-option>
                            <el-option key="2" label="唯一索引" value="unique"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
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
            default: 'small'
        },
        menuid: {
            type: String,
        },
        field: {
            type: Array,
        },
        item_field: {
            type: Array,
        }
    },
    data() {
        return {
            form: {
                title: '',
                post_status: 1,
                create_table_field: 1,
                list_show: 2,
                validate: [],
                item_config: [],
                other_config: {
                    address_type: 1,
                },
                datatype: '',
                length: '',
                belong_table: '',
                default_value: '',
                menu_id: this.menuid,
            },
            iconDialogStatus: false,
            activeName: 'first',
            list_item: false,
            loading: false,
            propertyField: [],
            default_config: '',
            default_rules: '',
            ruleList: [],
            tableList: [],
            rules: {
                title: [{required: true, message: '字段中文名不能为空', trigger: 'blur'}],
                field: [
                    {required: true, message: '字段英文名不能为空', trigger: 'blur'},
                    {pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许输入字母、数字和下划线', trigger: 'blur'}
                ],
                type: [{required: true, message: '字段类型不能为空', trigger: 'blur'}],
                login_fields: [{required: true, message: '请配置登录账号密码字段', trigger: 'blur'}],
            },
        }
    },
    methods: {
        submit() {
            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/createField', this.form).then(res => {
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
            axios.post(base_url + '/Sys.Base/configList').then(res => {
                this.ruleList = res.data.ruleList
                this.propertyField = res.data.propertyField
            })
        },
        checkCmsField() {
            axios.post(base_url + '/Sys.Base/checkCmsField', {field: this.form.field}).then(res => {
                if (res.data.status !== 200) {
                    this.$message.error('字段已存在')
                }
            })
        },
        selectType() {
            this.field.forEach(item => {
                if (this.form.type == item.type) {
                    this.propertyField.forEach(vo => {
                        if (item.property == vo.type) {
                            this.form.datatype = vo.name
                            this.form.length = vo.decimal ? vo.maxlen + ',' + vo.decimal : vo.maxlen
                        }
                    })
                    this.list_item = item.item
                    if (!item.item) {
                        this.form.item_config = []
                    }
                }
            })
        },
        setDefaultRule() {
            this.form.rule = this.default_rules
        },
        setFieldLength() {
            this.propertyField.forEach(item => {
                if (this.form.datatype == item.name) {
                    this.form.length = item.decimal ? item.maxlen + ',' + item.decimal : item.maxlen
                }
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
        setDefaultItem(val) {
            this.form['item_config'] = val
        },
        closeForm() {
            this.$emit('update:show', false)
            this.loading = false
            this.$nextTick(() => {
                this.$refs['form'].resetFields();//清空表单
                this.default_rules = ''
                this.list_item = false
                this.form.other_config = {}
                this.activeName = 'first'
            })
        }
    },
});


//修改api字段
Vue.component('CmsUpdate', {
    template: `
		<el-dialog title="创建字段" width="600px" class="icon-dialog" :visible.sync="show" :before-close="closeForm" @open="open"  append-to-body>
        <el-form :size="size" ref="form" :model="form" :rules="rules" label-width="90px">
            <el-row>
                <el-col :span="24">
                    <el-form-item label="字段标题" prop="title">
                        <el-input v-model="form.title" clearable placeholder="字段中文描述"  />
                    </el-form-item>
                </el-col>
            </el-row>
             <el-row>
                <el-col :span="24">
                    <el-form-item label="字段名称" prop="field">
                        <el-input @blur="checkCmsField" v-model="form.field" clearable placeholder="字段英文名称"/>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="12">
                    <el-form-item label="字段类型" prop="type">
                        <el-select style="width:100%" v-model="form.type" @change="selectType" filterable placeholder="请选择">
                            <el-option v-for="(item,i) in field" :key="i" :label="item.name" :value="item.type"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item v-if="form.type == 22" label="层级" prop="address_type">
                        <el-select style="width:100%" v-model="form.other_config.address_type"  placeholder="请选择级层">
                            <el-option  key="0" label="省市区" value="1"></el-option>
                            <el-option  key="1" label="省市" value="2"></el-option>
                            <el-option  key="2" label="省" value="3"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-else-if="form.type == 13" label="上传样式" prop="address_type">
                        <el-select style="width:100%" v-model="form.other_config.upload_type"  placeholder="上传样式">
                            <el-option key="0" label="样式1(带缩略图)" value="1"></el-option>
                            <el-option key="1" label="样式2(带输入框)" value="2"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-else-if="form.type == 31" label="长度" prop="rand_length">
                        <el-input v-model="form.other_config.rand_length" clearable placeholder="随机数长度"/>
                    </el-form-item>
                    <el-form-item v-else label="默认值" prop="default_value">
                        <el-input v-model="form.default_value" clearable placeholder="同步数据表默认值"/>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row v-if="form.type == 9 || form.type == 11 || form.type == 12">
                <el-form-item label="日期格式" prop="datetime_config">
                    <el-select style="width:100%" :size="size" v-model="form.datetime_config" clearable  filterable placeholder="请选择日期格式">
                            <el-option key="1" label="年月日时分秒" value="datetime"></el-option>
                            <el-option key="2" label="年月日" value="date"></el-option>
                            <el-option key="3" label="年" value="year"></el-option>
                            <el-option key="4" label="月" value="month"></el-option>
                            <el-option key="5" label="多个日期" value="dates"></el-option>
                    </el-select>
                </el-form-item>
            </el-row>
            <el-row v-if="list_item">
				<el-form-item label="选项配置" prop="item_config">
					<draggable v-model="form.item_config" v-bind="{group:'item'}" handle=".jzd-handle">
					<el-row v-for="(item,i) in form.item_config" :key="i">
						<el-col :span="7">
							<el-form-item style="margin-bottom:3px !important">
								<el-input  v-model="item.key" placeholder="选项名称"/>
							</el-form-item>
						</el-col>
						<el-col :span="6">
							<el-form-item style="margin-bottom:3px !important">
								<el-input style="position:relative;left:5px;" v-model="item.val" placeholder="选项值"/>
							</el-form-item>
						</el-col>
						<el-col :span="6">
							<el-form-item style="margin-bottom:3px !important">
								<el-select style="position:relative;left:10px;" v-model="item.label_color" size="small" clearable placeholder="请选择背景色">
									<el-option key="1" style="background:#409eff" label="primary" value="primary"></el-option>
									<el-option key="2" style="background:#67c23a" label="success" value="success"></el-option>
									<el-option key="3" style="background:#909399" label="info" value="info"></el-option>
									<el-option key="4" style="background:#e6a23c" label="warning" value="warning"></el-option>
									<el-option key="5" style="background:#f56c6c" label="danger" value="danger"></el-option>
									<el-option key="6" style="background:#5ed84f" label="fresh-green" value="fresh-green"></el-option>
									<el-option key="7" style="background:#353640" label="deep-space" value="deep-space"></el-option>
									<el-option key="8" style="background:#6b6f80" label="cool-gray" value="cool-gray"></el-option>
									<el-option key="9" style="background:#6967ce" label="violet" value="violet"></el-option>
									<el-option key="10" style="background:#28afd0" label="aqua" value="aqua"></el-option>
									<el-option key="11" style="background:#fdb901" label="golden" value="golden"></el-option>
									<el-option key="12" style="background:#fa626b" label="coral" value="coral"></el-option>
									<el-option key="13" style="background:#babfc7" label="silver" value="silver"></el-option>
									<el-option key="14" style="background:#53a8ff" label="sky-blue" value="sky-blue"></el-option>
									<el-option key="15" style="background:#85ce61" label="leaf-green" value="leaf-green"></el-option>
									<el-option key="16" style="background:#a6a9ad" label="mist-gray" value="mist-gray"></el-option>
									<el-option key="17" style="background:#eebe77" label="honey" value="honey"></el-option>
									<el-option key="18" style="background:#f78989" label="blossom" value="blossom"></el-option>
									<el-option key="19" style="background:#3d4b5c" label="steel" value="steel"></el-option>
									<el-option key="20" style="background:#7e57c2" label="amethyst" value="amethyst"></el-option>
								</el-select>
							</el-form-item>
						</el-col>
						<el-col :span="4">
							<el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem('item_config',i)"></el-button>
							<el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:12px" icon="el-icon-rank"></el-button>
						</el-col>
					</el-row>
					</draggable>
					<div>
						<el-button type="success" icon="el-icon-plus" style="padding:5px 7px" :size="size" @click="addItem('item_config')">追加</el-button>
						<el-button v-if="form.item_config.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" :size="size" @click="clearItem('item_config')">清空</el-button>
						<el-select v-if="form.item_config.length === 0"  :size="size" style="height:25px; light:25px; margin-left:20px;" v-model="default_config" @change="setDefaultItem"  placeholder="请选择默认配置">
							<el-option v-for="(item,i) in item_field" :key="i" :label="item.name" :value="item.item"></el-option>
						</el-select>
					</div>
				</el-form-item>
			</el-row>
            <el-row>
                <el-col :span="8">
                    <el-form-item label="数据结构" prop="datatype">
                        <el-select v-model="form.datatype" filterable @change="setFieldLength"  placeholder="请选择">
                            <el-option v-for="(item,i) in propertyField" :key="i" :label="item.name" :value="item.name"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="字段长度" prop="length">
                        <el-input v-model="form.length" placeholder="字段长度"/>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="字段索引" prop="indexdata">
                        <el-select v-model="form.indexdata" filterable placeholder="请选择">
                            <el-option key="1" label="普通索引" value="normal"></el-option>
                            <el-option key="2" label="唯一索引" value="unique"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
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
            default: 'small'
        },
        menu_id: {
            type: String,
        },
        field: {
            type: Array,
        },
        item_field: {
            type: Array,
        },
        info: {
            type: Object,
        },
    },
    data() {
        return {
            form: {
                title: '',
                post_status: 1,
                create_table_field: 1,
                list_show: 2,
                validate: [],
                item_config: [],
                other_config: {
                    address_type: 1,
                },
                datatype: '',
                length: '',
                belong_table: '',
                default_value: ''
            },
            activeName: 'first',
            list_item: false,
            loading: false,
            propertyField: [],
            default_config: '',
            default_rules: '',
            ruleList: [],
            tableList: [],
            rules: {
                title: [{required: true, message: '字段中文名不能为空', trigger: 'blur'}],
                field: [
                    {required: true, message: '字段英文名不能为空', trigger: 'blur'},
                    {pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许输入字母、数字和下划线', trigger: 'blur'}
                ],
                type: [{required: true, message: '字段类型不能为空', trigger: 'blur'}],
                login_fields: [{required: true, message: '请配置登录账号密码字段', trigger: 'blur'}],
            },
        }
    },
    methods: {
        submit() {
            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    axios.post(base_url + '/Sys.Base/updateField', this.form).then(res => {
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
            if (this.form.other_config == '' || this.form.other_config == '[]' || this.form.other_config == null) {
                this.form.other_config = {}
            } else {
                this.form.other_config = JSON.parse(this.info.other_config)
            }
            this.setDefaultVal('item_config')
            this.field.forEach(item => {
                if (this.form.type == item.type) {
                    this.list_item = item.item
                }
            })
            axios.post(base_url + '/Sys.Base/configList').then(res => {
                this.ruleList = res.data.ruleList
                this.propertyField = res.data.propertyField
            })
        },
        checkCmsField() {
            axios.post(base_url + '/Sys.Base/checkCmsField', {field: this.form.field}).then(res => {
                if (res.data.status !== 200) {
                    this.$message.error('字段已存在')
                }
            })
        },
        selectType() {
            this.field.forEach(item => {
                if (this.form.type == item.type) {
                    this.propertyField.forEach(vo => {
                        if (item.property == vo.type) {
                            this.form.datatype = vo.name
                            this.form.length = vo.decimal ? vo.maxlen + ',' + vo.decimal : vo.maxlen
                        }
                    })
                    this.list_item = item.item
                    if (!item.item) {
                        this.form.item_config = []
                    }
                }
            })
        },
        setDefaultVal(key) {
            if (this.form[key] == null || this.form[key] == '') {
                this.form[key] = []
            }
        },
        setDefaultRule() {
            this.form.rule = this.default_rules
        },
        setFieldLength() {
            this.propertyField.forEach(item => {
                if (this.form.datatype == item.name) {
                    this.form.length = item.decimal ? item.maxlen + ',' + item.decimal : item.maxlen
                }
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
        setDefaultItem(val) {
            this.form['item_config'] = val
        },
        closeForm() {
            this.$emit('update:show', false)
            this.loading = false
            this.$nextTick(() => {
                this.$refs['form'].resetFields();//清空表单
                this.default_rules = ''
                this.list_item = false
                this.form.other_config = {}
                this.activeName = 'first'
            })
        }
    },
});
