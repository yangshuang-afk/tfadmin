//上传组件
Vue.component('Upload', {
	template: `
	<div>
		<div v-if="file_type == 'images'" class="image-list" style="width:100%">
			<draggable v-model="imageList" v-bind="{group:'item'}" @change="changeSort">
				<div v-for="(item, index) in imageList" :key="index" class="image-wrap">
					<div class="imgStyle">
						<img :src="item.url" /><br/>
						<el-input size="mini" v-if="showtitle" style="width:102px; position:relative; bottom:0px;" v-model="item.name" placeholder="图片名称"></el-input>
					</div>
					<div class="icon-wrap">
						<i class="el-icon-delete" @click.stop="handleRemove('images',index,item.url)"></i>&nbsp;&nbsp; 
						<i style="cursor:pointer" :class="showtitle ? 'el-icon-top' : 'el-icon-bottom'" @click.stop="handlePictureCardPreview(index)"></i>
					</div>
				</div>
				<el-upload action="#" ref="upload" class="image-uploader" :before-upload="beforeUpload" :http-request="upload" multiple>
					<i :class="loading ? 'el-icon-loading' : 'el-icon-plus'"></i>
				</el-upload>
			</draggable>
		</div>
		<div v-else-if="file_type == 'image'" class="image-list" style="width:100%">
			<el-row v-if="upload_type == '1'">
				<el-row v-if="upload_type == '1'">
					<div v-if="scene">
						<div class="image-wrap" v-if="siglepic">
							<div class="guigeimg">
								<img style="width:50px; height:50px;" :src="siglepic" />
							</div>
							<div class="icon-wrap-guige">
								<i class="el-icon-delete" @click.stop="handleRemove('image')"></i>
							</div>
						 </div>
						<el-upload v-else action="#" ref="upload" :before-upload="beforeUpload" :http-request="upload">
							<i :class="loading ? 'el-icon-loading' : 'el-icon-plus'"></i>
						</el-upload>
					</div>
					<div v-else>
						<div class="image-wrap" v-if="siglepic">
							<div class="imgStyle">
								<img :src="siglepic" />
							</div>
							<div class="icon-wrap">
								<i class="el-icon-delete" @click.stop="handleRemove('image')"></i>
							</div>
						 </div>
						<el-upload action="#" ref="upload" class="image-uploader" :before-upload="beforeUpload" :http-request="upload">
							<i :class="loading ? 'el-icon-loading' : 'el-icon-plus'"></i>
						</el-upload>
					</div>
				</el-row>
			</el-row>
			<el-row v-else>
				<el-col :span="16">
					<el-popover v-if="siglepic" placement="top" width="210" trigger="hover">
						<img v-if="siglepic" style="max-width:200px; max-height:200px" :src="siglepic">
						<el-input  slot="reference" v-model="siglepic" @paste.native="remoteFile($event)" autoComplete="off" clearable placeholder="请上传图片"></el-input>
					</el-popover>
					<el-input v-else v-model="siglepic" @paste.native="remoteFile($event)" autoComplete="off" clearable placeholder="请上传图片"></el-input>
				</el-col>
				<el-col :span="8">
					<el-upload class="upload-demo" ref="upload" action="#" :before-upload="beforeUpload" :http-request="upload">
						<el-button class="el-icon-upload" size="small" type="primary">上传图片</el-button>
					</el-upload>
					 <el-tooltip class="item hidden-sm-and-down" effect="dark" :content="'选择图片'" placement="top">
						<el-button class="el-icon-picture" size="small" @click="openImagesPick"></el-button>
					</el-tooltip>
				</el-col>
			</el-row>
		</div>
		<div v-else-if="file_type == 'file'" class="image-list" style="width:100%">
			<el-col :span="19">
				<el-input v-model="sigleFile" autoComplete="off" clearable placeholder="请上传文件"></el-input>
			</el-col>
			<el-col :span="5">
				<el-upload class="upload-demo" ref="upload" action="#" :before-upload="beforeUpload" :http-request="upload">
					<el-button class="el-icon-upload" size="small" type="primary">点击上传</el-button>
				</el-upload>
			</el-col>
		</div>
		<div v-else class="image-list" style="width:100%">
			<el-upload action="#" ref="upload" :before-upload="beforeUpload" :http-request="upload" multiple>
				<el-button size="small" type="primary">点击上传</el-button>
			</el-upload>
			<ul class="el-upload-list el-upload-list--text">
				<li v-for="(item,i) in fileList" :tabindex="i" :key="i" class="el-upload-list__item is-success">
					<a @click.stop="handleRemove('files',i,item.url)" class="el-upload-list__item-name"><i class="el-icon-document"></i>{{item.name}}</a>
					<label class="el-upload-list__item-status-label">
					<i class="el-icon-upload-success el-icon-circle-check"></i>
					</label>
					<i class="el-icon-close"></i>
				</li>
			</ul>
		</div>
		<el-progress v-if="progress" :stroke-width="5" :percentage="progressPercent"></el-progress>
		<ImagesPick :imagesDiaShow.sync="imagesDialogStatus" :siglepic.sync ="siglepic" size="small"></ImagesPick>
		<VueCrop :cropDiaShow.sync="cropDialogStatus" :only_param="only_param" :coverage_tfadmin="coverage_tfadmin" :imgbase64="imgbase64" :cropFileName="cropFileName" :siglepic.sync="siglepic" :cropsize="cropsize" :rename="rename"></VueCrop>
	</div>
	`
	,
	components:{
		ImagesPick:{
			template:`
				<el-dialog title="图片管理" width="640px" class="icon-dialog" :visible.sync="imagesDiaShow" @open="open" :before-close="closePickForm" append-to-body>
					<div style="padding-left:10px">
						<el-row :gutter="20">
							<el-col :span="4">
								<el-upload class="upload-demo" ref="upload" multiple action="#" :before-upload="beforeUpload" :http-request="upload">
									<el-button class="el-icon-upload" size="small" type="primary"> 上传图片</el-button>
								</el-upload>
							</el-col>
							<el-col :span="8">
							  <el-button class="el-icon-delete" size="small" type="danger" @click="deletePic">批量删除</el-button>
							</el-col>
						</el-row>
						<el-row style="margin-top:15px;">
							<el-col :span="24">
								<el-row>
									<div class="image-wrap"  v-for="(item,index) in list" :key="index" @click="selection(item.filepath)">
										<label v-for="(v, k) in files" :key="k" v-show="item.filepath === v">
											<input type="checkbox" :checked="item.filepath === v">
											<i>✓</i>
										</label>
										<div class="imgStyle">
											<img :src="item.filepath" /><br/>
										</div>
									</div>
								</el-row>
								<Page :total="page_data.total" :page.sync="page_data.page" :limit.sync="page_data.limit" @pagination="index" />
							</el-col>
						</el-row>
					</div>
					<div slot="footer" class="dialog-footer">
						<el-button size="small" :loading="loading" type="primary" @click="submitPick" >
							<span>确 定</span>
						</el-button>
						<el-button size="small" @click="closePickForm">取 消</el-button>
					</div>
				</el-dialog>
			`,
			props: {
				imagesDiaShow: {
					type: Boolean,
					default: false
				},
			},
			watch: {
				imagesDiaShow(value) {
					if(value){
						this.index()
					}
				}
			},
			data() {
				return {
					files: [],
					list: [],
					loading: false,
					progressPercent:0,
					progress:false,
					page_data: {
						limit: 20,
						page: 1,
						total:20,
					},
				}
			},
			methods:{
				open(){
					this.files = []
				},
				beforeUpload(item){
					if(item.type.split('/')[0] !== 'image') {
						this.$message.error('请选择图片')
						return false
					}
				},
				selection(filepath){
					let index = this.files.indexOf(filepath)
					if (index === -1) {
						this.files.push(filepath)
					}else {
						this.files.splice(index, 1)
					}
				},
				deletePic(){
					this.$confirm('是否删除文件?', '提示', {
						confirmButtonText: '确定',
						cancelButtonText: '取消',
						type: 'warning'
					}).then(() => {
						axios.post(base_url+'/Base/deleteFile',{filepath:this.files}).then(res => {
							if(res.data.status == 200){
								this.$message({message: '删除成功', type: 'success'})
								this.index()
							}else{
								this.$message.error(res.data.msg)
							}
						})
					})
				},
				upload(item){
					let formdata = new FormData()
					let ali = new FormData()  //此处非常坑 阿里上传file属性必须要放到最后  所以只能赋值对象
					formdata.append('file', item.file)
					const config = {
						onUploadProgress: progressEvent => {
							if (progressEvent.lengthComputable) {
								this.progress = true
								this.progressPercent = Number((progressEvent.loaded / progressEvent.total * 100).toFixed(2))
							}
						}
					}
					try {
						axios.post(base_url+'/Upload/upload', formdata,config).then(res => {
							if(res.data.status == 200){
								if(!res.data.filestatus){
									if(res.data.data.type == 'qiniuyun'){
										formdata.append('token',res.data.data.token)
										formdata.append('key',res.data.data.key)
										axios.post(res.data.data.serverurl, formdata,config).then(ret => {
											if(ret.data.key){
												axios.post(base_url+'/Upload/createFile',{filepath:res.data.data.domain+'/'+ret.data.key}).then(res => {
													this.progressPercent = 0
													this.progress = false
													this.index()
												})
											}
										})
									}else if(res.data.data.type == 'tencent'){
										let vm = this
										$.getScript(base_dir+"/assets/js/cos-js-sdk-v5.min.js",function(script){
											var cos = new COS({
												SecretId: res.data.data.SecretId,
												SecretKey: res.data.data.SecretKey
											});
											cos.putObject({
												Bucket: res.data.data.Bucket, // 桶名-APPID  必须
												Region: res.data.data.Region, //区域 必须
												Schema: res.data.data.Schema, //区域 必须
												Key: res.data.data.key, //文件名必须
												Body: item.file, // 上传文件对象
												onProgress: function (progressData) {
												}
											}, function (err, data) {
												if(data.Location){
													const url = res.data.data.Schema+'://'+data.Location
													axios.post(base_url+'/Upload/createFile',{filepath:url}).then(result => {
														vm.setFilePath(item,url)
													})
												}
											})
										})
									}else if(res.data.data.type == 'ali'){
										ali.append('Signature',res.data.data.sign)
										ali.append('callback',res.data.data.callback)
										ali.append('OSSAccessKeyId',res.data.data.OSSAccessKeyId)
										ali.append('policy',res.data.data.policy)
										ali.append('key',res.data.data.key)
										ali.append('file', item.file)
										axios.post(res.data.data.serverurl, ali,config).then(ret => {
											if(ret.data.code == 1){
												axios.post(base_url+'/Upload/createFile',{filepath:ret.data.data}).then(res => {
													this.progressPercent = 0
													this.progress = false
													this.index()
												})
											}
										})
									}else{
										this.$message({message: '上传成功', type: 'success'})
										this.progressPercent = 0
										this.progress = false
										this.index()
									}
								}else{
									this.$message.error('文件已经存在')
								}
							}
						})
					}catch (error) {
						this.$message.error('上传失败')
					}finally {
						this.$refs.upload && this.$refs.upload.clearFiles()
					}
				},
				index(){
					let param = {limit:this.page_data.limit,page:this.page_data.page}
					this.loading = true
					axios.post(base_url+'/Base/fileList',param).then(res => {
						this.list = res.data.data.data
						this.page_data.total = res.data.data.total
						this.loading = false
					})
				},
				submitPick(){
					if(this.files[0] !== undefined){
						this.$emit('update:siglepic',this.files[0])
					}
					this.closePickForm()
				},
				closePickForm(){
					this.$emit('update:imagesDiaShow', false)
				}
			}
		},
		VueCrop:{
			template:`
				<el-dialog title="图片裁剪" width="70%" :visible.sync="cropDiaShow" :before-close="closeCrop" class="icon-dialog" append-to-body>
				<div style="height:500px">
					<vue-cropper
					  ref="cropper"
					  :img="example.img"
					  :output-size="example.size"
					  :output-type="example.outputType"
					  :info="example.info"
					  :can-move='example.canMove'
					  :fixed-box="example.fixedBox"
					  :center-box="example.centerBox"
					  :can-scale="example.canScale"
					  :auto-crop="example.autoCrop"
					  :auto-crop-width="example.autoCropWidth"
					  :auto-crop-height="example.autoCropHeight"
					  :fixed="example.fixed"
					  :fixed-number="example.fixedNumber"
					  >
					</vue-cropper>
					</div>
					<div style="text-align:center; margin-top:15px;">
						<div class="scope-btn">
						  <el-button size="mini" type="danger" plain icon="el-icon-zoom-in" @click="changeScale(1)">放大</el-button>
						  <el-button size="mini" type="danger" plain icon="el-icon-zoom-out" @click="changeScale(-1)">缩小</el-button>
						  <el-button size="mini" type="danger" plain @click="rotateLeft">↺ 左旋转</el-button>
						  <el-button size="mini" type="danger" plain @click="rotateRight">↻ 右旋转</el-button>
						</div>
					</div>
					<div slot="footer" class="dialog-footer">
						<el-button size="small" type="primary" @click="submitCrop" >
							<span>确 定</span>
						</el-button>
						<el-button size="small" @click="closeCrop">取 消</el-button>
					</div>
				</el-dialog>
			`,
			props: {
				cropDiaShow: {
					type: Boolean,
					default: false
				},
				imgbase64:{
					type:String,
				},
				cropFileName:{
					type:String,
				},
				cropsize:{
					type:String,
				},
				rename:{
					type:String,
				},
				only_param:{
					type: Number,
					default: null
				},
				coverage_tfadmin:{
					type: Number,
					default: null
				}
			},
			watch:{
				imgbase64(val){
					let size = this.cropsize.replace("，",",").split(",")
					this.example.autoCropWidth = size[0]
					this.example.autoCropHeight = size[1]
					this.example.img = val
				},
			},
			data() {
				return {
					loading: false,
					progressPercent:0,
					progress:false,
					example: {
						img: '', 					// 裁剪图片的地址	可选值：url 地址 || base64 || blob
						info: true,                 // 裁剪框的大小信息	可选值：true || false
						size: 1,                    // 裁剪生成图片的质量    可选值：0.1 - 1
						outputType:'png',           // 裁剪生成图片的格式    可选值：jpeg || png || webp
						canScale: true,             // 图片是否允许滚轮缩放     可选值：true || false
						autoCrop: true,             // 是否默认生成截图框
						canMove:true,	              // 上传图片是否可以移动 可选值：true | false
						fixedBox:false,             // 固定截图框大小 不允许改变  可选值：true | false
						centerBox:false,            // 截图框是否被限制在图片里面
						autoCropWidth: '',
						autoCropHeight: '',
						fixed: false,                // 开启宽度和高度比例
						fixedNumber: [1, 1]
					},
				}
			},
			methods:{
				submitCrop(){
					this.loading = true
					let that = this;
					this.$refs.cropper.getCropBlob(async (data) => {
						const time = new Date().getTime()
						let file = new window.File([data], this.cropFileName, {
							type: data.type,
						});
						this.upload(file)
					})
				},
				upload(file){
					console.log('only_param', this.only_param)
					let formdata = new FormData()
					let ali = new FormData()  //此处非常坑 阿里上传file属性必须要放到最后  所以只能赋值对象
					formdata.append('file', file)
					formdata.append('rename', this.rename)
					formdata.append('only_param', this.only_param)
			        formdata.append('coverage_tfadmin', this.coverage_tfadmin)
					const config = {
						onUploadProgress: progressEvent => {
							if (progressEvent.lengthComputable) {
								this.progress = true
								this.progressPercent = Number((progressEvent.loaded / progressEvent.total * 100).toFixed(2))
							}
						}
					}
					try {
						axios.post(base_url+'/Upload/upload', formdata).then(res => {
							if(res.data.status == 200){
								if(res.data.filestatus){
									this.setFilePath(file,res.data.data)
								}else{
									if(res.data.data.type == 'qiniuyun'){
										formdata.append('token',res.data.data.token)
										formdata.append('key',res.data.data.key)
										axios.post(res.data.data.serverurl, formdata,config).then(ret => {
											if(ret.data.key){
												axios.post(base_url+'/Upload/createFile',{filepath:res.data.data.domain+'/'+ret.data.key}).then(result => {
													this.setFilePath(file,res.data.data.domain+'/'+ret.data.key)
												})
											}
										})
									}else if(res.data.data.type == 'tencent'){
										let vm = this
										$.getScript(base_dir+"/assets/js/cos-js-sdk-v5.min.js",function(script){
											var cos = new COS({
												SecretId: res.data.data.SecretId,
												SecretKey: res.data.data.SecretKey
											});
											cos.putObject({
												Bucket: res.data.data.Bucket, // 桶名-APPID  必须
												Region: res.data.data.Region, //区域 必须
												Schema: res.data.data.Schema, //区域 必须
												Key: res.data.data.key, //文件名必须
												Body: file, // 上传文件对象
												onProgress: function (progressData) {
												}
											}, function (err, data) {
												if(data.Location){
													const url = res.data.data.Schema+'://'+data.Location
													axios.post(base_url+'/Upload/createFile',{filepath:url}).then(result => {
														vm.setFilePath(file,url)
													})
												}
											})
										})
									}else if(res.data.data.type == 'ali'){
										ali.append('Signature',res.data.data.sign)
										ali.append('callback',res.data.data.callback)
										ali.append('OSSAccessKeyId',res.data.data.OSSAccessKeyId)
										ali.append('policy',res.data.data.policy)
										ali.append('key',res.data.data.key)
										ali.append('file', file)
										axios.post(res.data.data.serverurl, ali,config).then(ret => {
											if(ret.data.code == 1){
												axios.post(base_url+'/Upload/createFile',{filepath:ret.data.data}).then(result => {
													this.setFilePath(file,ret.data.data)
												})
											}
										})
									}else{
										this.setFilePath(file,res.data.data)
									}
								}
							}else{
								this.$message.error('上传失败')
							}
						})
					}catch (error) {
						this.$message.error('上传失败')
					}finally {
						this.$refs.upload && this.$refs.upload.clearFiles()
					}
				},
				setFilePath(item,filepath){
					this.$emit('update:siglepic', filepath)
					this.$emit('update:cropDiaShow', false)
				},
				//图片缩放
				changeScale (num) {
					num = num || 1
					this.$refs.cropper.changeScale(num)
				},
				//向左旋转
				rotateLeft () {
					this.$refs.cropper.rotateLeft()
				},
				//向右旋转
				rotateRight () {
					this.$refs.cropper.rotateRight()
				},
				closeCrop(){
					this.$emit('update:cropDiaShow', false)
					this.example.img = ""
				}
			}
		},
	},
	props: {
		scene:{
			type:String,
		},
		file_ext:{
			type:String,
		},
		image:{
			type:String,
		},
		images: {
			type: Array,
			default:()=>[]
		},
		file:{
			type:String,
		},
		files:{
			type:Array,
			default:()=>[]
		},
		upload_type:{
			type:String,
			default:'1',
		},
		file_type:{
			type:String,
			default: null
		},
		upload_config_id:{
			type:Number,
		},
		rename:{
			type:String,
			default:'1',
		},
		cropsize:{
			type:String,
		},
		only_param:{
			type: Number,
			default: null
		},
		coverage_tfadmin: {
			type: Number,
			default: null
		}
	},
	watch: {
		image() {
			this.siglepic = this.image
		},
		images(){
			this.imageList = this.images
		},
		file(){
			this.sigleFile = this.file
		},
		files(){
			this.fileList = this.files
		},
		siglepic(newVal,oldVal){
			if(newVal && oldVal && oldVal !== newVal){
				this.deleteFile(oldVal)
			}
			this.$emit('update:image',this.siglepic)
		},
		sigleFile(newVal,oldVal){
			if(newVal && oldVal && oldVal !== newVal){
				this.deleteFile(oldVal)
			}
			this.$emit('update:file',this.sigleFile)
		}
	},
	data() {
		return {
			loading: false,
			siglepic: this.image,
			imageList:this.images,
			sigleFile:this.file,
			fileList:this.files,
			showtitle:false,
			progressPercent:0,
			progress:false,
			imagesDialogStatus : false,
			cropDialogStatus : false,
			imgbase64:'',
			cropFileName:'',
		};
	},
	methods:{
		beforeUpload(item){
			const fileName = item.name;
			const fileType = fileName.substring(fileName.lastIndexOf('.')+1);
			if(this.cropsize !== "" && this.cropsize !== undefined && ["jpg","jpeg","png","bmp"].includes(fileType)){
				this.cropDialogStatus = true
				this.cropFileName = item.name
				if (window.FileReader) {
					let reader = new FileReader();
					reader.readAsDataURL(item)
					reader.onload = e => {
						this.imgbase64 = e.target.result
					}
				}
				return false
			}else{
				if(this.file_ext){
					const file_exts = this.file_ext.split(',')
					if(!file_exts.includes(fileType)){
						this.$message.error('文件格式错误,不支持'+fileType+'格式')
						return false
					}
				}
				this.loading = true
			}
		},
		upload(item){
			console.log('only_param', this.only_param)
			let formdata = new FormData()
			let ali = new FormData()  //此处非常坑 阿里上传file属性必须要放到最后  所以只能赋值对象
			formdata.append('file', item.file)
			formdata.append('upload_config_id', this.upload_config_id)
			formdata.append('rename', this.rename)
			formdata.append('only_param', this.only_param)
			formdata.append('coverage_tfadmin', this.coverage_tfadmin)
			const config = {
				onUploadProgress: progressEvent => {
					if(progressEvent.lengthComputable) {
						this.progress = true
						this.progressPercent = Number((progressEvent.loaded / progressEvent.total * 100).toFixed(2))
					}
				}
			}
			try {
				axios.post(base_url+'/Upload/upload', formdata,config).then(res => {
					if (res.data.status == 200) {
						if(res.data.filestatus){
							this.setFilePath(item,res.data.data)
						}else{
							if(res.data.data.type == 'qiniuyun'){
								formdata.append('token',res.data.data.token)
								formdata.append('key',res.data.data.key)
								axios.post(res.data.data.serverurl, formdata,config).then(ret => {
									if(ret.data.key){
										axios.post(base_url+'/Upload/createFile',{filepath:res.data.data.domain+'/'+ret.data.key}).then(result => {
											this.setFilePath(item,res.data.data.domain+'/'+ret.data.key)
										})
									}
								})
							}else if(res.data.data.type == 'tencent'){
								let vm = this
								$.getScript(base_dir+"/assets/js/cos-js-sdk-v5.min.js",function(script){
									var cos = new COS({
										SecretId: res.data.data.SecretId,
										SecretKey: res.data.data.SecretKey
									});
									cos.putObject({
										Bucket: res.data.data.Bucket, // 桶名-APPID  必须
										Region: res.data.data.Region, //区域 必须
										Schema: res.data.data.Schema, //区域 必须
										Key: res.data.data.key, //文件名必须
										Body: item.file, // 上传文件对象
										onProgress: function (progressData) {
										}
									}, function (err, data) {
										if(data.Location){
											const url = res.data.data.Schema+'://'+data.Location
											axios.post(base_url+'/Upload/createFile',{filepath:url}).then(result => {
												vm.setFilePath(item,url)
											})
										}
									})
								})
							}else if(res.data.data.type == 'ali'){
								ali.append('Signature',res.data.data.sign)
								ali.append('callback',res.data.data.callback)
								ali.append('OSSAccessKeyId',res.data.data.OSSAccessKeyId)
								ali.append('policy',res.data.data.policy)
								ali.append('key',res.data.data.key)
								ali.append('file', item.file)
								axios.post(res.data.data.serverurl, ali,config).then(ret => {
									if(ret.data.code == 1){
										axios.post(base_url+'/Upload/createFile',{filepath:ret.data.data}).then(result => {
											this.setFilePath(item,ret.data.data)
										})
									}
								})
							}else{
								this.setFilePath(item,res.data.data)
							}
						}
					}else{
						this.$message.error(res.data.msg)
					}
				})
			}catch (error) {
				this.$message.error('上传失败')
			}finally {
				this.loading = false
				this.$refs.upload && this.$refs.upload.clearFiles()
			}
		},
		setFilePath(item,filepath){
			this.progressPercent = 0
			this.progress = false
			if(this.file_type == 'images'){
				this.imageList.push({url: filepath,name:item.file.name})
				this.$emit('update:images',this.imageList)
			}else if(this.file_type == 'image'){
				this.siglepic = filepath
				this.$emit('update:image',filepath)
			}else if(this.file_type == 'files'){
				this.fileList.push({url: filepath,name:item.file.name})
				this.$emit('update:files',this.fileList)
			}else{
				this.sigleFile = filepath
				this.$emit('update:file',filepath)
			}
		},
		handleRemove(type,index,file) {
			this.$confirm('是否删除文件?', '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(() => {
				if(type == 'image'){
					console.log('this.siglepic',this.siglepic)
					this.deleteFile(this.siglepic)
					this.siglepic = ''
					this.$emit('update:image','')
				}else if(type == 'images'){
					this.imageList.splice(index, 1)
					this.$emit('update:images',this.imageList)
				}else if(type == 'file'){
					this.deleteFile(this.sigleFile)
					this.sigleFile = ''
					this.$emit('update:file','')
				}else{
					this.fileList.splice(index, 1)
					this.$emit('update:files',this.fileList)
				}
				if(file){
					this.deleteFile(file)
				}
			}).catch(() => {})
		},
		handlePictureCardPreview(i){
			this.showtitle = !this.showtitle
		},
		deleteFile(val){
			console.log('deleteFile-val',val)
			if (this.only_param) {
				axios.post(base_url+'/Base/deleteFile', {filepath:val,only_param:this.only_param})
			}
		},
		openImagesPick(){
			this.imagesDialogStatus = true
		},
		changeSort(){
			this.$emit('update:images',this.imageList)
		},
		crop(){
			this.cropDialogStatus = true
		},
		remoteFile(event){
			const clipboardData = window.clipboardData || event.clipboardData || event.originalEvent.clipboardData;
			const items = clipboardData.items;
			for (let i = 0; i < items.length; i++){
				if(items[i].getAsFile()){
					let files = {file: null}
					files.file = items[i].getAsFile()
					this.upload(files)
				}
			}
		}
	}
});


//标签组件
Vue.component('Tag', {
	template:`
		<div>
			<el-tag v-for="(tag,index) in dynamicTags" :key="index" :closable="!disabled" type="success" :disable-transitions="false" @click="editTag(tag,index)" @close="handleClose(tag)">
				<span v-if="index!=num">{{tag}}</span>
				<input :disabled="disabled" class="custom_input" type="text" v-model="words" v-if="index==num" ref="editInput" @keyup.enter.native="handleInput(tag,index)" @blur="handleInput(tag,index)">
			</el-tag>
			
			<el-input class="input-new-tag" v-if="inputVisible && !disabled" v-model="inputValue" ref="saveTagInput" size="small"@blur="handleInputConfirm"></el-input>
			
			<el-button v-else class="button-new-tag"size="small"  @click="showInput">{{theme}}</el-button>
		</div>
	`,
	model: {
		prop: 'tag_list',
		event: 'input'
	},
	props: {
		tag_list: {
			type: Array,
			default () {
				return []
			}
		},
		theme: {
			type: String,
			default: '+ 新标签'
		},
		disabled: {
			type: false,
		}
	},
	computed: {
		dynamicTags: {
			get(val) {
				if(val){
					return this.tag_list
				}
			},
			set(tag_list) {
				this.$emit('update:tag_list', tag_list)
			}
		}
	},
	data(){
		return {
			inputVisible: false,
			inputValue: '',
			num: -1,
			words: ''
		}
	},
	methods:{
		unique(arr) {
			let x = new Set(arr);
			return [...x];
		},
		handleClose(tag) {
			this.dynamicTags.splice(this.dynamicTags.indexOf(tag), 1);
		},
		showInput() {
			this.inputVisible = true;
			this.$nextTick(_ => {
				this.$refs.saveTagInput.$refs.input.focus();
			});
		},
		handleInputConfirm() {
			let inputValue = this.inputValue;
			if (inputValue) {
				this.dynamicTags.push(inputValue);
			}
			this.dynamicTags = this.unique(this.dynamicTags);
			this.inputVisible = false;
			this.inputValue = '';
		},
		editTag(tag, index) {
			this.num = index;
			this.$nextTick(_ => {
				this.$refs.editInput[0].focus();
			});
			this.words = tag;
		},
		handleInput(tag, index) {
			let words = this.words;
			if (words) {
				this.dynamicTags[index] = words;
			}
			this.dynamicTags = this.unique(this.dynamicTags);
			this.words = ''
			this.num = -1
		}
	}
});

//百度地图
Vue.component('BaiduMap', {
	template: `
		<div id="all">
			<el-dialog title="百度地图坐标选择器" width="600px" top="20px" :visible.sync="show" :before-close="closeForm" append-to-body>
				<el-input id="suggestId" size="small" suffix-icon="el-icon-location" v-model="address" autoComplete="off" clearable  placeholder="请输入搜索地址"></el-input>
				<div id="allmap"></div>
				<div slot="footer" class="dialog-footer">
					<el-button size="small" type="primary" @click="submit" >
						<span>确 定</span>
					</el-button>
					<el-button size="small" @click="closeForm">取 消</el-button>
				</div>
			</el-dialog>
		</div>
	`
	,
	props: {
		mapKey: {
			type: String,
			default: 'EZPCgQ6zGu6hZSmXlR'
		},
		show: {
			type: Boolean,
			default: false
		},
		address_detail:{
			type:String,
			default:null
		},
		picker:{
			type:String
		}
	},
	data() {
		let points =  this.address_detail ? JSON.parse(this.address_detail) : {lng:'',lat:'',address:''}
		return {
			lng:points.lng,
			lat:points.lat,
			address:points.address,
		};
	},
	mounted(){
		this.loadScript()
	},
	methods: {
		loadScript() {
			var script = document.createElement("script");
			script.type = "text/javascript";
			window.initMap = () => {
				this.init()
			};
			script.src ="https://api.map.baidu.com/api?v=2.0&ak=" +this.mapKey +"&callback=initMap"
			document.body.appendChild(script);
		},
		init(){
			var a = {lng:this.lng,lat:this.lat,of: "inner"}
			var map = new BMap.Map("allmap");
			map.enableScrollWheelZoom(false)
			if(this.lng && this.lat){
				var point = new BMap.Point(this.lng,this.lat);
				map.centerAndZoom(point,16)
				var myIcon = new BMap.Icon("data:image/png;base64,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", new BMap.Size(50,70));
				map.addOverlay(new BMap.Marker(a,{icon:myIcon}))
			}else{
				var geolocation = new BMap.Geolocation();
				geolocation.getCurrentPosition(function(r) {
					map.centerAndZoom(r.point, 16); //根据浏览器定位,设置城市和地图级别。
				})
			}
			var that = this;
			//地图点击事件。获取点击位置经纬度位置
			map.addEventListener('click', function(e) {
				that.lng = e.point.lng
				that.lat = e.point.lat
				map.clearOverlays();
				map.addOverlay(new BMap.Marker(e.point))
				var point = new BMap.Point(e.point.lng, e.point.lat);
				var gc = new BMap.Geocoder();
				gc.getLocation(point, function (rs) {
					var addComp = rs.addressComponents;
					that.address = addComp.province + addComp.city + addComp.district + addComp.street
				})
			})
			
			var ac = new BMap.Autocomplete( //建立一个自动完成的对象
				{
					"input": "suggestId",
					"location": map
				});
			ac.setInputValue(that.address)
			var myValue;
			ac.addEventListener("onconfirm", function(e) { //鼠标点击下拉列表后的事件
				var _value = e.item.value;
				myValue = _value.province + _value.city + _value.district + _value.street + _value.business;
				that.address = myValue
				map.clearOverlays(); //清除地图上所有覆盖物
				function myFun() {
					var pp = local.getResults().getPoi(0).point; //获取第一个智能搜索的结果
					that.lng = pp.lng
					that.lat = pp.lat
					map.centerAndZoom(pp, 16);
					var myIcon = new BMap.Icon("data:image/png;base64,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", new BMap.Size(50,70));
					map.addOverlay(new BMap.Marker(pp,{icon:myIcon})); //添加标注
				}
				var local = new BMap.LocalSearch(map, { //智能搜索
					onSearchComplete: myFun
				});
				local.search(myValue);
			})
		},
		submit(){
			let address = {address:this.address,lng:this.lng,lat:this.lat}
			this.$emit('update:address_detail', JSON.stringify(address))
			this.closeForm()
		},
		closeForm(){
			this.$emit('update:show', false)
		}
	},
});


//高德地图
Vue.component('GaodeMap', {
	template: `
		<div id="all">
			<el-dialog title="高德地图坐标选择器" width="600px" top="20px" :visible.sync="show" :before-close="closeForm" append-to-body>
				<el-input id="keyword" size="small" suffix-icon="el-icon-location" v-model="address" autoComplete="off" clearable  placeholder="请输入搜索地址"></el-input>
				<div id="mapContainer"></div>
				<div slot="footer" class="dialog-footer">
					<el-button size="small" type="primary" @click="submit" >
						<span>确 定</span>
					</el-button>
					<el-button size="small" @click="closeForm">取 消</el-button>
				</div>
			</el-dialog>
		</div>
	`
	,
	props: {
		mapKey: {
			type: String,
			default: '07ebdc228572e133ca7e6dd6dad431c2'
		},
		show: {
			type: Boolean,
			default: false
		},
		address_detail:{
			type:String,
			default:null
		},
		picker:{
			type:String
		}
	},
	data() {
		let points =  this.address_detail ? JSON.parse(this.address_detail) : {lng:'',lat:'',address:''}
		return {
			lng:points.lng,
			lat:points.lat,
			zoom: 15,
			address: points.address,
		}
	},
	mounted() {
		this.loadScript()
	},
	methods: {
		//异步加载地图js
		loadScript() {
			var script = document.createElement("script");
			script.type = "text/javascript";
			window._AMapSecurityConfig = {
				securityJsCode:'15ec396e198d20115e62b44f4db62f64'
			}
			window.initMap = () => {
				this.init()
			};
			script.src ="https://webapi.amap.com/maps?v=1.4.15&key=" +this.mapKey +"&callback=initMap"
			document.body.appendChild(script);
		},
		init() {
			const self = this
			AMap.plugin(['AMap.CitySearch', 'AMap.Autocomplete', 'AMap.PlaceSearch', 'AMap.Geocoder', 'AMap.ToolBar'], function () {
				var citySearch = new AMap.CitySearch()
				var map
				var placeSearch
				
				if(self.lng && self.lat){
					var map=new AMap.LngLat(self.lng, self.lat);
				}
				
				citySearch.getLocalCity(function (status, result) {
					if(status === 'complete' && result.info === 'OK') {
						map = new AMap.Map("mapContainer", {
							view:new AMap.View2D({
								resizeEnable: true,
								zoom: self.zoom,//地图显示的缩放级别
								center:map,
								keyboardEnable: false,
							})
						});
						
						if(self.lng && self.lat){
							let marker = new AMap.Marker({
								icon:new AMap.Icon({
									image: 'data:image/png;base64,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',
									size: new AMap.Size(45, 65),  //图标所处区域大小
									imageSize: new AMap.Size(45,65) //图标大小
								}),
								position: [self.lng,self.lat],
								offset: new AMap.Pixel(-13, -30)
							});
							marker.setMap(map);
						}
						
						map.addControl(new AMap.ToolBar());
						let marker, geocoder
						AMap.event.addListener(map, "click", function(e) {
							if(marker != null) {
								marker.setMap(null)
							}
							marker = new AMap.Marker({
								icon:new AMap.Icon({
									image: 'data:image/png;base64,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',
									size: new AMap.Size(45, 65),  //图标所处区域大小
									imageSize: new AMap.Size(45,65) //图标大小
								}),
								position: e.lnglat,
								offset: new AMap.Pixel(-13, -30)
							});
							marker.setMap(map);
							self.lng = e.lnglat.lng
							self.lat = e.lnglat.lat
							if(!geocoder){
								geocoder = new AMap.Geocoder({
									radius: 1000 //范围，默认：500
								});
							}
							geocoder.getAddress(e.lnglat, function(status, result) {
								if (status === 'complete'&&result.regeocode) {
									var address = result.regeocode.formattedAddress;
									self.address = address
								}else{
									log.error('根据经纬度查询地址失败')
								}
							});
						})
					}
				})
				var autoOptions = {
					input: "keyword"
				};
				var autocomplete= new AMap.Autocomplete(autoOptions);
				AMap.event.addListener(autocomplete, "select", function(e){
					placeSearch = new AMap.PlaceSearch({
						map:map
					})
					placeSearch.search(e.poi.name)
					self.address = e.poi.district+e.poi.name
					self.lng = e.poi.location.lng
					self.lat = e.poi.location.lat
				});
			})
		},
		submit(){
			let address = {address:this.address,lng:this.lng,lat:this.lat}
			this.$emit('update:address_detail', JSON.stringify(address))
			this.closeForm()
		},
		closeForm(){
			this.$emit('update:show', false)
		}
	}
});


//腾讯地图
Vue.component('TxMap', {
	template: `
		<div class="app-content">
			<el-dialog title="腾讯地图坐标选择器" width="600px" top="20px" :visible.sync="show" :before-close="closeForm" append-to-body>
            <el-input size="small" @input="getLatLngBounds()" suffix-icon="el-icon-location" v-model="address" autoComplete="off" clearable  placeholder="请输入搜索地址"></el-input>
            <div class="content"  ref="bounds">
                <div v-if="showaddress">
                  <p v-for="(i,index) in BoundsPois" :key="index" @click="selectCity(index,i)">
                      {{i.name}}
                      <span class="address">{{i.address}}</span>
                  </p>
                </div>
                <div id="map"></div>
            </div>
            <div slot="footer" class="dialog-footer">
				<el-button size="small" type="primary" @click="submit" >
					<span>确 定</span>
				</el-button>
				<el-button size="small" @click="closeForm">取 消</el-button>
            </div>
			</el-dialog>
		</div>
	`
	,
	props: {
		//地图key
		mapKey: {
			type: String,
			default: 'KZOBZ-MHMWG-IVJQG-IICXA-E3FFS-TKBZR'
		},
		//周边位置高度
		boundsHeight: {
			type: String,
			default: '200px'
		},
		show: {
			type: Boolean,
			default: false
		},
		address_detail:{
			type:String,
			default:null
		},
	},
	data() {
		let points =  this.address_detail ? JSON.parse(this.address_detail) : {lng:'',lat:'',address:''}
		return {
			lat:points.lat,
			lng:points.lng,
			address: points.address, //搜索关键字
			marker: null, //标记点
			map: null, //地图实例
			setTime: null,
			selectPosition: -1,  //选择的位置
			BoundsPois: [], //周边列表,
			showaddress:true
		};
	},
	mounted() {
		this.loadScript()
	},
	methods: {
		//异步加载地图js
		loadScript() {
			var script = document.createElement("script")
			script.type = "text/javascript";
			window.initMap = () => {
				this.init()
			};
			script.src ="https://map.qq.com/api/js?v=2.exp&key=" +this.mapKey +"&callback=initMap"
			document.body.appendChild(script);
		},
		
		//初始化地图
		init() {
			this.map = new qq.maps.Map(document.getElementById("map"), {
				center: new qq.maps.LatLng(this.lat, this.lng), //设置地图中心的
				keyboardShortcuts: false,  //禁止键盘操控
				disableDefaultUI: true,    //禁止所有控件
				zoom: 16
			});
			if(!this.lat){
				this.getLocation();
			}else{
				let latLng = new qq.maps.LatLng(this.lat, this.lng)
				this.setMarker(latLng);
			}
			
			//绑定地图点击事件
			qq.maps.event.addListener(this.map, "click", e => {
				let latLng = new qq.maps.LatLng(e.latLng.lat, e.latLng.lng)
				this.marker.setPosition(latLng)
				this.marker.setAnimation(qq.maps.MarkerAnimation.DOWN)
				this.getAddress(latLng)
			});
			//绑定地图中心点改变事件
			qq.maps.event.addListener(this.map, "center_changed", e => {
				if(this.selectPosition != -1) return;
				this.centerChanged(e);
			});
			//绑定地图开始拖动事件
			qq.maps.event.addListener(this.map, "dragstart", e => {
				//重置选择、搜索框
				this.address = '';
				this.selectPosition = -1;
			});
			//绑定地图缩放级别更改事件
			qq.maps.event.addListener(this.map, "zoom_changed", e => {
				//重置选择、搜索框
				this.address = '';
				this.selectPosition = -1;
			});
		},
		
		//地图中心点改变事件
		centerChanged(e, type) {
			let center = this.map.getCenter();
			if (this.marker) {
				this.marker.setPosition(center);
				this.marker.setAnimation(qq.maps.MarkerAnimation.DOWN);
			}
		},
		
		//查询位置
		searchCity() {
			let geocoder = new qq.maps.Geocoder({
				complete: result => {
					this.map.setCenter(result.detail.location);
					//设置标注点位置
					this.marker.setPosition(result.detail.location);
					//设置标注点下落动画
					this.marker.setAnimation(qq.maps.MarkerAnimation.DOWN);
				}
			});
			geocoder.getLocation(this.address);
		},
		
		//搜索位置，查询周边位置信息
		getLatLngBounds() {
			let geocoder = new qq.maps.Geocoder({
				complete: result => {
					let latLng = new qq.maps.LatLng(result.detail.location.lat, result.detail.location.lng)
					this.getAddress(latLng)
					this.map.setCenter(result.detail.location);
					//设置标注点位置
					this.marker.setPosition(result.detail.location);
					//设置标注点下落动画
					this.marker.setAnimation(qq.maps.MarkerAnimation.DOWN);
				}
			});
			geocoder.getLocation(this.address);
		},
		//根据经纬度进行位置解析
		getAddress(latLng) {
			let geocoder = new qq.maps.Geocoder({
				complete: result => {
					this.scrollTop();
					this.showaddress = true
					this.BoundsPois = result.detail.nearPois
				}
			});
			geocoder.getAddress(latLng);
		},
		//选择周边滚动条返回到顶部
		scrollTop () {
			this.$refs.bounds.scrollTop = 0;
		},
		
		//在列表中选择位置
		selectCity (index,row) {
			this.selectPosition = index;
			let lat = this.BoundsPois[index].latLng.lat,
				lng = this.BoundsPois[index].latLng.lng,
				latLng = new qq.maps.LatLng(lat, lng);
			//将地图中心移至当前坐标
			this.map.panTo(latLng);
			//设置标注点位置
			this.marker.setPosition(latLng);
			//设置标注点下落动画
			this.marker.setAnimation(qq.maps.MarkerAnimation.DOWN);
			
			this.address = row.address + row.name
			this.lat = lat
			this.lng = lng
			this.showaddress = false
		},
		
		//获取当前位置
		getLocation() {
			let cs = new qq.maps.CityService({
				map: this.map,
				complete: results => {
					this.latLng = [results.detail.latLng.lat, results.detail.latLng.lng];
					this.map.setCenter(results.detail.latLng);
					this.setMarker(results.detail.latLng);
				}
			});
			cs.searchLocalCity();
		},
		
		//设置底部标记点
		setMarker(center) {
			let icon = new qq.maps.MarkerImage("data:image/png;base64,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",
				new qq.maps.Size(52, 73),
				new qq.maps.Point(0, 0),
				new qq.maps.Point(12, 34),
				new qq.maps.Size(45, 65)
			);
			this.marker = new qq.maps.Marker({
				map: this.map,
				position: center
			});
			this.marker.setIcon(icon);
		},
		submit(){
			let address = {address:this.address,lng:this.lng,lat:this.lat}
			this.$emit('update:address_detail', JSON.stringify(address))
			this.closeForm()
		},
		closeForm(){
			this.$emit('update:show', false)
		}
	}
});

//wangeditor编辑器
Vue.component('WangEditor', {
	template: `
		<div id="editor" ref="editor"></div>
	`
	,
	props:{
		wangcontent:{
			type:String,
		},
		is_clear: {
			type: Boolean,
			default: false
		},
		upload_config_id:{
			type:Number,
		},
		only_param:{
			type:Number,
			default: null
		},
		coverage_tfadmin: {
			type:Number,
			default: null
		}
	},
	data() {
		return {
			editor: ""
		}
	},
	watch: {
		is_clear(val) {
			if(val) {
				this.editor.txt.clear()
			}
		}
	},
	mounted() {
		let vm=this;
		this.requireEditor(function(){
			vm.init()
		})
	},
	methods:{
		requireEditor(callback){
			let vm=this;
			$.getScript(base_dir+"/assets/editor/wangeditor/wangEditor.min.js",function(script){
				callback();
			})
		},
		init(){
			this.$nextTick(function() {
				const _this = this
				const E = window.wangEditor
				this.editor = new E(this.$refs.editor)
				this.editor.config.uploadImgMaxLength = 1
				this.editor.config.uploadFileName = 'file'
				this.editor.config.uploadImgAccept = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']
				this.editor.config.uploadImgMaxSize = 10 * 1024 * 1024
				this.editor.config.uploadImgServer = '#'
				this.editor.create()
				if(_this.wangcontent){
					this.editor.txt.html(_this.wangcontent)
				}
				this.editor.config.onchange = (html)=>{
					_this.$emit('update:wangcontent',html)
				}
				this.editor.config.uploadImgHooks = {
					error: function(xhr, editor, resData) {
						console.log('error', xhr, resData)
					},
					customInsert: function(insertImgFn, result) {
						insertImgFn(result.data)
					}
				}
				this.editor.config.customUploadImg = function (resultFiles, insertImgFn) {
					let formdata = new FormData()
					formdata.append('file', resultFiles[0])
					formdata.append('edit', true)
					formdata.append('only_param', _this.only_param)
					formdata.append('upload_config_id', _this.upload_config_id)
			        formdata.append('coverage_tfadmin', _this.coverage_tfadmin)
					axios.post(base_url+'/Upload/upload', formdata).then(res => { //
						insertImgFn(res.data.data)
					})
				}
			});
		}
	}
});


//tinymce编辑器组件
Vue.component('tinymce', {
	template: '<textarea :id="computedId" :value="htmlcontent"></textarea>',
	props: {
		id: {
			type: String,
			default:"editor"
		},
		content: {
			default:""
		},
		only_param:{
			type:Number,
			default: null
		},
		coverage_tfadmin:{
			type:Number,
			default: null
		}
	},
	data: function() {
		return {
			htmlcontent: this.content,
			objTinymce: null,
			url:'',
		}
	},
	computed: {
		computedId: function() {
			return "editor" === this.id || "" === this.id || null === this.id ? "editor-" + this.guidGenerator() : this.id
		}
	},
	mounted: function() {
		const image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
			let formdata = new FormData()
			formdata.append('file', blobInfo.blob())
			formdata.append('edit', true)
			axios.post(base_url+'/Upload/upload', formdata).then(res => {
				resolve(res.data.data)
			})
		});
		var t = this
		tinymce.init({
			target: this.$el,
			toolbar1: 'bold italic underline strikethrough fontsizeselect forecolor backcolor alignleft aligncenter alignright alignjustify bullist numlist link unlink image media code removeformat',
			toolbar2: '',
			plugins: [
				'link','lists','image','code', 'table', 'wordcount', 'media'
			],
			paste_data_images:true,
			urlconverter_callback: function(url, node, on_save, name) {
				t.url = url
				return url
			},
			init_instance_callback: function(e) {
				e.on("Change KeyUp Undo Redo",
					function(n) {
						t.$emit('update:content',e.getContent())
					}),
					e.on('paste', function (el) {
						var clipboardData = el.clipboardData || el.originalEvent.clipboardData;
						if (clipboardData && clipboardData.items) {
							var items = clipboardData.items
							for (var i = 0; i < items.length; i++) {
								var item = items[i];
								if (item.type.indexOf('image') !== -1) {
									var file = item.getAsFile()
									let formdata = new FormData()
									formdata.append('file', file)
									formdata.append('only_param', _this.only_param)
			                        formdata.append('coverage_tfadmin', _this.coverage_tfadmin)
									formdata.append('edit', true)
									axios.post(base_url+'/Upload/upload', formdata).then(res => {
										e.setContent(e.getContent().replace(t.url,res.data.data))
									})
								}
							}
						}
					}),
					t.objTinymce = e
			},
			height: 400,
			language_url: base_dir+'/assets/editor/tinymce/langs/zh_CN.js',
			language: 'zh_CN',
			branding: false,
			convert_urls:false,
			images_upload_handler: image_upload_handler,
		})
	},
	methods: {
		guidGenerator: function() {
			function t() {
				return Math.floor(65536 * (1 + Math.random())).toString(16).substring(1)
			}
			return t() + t() + "-" + t() + "-" + t() + "-" + t() + "-" + t() + t() + t()
		},
		urlToUploadObject(url, fileName) {
			return fetch(url)
				.then(response => response.blob())
				.then(blob => new File([blob], fileName, { type: blob.type }));
		}
		
	},
});


//mdeditor编辑器组件
Vue.component('EditorMd', {
	template: `
		<div :id="id">
			<textarea v-model="content" style="display:none"></textarea>
		</div>
	`,
	data:function(){
		return {
			//最终生成的编辑器
			editor:null,
			//默认选项
			defaultOptions:{
				//width: "90%",
				height: 500,
				path : base_dir+"/assets/editor/editormd/lib/",
				//markdown : md,   //动态设置的markdown文本
				codeFold : true,
				//syncScrolling : false,
				saveHTMLToTextarea : true,    // 保存 HTML 到 Textarea
				searchReplace : true,
				//watch : false,                // 关闭实时预览
				htmlDecode : "style,script,iframe|on*",            // 开启 HTML 标签解析，为了安全性，默认不开启
				//toolbar  : false,             //关闭工具栏
				//previewCodeHighlight : false, // 关闭预览 HTML 的代码块高亮，默认开启
				emoji : true,
				taskList : true,
				tocm : true,         // Using [TOCM]
				tex : true,                   // 开启科学公式TeX语言支持，默认关闭
				flowChart : true,             // 开启流程图支持，默认关闭
				sequenceDiagram : true,       // 开启时序/序列图支持，默认关闭,
				//dialogLockScreen : false,   // 设置弹出层对话框不锁屏，全局通用，默认为true
				//dialogShowMask : false,     // 设置弹出层对话框显示透明遮罩层，全局通用，默认为true
				//dialogDraggable : false,    // 设置弹出层对话框不可拖动，全局通用，默认为true
				//dialogMaskOpacity : 0.4,    // 设置透明遮罩层的透明度，全局通用，默认值为0.1
				//dialogMaskBgColor : "#000", // 设置透明遮罩层的背景颜色，全局通用，默认为#fff
				imageUpload : true,
				imageFormats : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
				imageUploadURL : base_url+"/Upload/markDownUpload",
				onload : function() {
				},
				onchange : ()=>{
					this.$emit("update:mdcontent",this.editor.getPreviewedHTML())
				}
			},
			content :this.mdcontent,
		}
	},
	props:{
		id:{
			type:String,
			default:'editor'
		},
		mdcontent:{
			type:String,
		},
		options:{
			type:Object
		}
	},
	mounted(){
		let vm=this;
		this.requireEditor(function(){
			vm.editor=editormd(vm.id,vm.getOptions())
		})
	},
	methods:{
		requireEditor(callback){
			let vm=this;
			$.getScript(base_dir+"/assets/editor/editormd/editormd.js",function(script){
				callback();
			})
			$('head').append( $('<link rel="stylesheet" type="text/css" />').attr('href', base_dir+'/assets/editor/editormd/css/editormd.min.css') );
		},
		getOptions(){
			return Object.assign(this.defaultOptions,this.options);
		},
	}
});


//分页组件
Vue.component('Page', {
	template: `
		<div :class="{'hidden':hidden}" class="pagination-container">
			<el-pagination
				:background="background"
				:current-page.sync="currentPage"
				:page-size.sync="pageSize"
				:layout="layout"
				:page-sizes="pageSizes"
				:total="total"
				@size-change="handleSizeChange"
				@current-change="handleCurrentChange"
			/>
	  </div>
	`
	,
	props: {
		total: {
			required: true,
			type: Number
		},
		page: {
			type: Number,
			default: 1
		},
		limit: {
			type: Number,
			default: 20
		},
		pageSizes: {
			type: Array,
			default() {
				return [10, 20, 50,100,200,1000]
			}
		},
		layout:{
			type:String,
			default:'total, sizes, prev, pager, next, jumper',
		},
		background:{
			type:Boolean,
			default:true,
		},
		hidden: {
			type: Boolean,
			default: false
		}
	},
	computed: {
		currentPage: {
			get() {
				return this.page
			},
			set(val) {
				this.$emit('update:page', val)
			}
		},
		pageSize: {
			get() {
				return this.limit
			},
			set(val) {
				this.$emit('update:limit', val)
				this.$emit('update:page',1)
			}
		}
	},
	methods: {
		handleSizeChange(val) {
			this.$emit('pagination', { page: this.currentPage, limit: val })
		},
		handleCurrentChange(val) {
			this.$emit('pagination', { page: val, limit: this.pageSize })
		}
	}
});


//icon图标组件
Vue.component('Icon', {
	template: `
        <el-dialog title="设置字体图标" class="icon-dialog" width="800px" top="20px" :visible.sync="iconshow" :before-close="closeForm" append-to-body>
            <div class="icon-search">
                <el-input
                    v-model="searchText"
                    placeholder="搜索图标..."
                    clearable
                    @input="filterIcons"
                >
                    <i slot="prefix" class="el-input__icon el-icon-search"></i>
                </el-input>
            </div>
            
            <!-- 添加选项卡 -->
            <el-tabs v-model="activeTab" type="card" @tab-click="handleTabClick" style="margin-top: 15px;">
                <el-tab-pane v-for="group in iconGroups" :key="group.name" :label="group.label" :name="group.name">
                    <ul class="icon-ul">
                        <li v-for="icon in getGroupIcons(group.name)" :key="icon" @click="onSelect(icon)">
                            <i :class="icon" />
                            <div>{{ icon }}</div>
                        </li>
                    </ul>
                </el-tab-pane>
            </el-tabs>
        </el-dialog>
    `,
	props: {
		iconshow: {
			type: Boolean,
			default: false
		},
		icon: {
			type: String
		},
	},
	data() {
		return {
			searchText: '',
			activeTab: 'el', // 默认激活的选项卡
			iconGroups: [
				{ name: 'el', label: 'Element UI' },
				{ name: 'fas', label: 'Font Awesome' },
				{ name: 'dripicons', label: 'Dripicons' }
			],
			// 按组分类的图标
			groupedIcons: {
				el: [
					"el-icon-platform-eleme","el-icon-eleme","el-icon-delete-solid","el-icon-delete","el-icon-s-tools","el-icon-setting","el-icon-user-solid","el-icon-user","el-icon-phone","el-icon-phone-outline","el-icon-more","el-icon-more-outline","el-icon-star-on","el-icon-star-off","el-icon-s-goods","el-icon-goods","el-icon-warning","el-icon-warning-outline","el-icon-question","el-icon-info","el-icon-remove","el-icon-circle-plus","el-icon-success","el-icon-error","el-icon-zoom-in","el-icon-zoom-out","el-icon-remove-outline","el-icon-circle-check","el-icon-circle-close","el-icon-s-help","el-icon-help","el-icon-minus","el-icon-plus","el-icon-check","el-icon-close","el-icon-picture","el-icon-picture-outline","el-icon-picture-outline-round","el-icon-upload","el-icon-upload2","el-icon-download","el-icon-camera-solid","el-icon-camera","el-icon-video-camera-solid","el-icon-video-camera","el-icon-message-solid","el-icon-bell","el-icon-s-cooperation","el-icon-s-order","el-icon-s-platform","el-icon-s-fold","el-icon-s-unfold","el-icon-s-operation","el-icon-s-promotion","el-icon-s-home","el-icon-s-release","el-icon-s-ticket","el-icon-s-management","el-icon-s-open","el-icon-s-shop","el-icon-s-marketing","el-icon-s-flag","el-icon-s-comment","el-icon-s-finance","el-icon-s-claim","el-icon-s-custom","el-icon-s-opportunity","el-icon-s-data","el-icon-s-check","el-icon-s-grid","el-icon-menu","el-icon-share","el-icon-d-caret","el-icon-caret-left","el-icon-caret-right","el-icon-caret-bottom","el-icon-caret-top","el-icon-bottom-left","el-icon-bottom-right","el-icon-back","el-icon-right","el-icon-bottom","el-icon-top","el-icon-top-left","el-icon-top-right","el-icon-arrow-left","el-icon-arrow-right","el-icon-arrow-down","el-icon-arrow-up","el-icon-d-arrow-left","el-icon-d-arrow-right","el-icon-video-pause","el-icon-video-play","el-icon-refresh","el-icon-refresh-right","el-icon-refresh-left","el-icon-finished","el-icon-sort","el-icon-sort-up","el-icon-sort-down","el-icon-rank","el-icon-loading","el-icon-view","el-icon-c-scale-to-original","el-icon-date","el-icon-edit","el-icon-edit-outline","el-icon-folder","el-icon-folder-opened","el-icon-folder-add","el-icon-folder-remove","el-icon-folder-delete","el-icon-folder-checked","el-icon-tickets","el-icon-document-remove","el-icon-document-delete","el-icon-document-copy","el-icon-document-checked","el-icon-document","el-icon-document-add","el-icon-printer","el-icon-paperclip","el-icon-takeaway-box","el-icon-search","el-icon-monitor","el-icon-attract","el-icon-mobile","el-icon-scissors","el-icon-umbrella","el-icon-headset","el-icon-brush","el-icon-mouse","el-icon-coordinate","el-icon-magic-stick","el-icon-reading","el-icon-data-line","el-icon-data-board","el-icon-pie-chart","el-icon-data-analysis","el-icon-collection-tag","el-icon-film","el-icon-suitcase","el-icon-suitcase-1","el-icon-receiving","el-icon-collection","el-icon-files","el-icon-notebook-1","el-icon-notebook-2","el-icon-toilet-paper","el-icon-office-building","el-icon-school","el-icon-table-lamp","el-icon-house","el-icon-no-smoking","el-icon-smoking","el-icon-shopping-cart-full","el-icon-shopping-cart-1","el-icon-shopping-cart-2","el-icon-shopping-bag-1","el-icon-shopping-bag-2","el-icon-sold-out","el-icon-sell","el-icon-present","el-icon-box","el-icon-bank-card","el-icon-money","el-icon-coin","el-icon-wallet","el-icon-discount","el-icon-price-tag","el-icon-news","el-icon-guide","el-icon-male","el-icon-female","el-icon-thumb","el-icon-cpu","el-icon-link","el-icon-connection","el-icon-open","el-icon-turn-off","el-icon-set-up","el-icon-chat-round","el-icon-chat-line-round","el-icon-chat-square","el-icon-chat-dot-round","el-icon-chat-dot-square","el-icon-chat-line-square","el-icon-message","el-icon-postcard","el-icon-position","el-icon-turn-off-microphone","el-icon-microphone","el-icon-close-notification","el-icon-bangzhu","el-icon-time","el-icon-odometer","el-icon-crop","el-icon-aim","el-icon-switch-button","el-icon-full-screen","el-icon-copy-document","el-icon-mic","el-icon-stopwatch","el-icon-medal-1","el-icon-medal","el-icon-trophy","el-icon-trophy-1","el-icon-first-aid-kit","el-icon-discover","el-icon-place","el-icon-location","el-icon-location-outline","el-icon-location-information","el-icon-add-location","el-icon-delete-location","el-icon-map-location","el-icon-alarm-clock","el-icon-timer","el-icon-watch-1","el-icon-watch","el-icon-lock","el-icon-unlock","el-icon-key","el-icon-service","el-icon-mobile-phone","el-icon-bicycle","el-icon-truck","el-icon-ship","el-icon-basketball","el-icon-football","el-icon-soccer","el-icon-baseball","el-icon-wind-power","el-icon-light-rain","el-icon-lightning","el-icon-heavy-rain","el-icon-sunrise","el-icon-sunrise-1","el-icon-sunset","el-icon-sunny","el-icon-cloudy","el-icon-partly-cloudy","el-icon-cloudy-and-sunny","el-icon-moon","el-icon-moon-night","el-icon-dish","el-icon-dish-1","el-icon-food","el-icon-chicken","el-icon-fork-spoon","el-icon-knife-fork","el-icon-burger","el-icon-tableware","el-icon-sugar","el-icon-dessert","el-icon-ice-cream","el-icon-hot-water","el-icon-water-cup","el-icon-coffee-cup","el-icon-cold-drink","el-icon-goblet","el-icon-goblet-full","el-icon-goblet-square","el-icon-goblet-square-full","el-icon-refrigerator","el-icon-grape","el-icon-watermelon","el-icon-cherry","el-icon-apple","el-icon-pear","el-icon-orange","el-icon-coffee","el-icon-ice-tea","el-icon-ice-drink","el-icon-milk-tea","el-icon-potato-strips","el-icon-lollipop","el-icon-ice-cream-square","el-icon-ice-cream-round",
					// 其他Element UI图标...
				],
				fas: [
					"fas fa-passport","fas fa-search","fas fa-ad","fas fa-address-book","fas fa-address-card","fas fa-adjust","fas fa-air-freshener","fas fa-align-center","fas fa-align-justify","fas fa-align-left","fas fa-align-right","fas fa-allergies","fas fa-ambulance","fas fa-american-sign-language-interpreting","fas fa-anchor","fas fa-angle-double-down","fas fa-angle-double-left","fas fa-angle-double-right","fas fa-angle-double-up","fas fa-angle-down","fas fa-angle-left","fas fa-angle-right","fas fa-angle-up","fas fa-angry","fas fa-ankh","fas fa-apple-alt","fas fa-archive","fas fa-archway","fas fa-arrow-alt-circle-down","fas fa-arrow-alt-circle-left","fas fa-arrow-alt-circle-right","fas fa-arrow-alt-circle-up","fas fa-arrow-circle-down","fas fa-arrow-circle-left","fas fa-arrow-circle-right","fas fa-arrow-circle-up","fas fa-arrow-down","fas fa-arrow-left","fas fa-arrow-right","fas fa-arrow-up","fas fa-arrows-alt","fas fa-arrows-alt-h","fas fa-arrows-alt-v","fas fa-assistive-listening-systems","fas fa-asterisk","fas fa-at","fas fa-atlas","fas fa-atom","fas fa-audio-description","fas fa-award","fas fa-baby","fas fa-baby-carriage","fas fa-backspace","fas fa-backward","fas fa-bacon","fas fa-bahai","fas fa-balance-scale","fas fa-balance-scale-left","fas fa-balance-scale-right","fas fa-ban","fas fa-band-aid","fas fa-barcode","fas fa-bars","fas fa-baseball-ball","fas fa-basketball-ball","fas fa-bath","fas fa-battery-empty","fas fa-battery-full","fas fa-battery-half","fas fa-battery-quarter","fas fa-battery-three-quarters","fas fa-bed","fas fa-beer","fas fa-bell","fas fa-bell-slash","fas fa-bezier-curve","fas fa-bible","fas fa-bicycle","fas fa-biking","fas fa-binoculars","fas fa-biohazard","fas fa-birthday-cake","fas fa-blender","fas fa-blender-phone","fas fa-blind","fas fa-blog","fas fa-bold","fas fa-bolt","fas fa-bomb","fas fa-bone","fas fa-bong","fas fa-book","fas fa-book-dead","fas fa-book-medical","fas fa-book-open","fas fa-book-reader","fas fa-bookmark","fas fa-border-all","fas fa-border-none","fas fa-border-style","fas fa-bowling-ball","fas fa-box","fas fa-box-open","fas fa-box-tissue","fas fa-boxes","fas fa-braille","fas fa-brain","fas fa-bread-slice","fas fa-briefcase","fas fa-briefcase-medical","fas fa-broadcast-tower","fas fa-broom","fas fa-brush","fas fa-bug","fas fa-building","fas fa-bullhorn","fas fa-bullseye","fas fa-burn","fas fa-bus","fas fa-bus-alt","fas fa-business-time","fas fa-calculator","fas fa-calendar","fas fa-calendar-alt","fas fa-calendar-check","fas fa-calendar-day","fas fa-calendar-minus","fas fa-calendar-plus","fas fa-calendar-times","fas fa-calendar-week","fas fa-camera","fas fa-camera-retro","fas fa-campground","fas fa-candy-cane","fas fa-cannabis","fas fa-capsules","fas fa-car","fas fa-car-alt","fas fa-car-battery","fas fa-car-crash","fas fa-car-side","fas fa-caravan","fas fa-caret-down","fas fa-caret-left","fas fa-caret-right","fas fa-caret-square-down","fas fa-caret-square-left","fas fa-caret-square-right","fas fa-caret-square-up","fas fa-caret-up","fas fa-carrot","fas fa-cart-arrow-down","fas fa-cart-plus","fas fa-cash-register","fas fa-cat","fas fa-certificate","fas fa-chair","fas fa-chalkboard","fas fa-chalkboard-teacher","fas fa-charging-station","fas fa-chart-area","fas fa-chart-bar","fas fa-chart-line","fas fa-chart-pie","fas fa-check","fas fa-check-circle","fas fa-check-double","fas fa-check-square","fas fa-cheese","fas fa-chess","fas fa-chess-bishop","fas fa-chess-board","fas fa-chess-king","fas fa-chess-knight","fas fa-chess-pawn","fas fa-chess-queen","fas fa-chess-rook","fas fa-chevron-circle-down","fas fa-chevron-circle-left","fas fa-chevron-circle-right","fas fa-chevron-circle-up","fas fa-chevron-down","fas fa-chevron-left","fas fa-chevron-right","fas fa-chevron-up","fas fa-child","fas fa-church","fas fa-circle","fas fa-circle-notch","fas fa-city","fas fa-clinic-medical","fas fa-clipboard","fas fa-clipboard-check","fas fa-clipboard-list","fas fa-clock","fas fa-clone","fas fa-closed-captioning","fas fa-cloud","fas fa-cloud-download-alt","fas fa-cloud-meatball","fas fa-cloud-moon","fas fa-cloud-moon-rain","fas fa-cloud-rain","fas fa-cloud-showers-heavy","fas fa-cloud-sun","fas fa-cloud-sun-rain","fas fa-cloud-upload-alt","fas fa-cocktail","fas fa-code","fas fa-code-branch","fas fa-coffee","fas fa-cog","fas fa-cogs","fas fa-coins","fas fa-columns","fas fa-comment","fas fa-comment-alt","fas fa-comment-dollar","fas fa-comment-dots","fas fa-comment-medical","fas fa-comment-slash","fas fa-comments","fas fa-comments-dollar","fas fa-compact-disc","fas fa-compass","fas fa-compress","fas fa-compress-alt","fas fa-compress-arrows-alt","fas fa-concierge-bell","fas fa-cookie","fas fa-cookie-bite","fas fa-copy","fas fa-copyright","fas fa-couch","fas fa-credit-card","fas fa-crop","fas fa-crop-alt","fas fa-cross","fas fa-crosshairs","fas fa-crow","fas fa-crown","fas fa-crutch","fas fa-cube","fas fa-cubes","fas fa-cut","fas fa-database","fas fa-deaf","fas fa-democrat","fas fa-desktop","fas fa-dharmachakra","fas fa-diagnoses","fas fa-dice","fas fa-dice-d20","fas fa-dice-d6","fas fa-dice-five","fas fa-dice-four","fas fa-dice-one","fas fa-dice-six","fas fa-dice-three","fas fa-dice-two","fas fa-digital-tachograph","fas fa-directions","fas fa-disease","fas fa-divide","fas fa-dizzy","fas fa-dna","fas fa-dog","fas fa-dollar-sign","fas fa-dolly","fas fa-dolly-flatbed","fas fa-donate","fas fa-door-closed","fas fa-door-open","fas fa-dot-circle","fas fa-dove","fas fa-download","fas fa-drafting-compass","fas fa-dragon","fas fa-draw-polygon","fas fa-drum","fas fa-drum-steelpan","fas fa-drumstick-bite","fas fa-dumbbell","fas fa-dumpster","fas fa-dumpster-fire","fas fa-dungeon","fas fa-edit","fas fa-egg","fas fa-eject","fas fa-ellipsis-h","fas fa-ellipsis-v","fas fa-envelope","fas fa-envelope-open","fas fa-envelope-open-text","fas fa-envelope-square","fas fa-equals","fas fa-eraser","fas fa-ethernet","fas fa-euro-sign","fas fa-exchange-alt","fas fa-exclamation","fas fa-exclamation-circle","fas fa-exclamation-triangle","fas fa-expand","fas fa-expand-alt","fas fa-expand-arrows-alt","fas fa-external-link-alt","fas fa-external-link-square-alt","fas fa-eye","fas fa-eye-dropper","fas fa-eye-slash","fas fa-fan","fas fa-fast-backward","fas fa-fast-forward","fas fa-faucet","fas fa-fax","fas fa-feather","fas fa-feather-alt","fas fa-female","fas fa-fighter-jet","fas fa-file","fas fa-file-alt","fas fa-file-archive","fas fa-file-audio","fas fa-file-code","fas fa-file-contract","fas fa-file-csv","fas fa-file-download","fas fa-file-excel","fas fa-file-export","fas fa-file-image","fas fa-file-import","fas fa-file-invoice","fas fa-file-invoice-dollar","fas fa-file-medical","fas fa-file-medical-alt","fas fa-file-pdf","fas fa-file-powerpoint","fas fa-file-prescription","fas fa-file-signature","fas fa-file-upload","fas fa-file-video","fas fa-file-word","fas fa-fill","fas fa-fill-drip","fas fa-film","fas fa-filter","fas fa-fingerprint","fas fa-fire","fas fa-fire-alt","fas fa-fire-extinguisher","fas fa-first-aid","fas fa-fish","fas fa-fist-raised","fas fa-flag","fas fa-flag-checkered","fas fa-flag-usa","fas fa-flask","fas fa-flushed","fas fa-folder","fas fa-folder-minus","fas fa-folder-open","fas fa-folder-plus","fas fa-font","fas fa-football-ball","fas fa-forward","fas fa-frog","fas fa-frown","fas fa-frown-open","fas fa-funnel-dollar","fas fa-futbol","fas fa-gamepad","fas fa-gas-pump","fas fa-gavel","fas fa-gem","fas fa-genderless","fas fa-ghost","fas fa-gift","fas fa-gifts","fas fa-glass-cheers","fas fa-glass-martini","fas fa-glass-martini-alt","fas fa-glass-whiskey","fas fa-glasses","fas fa-globe","fas fa-globe-africa","fas fa-globe-americas","fas fa-globe-asia","fas fa-globe-europe","fas fa-golf-ball","fas fa-gopuram","fas fa-graduation-cap","fas fa-greater-than","fas fa-greater-than-equal","fas fa-grimace","fas fa-grin","fas fa-grin-alt","fas fa-grin-beam","fas fa-grin-beam-sweat","fas fa-grin-hearts","fas fa-grin-squint","fas fa-grin-squint-tears","fas fa-grin-stars","fas fa-grin-tears","fas fa-grin-tongue","fas fa-grin-tongue-squint","fas fa-grin-tongue-wink","fas fa-grin-wink","fas fa-grip-horizontal","fas fa-grip-lines","fas fa-grip-lines-vertical","fas fa-grip-vertical","fas fa-guitar","fas fa-h-square","fas fa-hamburger","fas fa-hammer","fas fa-hamsa","fas fa-hand-holding","fas fa-hand-holding-heart","fas fa-hand-holding-medical","fas fa-hand-holding-usd","fas fa-hand-holding-water","fas fa-hand-lizard","fas fa-hand-middle-finger","fas fa-hand-paper","fas fa-hand-peace","fas fa-hand-point-down","fas fa-hand-point-left","fas fa-hand-point-right","fas fa-hand-point-up","fas fa-hand-pointer","fas fa-hand-rock","fas fa-hand-scissors","fas fa-hand-sparkles","fas fa-hand-spock","fas fa-hands","fas fa-hands-helping","fas fa-hands-wash","fas fa-handshake","fas fa-handshake-alt-slash","fas fa-handshake-slash","fas fa-hanukiah","fas fa-hard-hat","fas fa-hashtag","fas fa-hat-cowboy","fas fa-hat-cowboy-side","fas fa-hat-wizard","fas fa-hdd","fas fa-head-side-cough","fas fa-head-side-cough-slash","fas fa-head-side-mask","fas fa-head-side-virus","fas fa-heading","fas fa-headphones","fas fa-headphones-alt","fas fa-headset","fas fa-heart","fas fa-heart-broken","fas fa-heartbeat","fas fa-helicopter","fas fa-highlighter","fas fa-hiking","fas fa-hippo","fas fa-history","fas fa-hockey-puck","fas fa-holly-berry","fas fa-home","fas fa-horse","fas fa-horse-head","fas fa-hospital","fas fa-hospital-alt","fas fa-hospital-symbol","fas fa-hospital-user","fas fa-hot-tub","fas fa-hotdog","fas fa-hotel","fas fa-hourglass","fas fa-hourglass-end","fas fa-hourglass-half","fas fa-hourglass-start","fas fa-house-damage","fas fa-house-user","fas fa-hryvnia","fas fa-i-cursor","fas fa-ice-cream","fas fa-icicles","fas fa-icons","fas fa-id-badge","fas fa-id-card","fas fa-id-card-alt","fas fa-igloo","fas fa-image","fas fa-images","fas fa-inbox","fas fa-indent","fas fa-industry","fas fa-infinity","fas fa-info","fas fa-info-circle","fas fa-italic","fas fa-jedi","fas fa-joint","fas fa-journal-whills","fas fa-kaaba","fas fa-key","fas fa-keyboard","fas fa-khanda","fas fa-kiss","fas fa-kiss-beam","fas fa-kiss-wink-heart","fas fa-kiwi-bird","fas fa-landmark","fas fa-language","fas fa-laptop","fas fa-laptop-code","fas fa-laptop-house","fas fa-laptop-medical","fas fa-laugh","fas fa-laugh-beam","fas fa-laugh-squint","fas fa-laugh-wink","fas fa-layer-group","fas fa-leaf","fas fa-lemon","fas fa-less-than","fas fa-less-than-equal","fas fa-level-down-alt","fas fa-level-up-alt","fas fa-life-ring","fas fa-lightbulb","fas fa-link","fas fa-lira-sign","fas fa-list","fas fa-list-alt","fas fa-list-ol","fas fa-list-ul","fas fa-location-arrow","fas fa-lock","fas fa-lock-open","fas fa-long-arrow-alt-down","fas fa-long-arrow-alt-left","fas fa-long-arrow-alt-right","fas fa-long-arrow-alt-up","fas fa-low-vision","fas fa-luggage-cart","fas fa-lungs","fas fa-lungs-virus","fas fa-magic","fas fa-magnet","fas fa-mail-bulk","fas fa-male","fas fa-map","fas fa-map-marked","fas fa-map-marked-alt","fas fa-map-marker","fas fa-map-marker-alt","fas fa-map-pin","fas fa-map-signs","fas fa-marker","fas fa-mars","fas fa-mars-double","fas fa-mars-stroke","fas fa-mars-stroke-h","fas fa-mars-stroke-v","fas fa-mask","fas fa-medal","fas fa-medkit","fas fa-meh","fas fa-meh-blank","fas fa-meh-rolling-eyes","fas fa-memory","fas fa-menorah","fas fa-mercury","fas fa-meteor","fas fa-microchip","fas fa-microphone","fas fa-microphone-alt","fas fa-microphone-alt-slash","fas fa-microphone-slash","fas fa-microscope","fas fa-minus","fas fa-minus-circle","fas fa-minus-square","fas fa-mitten","fas fa-mobile","fas fa-mobile-alt","fas fa-money-bill","fas fa-money-bill-alt","fas fa-money-bill-wave","fas fa-money-bill-wave-alt","fas fa-money-check","fas fa-money-check-alt","fas fa-monument","fas fa-moon","fas fa-mortar-pestle","fas fa-mosque","fas fa-motorcycle","fas fa-mountain","fas fa-mouse","fas fa-mouse-pointer","fas fa-mug-hot","fas fa-music","fas fa-network-wired","fas fa-neuter","fas fa-newspaper","fas fa-not-equal","fas fa-notes-medical","fas fa-object-group","fas fa-object-ungroup","fas fa-oil-can","fas fa-om","fas fa-otter","fas fa-outdent","fas fa-pager","fas fa-paint-brush","fas fa-paint-roller","fas fa-palette","fas fa-pallet","fas fa-paper-plane","fas fa-paperclip","fas fa-parachute-box","fas fa-paragraph","fas fa-parking","fas fa-pastafarianism","fas fa-paste","fas fa-pause","fas fa-pause-circle","fas fa-paw","fas fa-peace","fas fa-pen","fas fa-pen-alt","fas fa-pen-fancy","fas fa-pen-nib","fas fa-pen-square","fas fa-pencil-alt","fas fa-pencil-ruler","fas fa-people-arrows","fas fa-people-carry","fas fa-pepper-hot","fas fa-percent","fas fa-percentage","fas fa-person-booth","fas fa-phone","fas fa-phone-alt","fas fa-phone-slash","fas fa-phone-square","fas fa-phone-square-alt","fas fa-phone-volume","fas fa-photo-video","fas fa-piggy-bank","fas fa-pills","fas fa-pizza-slice","fas fa-place-of-worship","fas fa-plane","fas fa-plane-arrival","fas fa-plane-departure","fas fa-plane-slash","fas fa-play","fas fa-play-circle","fas fa-plug","fas fa-plus","fas fa-plus-circle","fas fa-plus-square","fas fa-podcast","fas fa-poll","fas fa-poll-h","fas fa-poo","fas fa-poo-storm","fas fa-poop","fas fa-portrait","fas fa-pound-sign","fas fa-power-off","fas fa-pray","fas fa-praying-hands","fas fa-prescription","fas fa-prescription-bottle","fas fa-prescription-bottle-alt","fas fa-print","fas fa-procedures","fas fa-project-diagram","fas fa-pump-medical","fas fa-pump-soap","fas fa-puzzle-piece","fas fa-qrcode","fas fa-question","fas fa-question-circle","fas fa-quidditch","fas fa-quote-left","fas fa-quote-right","fas fa-quran","fas fa-radiation","fas fa-radiation-alt","fas fa-rainbow","fas fa-random","fas fa-receipt","fas fa-record-vinyl","fas fa-recycle","fas fa-redo","fas fa-redo-alt","fas fa-registered","fas fa-remove-format","fas fa-reply","fas fa-reply-all","fas fa-republican","fas fa-restroom","fas fa-retweet","fas fa-ribbon","fas fa-ring","fas fa-road","fas fa-robot","fas fa-rocket","fas fa-route","fas fa-rss","fas fa-rss-square","fas fa-ruble-sign","fas fa-ruler","fas fa-ruler-combined","fas fa-ruler-horizontal","fas fa-ruler-vertical","fas fa-running","fas fa-rupee-sign","fas fa-sad-cry","fas fa-sad-tear","fas fa-satellite","fas fa-satellite-dish","fas fa-save","fas fa-school","fas fa-screwdriver","fas fa-scroll","fas fa-sd-card","fas fa-search-dollar","fas fa-search-location","fas fa-search-minus","fas fa-search-plus","fas fa-seedling","fas fa-server","fas fa-shapes","fas fa-share","fas fa-share-alt","fas fa-share-alt-square","fas fa-share-square","fas fa-shekel-sign","fas fa-shield-alt","fas fa-shield-virus","fas fa-ship","fas fa-shipping-fast","fas fa-shoe-prints","fas fa-shopping-bag","fas fa-shopping-basket","fas fa-shopping-cart","fas fa-shower","fas fa-shuttle-van","fas fa-sign","fas fa-sign-in-alt","fas fa-sign-language","fas fa-sign-out-alt","fas fa-signal","fas fa-signature","fas fa-sim-card","fas fa-sitemap","fas fa-skating","fas fa-skiing","fas fa-skiing-nordic","fas fa-skull","fas fa-skull-crossbones","fas fa-slash","fas fa-sleigh","fas fa-sliders-h","fas fa-smile","fas fa-smile-beam","fas fa-smile-wink","fas fa-smog","fas fa-smoking","fas fa-smoking-ban","fas fa-sms","fas fa-snowboarding","fas fa-snowflake","fas fa-snowman","fas fa-snowplow","fas fa-soap","fas fa-socks","fas fa-solar-panel","fas fa-sort","fas fa-sort-alpha-down","fas fa-sort-alpha-down-alt","fas fa-sort-alpha-up","fas fa-sort-alpha-up-alt","fas fa-sort-amount-down","fas fa-sort-amount-down-alt","fas fa-sort-amount-up","fas fa-sort-amount-up-alt","fas fa-sort-down","fas fa-sort-numeric-down","fas fa-sort-numeric-down-alt","fas fa-sort-numeric-up","fas fa-sort-numeric-up-alt","fas fa-sort-up","fas fa-spa","fas fa-space-shuttle","fas fa-spell-check","fas fa-spider","fas fa-spinner","fas fa-splotch","fas fa-spray-can","fas fa-square","fas fa-square-full","fas fa-square-root-alt","fas fa-stamp","fas fa-star","fas fa-star-and-crescent","fas fa-star-half","fas fa-star-half-alt","fas fa-star-of-david","fas fa-star-of-life","fas fa-step-backward","fas fa-step-forward","fas fa-stethoscope","fas fa-sticky-note","fas fa-stop","fas fa-stop-circle","fas fa-stopwatch","fas fa-stopwatch-20","fas fa-store","fas fa-store-alt","fas fa-store-alt-slash","fas fa-store-slash","fas fa-stream","fas fa-street-view","fas fa-strikethrough","fas fa-stroopwafel","fas fa-subscript","fas fa-subway","fas fa-suitcase","fas fa-suitcase-rolling","fas fa-sun","fas fa-superscript","fas fa-surprise","fas fa-swatchbook","fas fa-swimmer","fas fa-swimming-pool","fas fa-synagogue","fas fa-sync","fas fa-sync-alt","fas fa-syringe","fas fa-table","fas fa-table-tennis","fas fa-tablet","fas fa-tablet-alt","fas fa-tablets","fas fa-tachometer-alt","fas fa-tag","fas fa-tags","fas fa-tape","fas fa-tasks","fas fa-taxi","fas fa-teeth","fas fa-teeth-open","fas fa-temperature-high","fas fa-temperature-low","fas fa-tenge","fas fa-terminal","fas fa-text-height","fas fa-text-width","fas fa-th","fas fa-th-large","fas fa-th-list","fas fa-theater-masks","fas fa-thermometer","fas fa-thermometer-empty","fas fa-thermometer-full","fas fa-thermometer-half","fas fa-thermometer-quarter","fas fa-thermometer-three-quarters","fas fa-thumbs-down","fas fa-thumbs-up","fas fa-thumbtack","fas fa-ticket-alt","fas fa-times","fas fa-times-circle","fas fa-tint","fas fa-tint-slash","fas fa-tired","fas fa-toggle-off","fas fa-toggle-on","fas fa-toilet","fas fa-toilet-paper","fas fa-toilet-paper-slash","fas fa-toolbox","fas fa-tools","fas fa-tooth","fas fa-torah","fas fa-torii-gate","fas fa-tractor","fas fa-trademark","fas fa-traffic-light","fas fa-trailer","fas fa-train","fas fa-tram","fas fa-transgender","fas fa-transgender-alt","fas fa-trash","fas fa-trash-alt","fas fa-trash-restore","fas fa-trash-restore-alt","fas fa-tree","fas fa-trophy","fas fa-truck","fas fa-truck-loading","fas fa-truck-monster","fas fa-truck-moving","fas fa-truck-pickup","fas fa-tshirt","fas fa-tty","fas fa-tv","fas fa-umbrella","fas fa-umbrella-beach","fas fa-underline","fas fa-undo","fas fa-undo-alt","fas fa-universal-access","fas fa-university","fas fa-unlink","fas fa-unlock","fas fa-unlock-alt","fas fa-upload","fas fa-user","fas fa-user-alt","fas fa-user-alt-slash","fas fa-user-astronaut","fas fa-user-check","fas fa-user-circle","fas fa-user-clock","fas fa-user-cog","fas fa-user-edit","fas fa-user-friends","fas fa-user-graduate","fas fa-user-injured","fas fa-user-lock","fas fa-user-md","fas fa-user-minus","fas fa-user-ninja","fas fa-user-nurse","fas fa-user-plus","fas fa-user-secret","fas fa-user-shield","fas fa-user-slash","fas fa-user-tag","fas fa-user-tie","fas fa-user-times","fas fa-users","fas fa-users-cog","fas fa-utensil-spoon","fas fa-utensils","fas fa-vector-square","fas fa-venus","fas fa-venus-double","fas fa-venus-mars","fas fa-vial","fas fa-vials","fas fa-video","fas fa-video-slash","fas fa-vihara","fas fa-virus","fas fa-virus-slash","fas fa-viruses","fas fa-voicemail","fas fa-volleyball-ball","fas fa-volume-down","fas fa-volume-mute","fas fa-volume-off","fas fa-volume-up","fas fa-vote-yea","fas fa-vr-cardboard","fas fa-walking","fas fa-wallet","fas fa-warehouse","fas fa-water","fas fa-wave-square","fas fa-weight","fas fa-weight-hanging","fas fa-wheelchair","fas fa-wifi","fas fa-wind","fas fa-window-close","fas fa-window-maximize","fas fa-window-minimize","fas fa-window-restore","fas fa-wine-bottle","fas fa-wine-glass","fas fa-wine-glass-alt","fas fa-won-sign","fas fa-wrench","fas fa-x-ray",
					// 其他Font Awesome图标...
				],
				dripicons: [
					"dripicons-alarm","dripicons-align-center","dripicons-align-justify","dripicons-align-left","dripicons-align-right","dripicons-anchor","dripicons-archive","dripicons-arrow-down","dripicons-arrow-left","dripicons-arrow-right","dripicons-arrow-thin-down","dripicons-arrow-thin-left","dripicons-arrow-thin-right","dripicons-arrow-thin-up","dripicons-arrow-up","dripicons-article","dripicons-backspace","dripicons-basket","dripicons-basketball","dripicons-battery-empty","dripicons-battery-full","dripicons-battery-low","dripicons-battery-medium","dripicons-bell","dripicons-blog","dripicons-bluetooth","dripicons-bold","dripicons-bookmark","dripicons-bookmarks","dripicons-box","dripicons-briefcase","dripicons-brightness-low","dripicons-brightness-max","dripicons-brightness-medium","dripicons-broadcast","dripicons-browser","dripicons-browser-upload","dripicons-brush","dripicons-calendar","dripicons-camcorder","dripicons-camera","dripicons-card","dripicons-cart","dripicons-checklist","dripicons-checkmark","dripicons-chevron-down","dripicons-chevron-left","dripicons-chevron-right","dripicons-chevron-up","dripicons-clipboard","dripicons-clock","dripicons-clockwise","dripicons-cloud","dripicons-cloud-download","dripicons-cloud-upload","dripicons-code","dripicons-contract","dripicons-contract-2","dripicons-conversation","dripicons-copy","dripicons-crop","dripicons-cross","dripicons-crosshair","dripicons-cutlery","dripicons-device-desktop","dripicons-device-mobile","dripicons-device-tablet","dripicons-direction","dripicons-disc","dripicons-document","dripicons-document-delete","dripicons-document-edit","dripicons-document-new","dripicons-document-remove","dripicons-dot","dripicons-dots-2","dripicons-dots-3","dripicons-download","dripicons-duplicate","dripicons-enter","dripicons-exit","dripicons-expand","dripicons-expand-2","dripicons-experiment","dripicons-export","dripicons-feed","dripicons-flag","dripicons-flashlight","dripicons-folder","dripicons-folder-open","dripicons-forward","dripicons-gaming","dripicons-gear","dripicons-graduation","dripicons-graph-bar","dripicons-graph-line","dripicons-graph-pie","dripicons-headset","dripicons-heart","dripicons-help","dripicons-home","dripicons-hourglass","dripicons-inbox","dripicons-information","dripicons-italic","dripicons-jewel","dripicons-lifting","dripicons-lightbulb","dripicons-link","dripicons-link-broken","dripicons-list","dripicons-loading","dripicons-location","dripicons-lock","dripicons-lock-open","dripicons-mail","dripicons-map","dripicons-media-loop","dripicons-media-next","dripicons-media-pause","dripicons-media-play","dripicons-media-previous","dripicons-media-record","dripicons-media-shuffle","dripicons-media-stop","dripicons-medical","dripicons-menu","dripicons-message","dripicons-meter","dripicons-microphone","dripicons-minus","dripicons-monitor","dripicons-move","dripicons-music","dripicons-network-1","dripicons-network-2","dripicons-network-3","dripicons-network-4","dripicons-network-5","dripicons-pamphlet","dripicons-paperclip","dripicons-pencil","dripicons-phone","dripicons-photo","dripicons-photo-group","dripicons-pill","dripicons-pin","dripicons-plus","dripicons-power","dripicons-preview","dripicons-print","dripicons-pulse","dripicons-question","dripicons-reply","dripicons-reply-all","dripicons-return","dripicons-retweet","dripicons-rocket","dripicons-scale","dripicons-search","dripicons-shopping-bag","dripicons-skip","dripicons-stack","dripicons-star","dripicons-stopwatch","dripicons-store","dripicons-suitcase","dripicons-swap","dripicons-tag","dripicons-tag-delete","dripicons-tags","dripicons-thumbs-down","dripicons-thumbs-up","dripicons-ticket","dripicons-time-reverse","dripicons-to-do","dripicons-toggles","dripicons-trash","dripicons-trophy","dripicons-upload","dripicons-user","dripicons-user-group","dripicons-user-id","dripicons-vibrate","dripicons-view-apps","dripicons-view-list","dripicons-view-list-large","dripicons-view-thumb","dripicons-volume-full","dripicons-volume-low","dripicons-volume-medium","dripicons-volume-off","dripicons-wallet","dripicons-warning","dripicons-web","dripicons-weight","dripicons-wifi","dripicons-wrong","dripicons-zoom-in","dripicons-zoom-out",
					// 其他Dripicons图标...
				]
			},
			filteredIconList: [] // 保留用于搜索功能
		}
	},
	methods: {
		onSelect(icon) {
			this.$emit('update:icon', icon)
			this.closeForm()
		},
		closeForm() {
			this.$emit('update:iconshow', false)
		},
		// 获取当前分组的图标
		getGroupIcons(groupName) {
			if (!this.searchText) {
				return this.groupedIcons[groupName] || [];
			}
			return (this.groupedIcons[groupName] || []).filter(icon =>
				icon.toLowerCase().includes(this.searchText.toLowerCase())
			);
		},
		// 处理选项卡切换
		handleTabClick(tab) {
			this.activeTab = tab.name;
		},
		// 过滤图标方法保留但修改实现
		filterIcons() {
			// 现在过滤由getGroupIcons方法处理
		}
	},
	created() {
		// 初始化时显示Element UI图标
		this.filteredIconList = this.groupedIcons.el;
	}
});


//键值对组件带自定义标签
Vue.component('KeyData', {
	template: `
        <div class="jzdItem">
            <draggable v-model="jzd" v-bind="{group:'item'}" handle=".jzd-handle">
                <el-row v-for="(item,i) in jzd" :key="i">
                    <el-col :span="10">
                        <el-form-item style="margin-bottom:3px !important">
                            <el-input v-model="item.key" :placeholder="keyPlaceholder || '选项名称'"/>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item style="margin-bottom:3px !important">
                            <el-input style="position:relative;left:5px;" v-model="item.val" :placeholder="valuePlaceholder || '选项值'"/>
                        </el-form-item>
                    </el-col>
                    <el-col :span="4">
                        <el-button type="danger" size="mini" style="position:relative;left:15px"  icon="el-icon-close" @click="deleteItem(i)"></el-button>
                        <el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:15px" icon="el-icon-rank"></el-button>
                    </el-col>
                </el-row>
            </draggable>
            <div>
                <el-button type="info" icon="el-icon-plus" style="padding:5px 7px" size="mini" @click="addItem">追加</el-button>
                <el-button v-if="jzd.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" size="mini" @click="clearItem">清空</el-button>
            </div>
        </div>
    `,
	props: {
		item: {
			type: Array,
		},
		keyPlaceholder: {
			type: String,
			default: ''
		},
		valuePlaceholder: {
			type: String,
			default: ''
		}
	},
	watch: {
		jzd() {
			this.$emit('update:item', this.jzd)
		},
	},
	data() {
		return {
			jzd: this.item
		}
	},
	methods: {
		addItem() {
			this.jzd.push({})
		},
		deleteItem(index) {
			this.jzd.splice(index, 1)
		},
		clearItem() {
			this.jzd = []
		},
	}
});

// 下拉单选审批流组件
Vue.component('WorkflowData', {
	template: `
        <div class="jzdItem">
            <draggable v-model="jzd" v-bind="{group:'item'}" handle=".jzd-handle">
                <el-row v-for="(item,i) in jzd" :key="i">
                    <el-col :span="19">
                        <el-form-item style="margin-bottom:3px !important">
                            <el-select
                                style="width:100%"
                                v-model="jzd[i]"
                                filterable clearable
                                :placeholder="keyPlaceholder || '选择审核员'"
                            >
                                <el-option
                                    v-for="(option,index) in workflow"
                                    :key="index"
                                    :label="getOptionLabel(option)"
                                    :value="getOptionValue(option)"
                                >
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="5">
                        <el-button type="danger" size="mini" style="position:relative;left:15px" icon="el-icon-close" @click="deleteItem(i)"></el-button>
                        <el-button class="jzd-handle" type="success" size="mini" style="position:relative;left:15px" icon="el-icon-rank"></el-button>
                    </el-col>
                </el-row>
            </draggable>
            <div>
                <el-button type="info" icon="el-icon-plus" style="padding:5px 7px" size="mini" @click="addItem">追加</el-button>
                <el-button v-if="jzd.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" size="mini" @click="clearItem">清空</el-button>
            </div>
        </div>
    `,
	props: {
		item: {
			type: Array,
			default: () => []
		},
		workflow: {
			type: Array,
			default: () => []
		},
		keyPlaceholder: {
			type: String,
			default: '选择审核员'
		},
		// 新增：支持指定选项的标签和值字段
		labelKey: {
			type: String,
			default: 'label'
		},
		valueKey: {
			type: String,
			default: 'value'
		}
	},
	watch: {
		jzd: {
			handler(newVal) {
				this.$emit('update:item', newVal)
			},
			deep: true
		}
	},
	data() {
		return {
			jzd: Array.isArray(this.item) ? this.item : []
		}
	},
	methods: {
		getOptionLabel(option) {
			// 支持对象和字符串两种格式
			if (typeof option === 'object') {
				return option[this.labelKey] || option.label || option.key || option.name || '';
			}
			return option;
		},
		
		getOptionValue(option) {
			// 支持对象和字符串两种格式
			if (typeof option === 'object') {
				return option[this.valueKey] || option.value || option.val || option.id || '';
			}
			return option;
		},
		
		addItem() {
			this.jzd.push('')
		},
		
		deleteItem(index) {
			this.jzd.splice(index, 1)
		},
		
		clearItem() {
			this.jzd = []
		}
	}
});


//表格工具栏组件
Vue.component('tableTool', {
	template: `
    <div>
      <el-button-group>
        <el-tooltip class="item" effect="dark" :content="hsz ? '正常数据' : '回收站'" placement="top">
          <el-button v-if="hsz_status" size="small" @click="toggleHszList">
            <i :class="!hsz ? 'el-icon-delete' : 'el-icon-s-grid'"></i> {{ hsz ? '正常数据' : '回收站' }}
          </el-button>
        </el-tooltip>
        
        <el-tooltip class="item" effect="dark" :content="expand ? '菜单收起' : '菜单展开'" placement="top">
          <el-button v-if="expand_status" size="small" @click="toggleRowExpansion">
            <i :class="expand ? 'el-icon-top' : 'el-icon-bottom'"></i> {{ expand ? '收起' : '展开' }}
          </el-button>
        </el-tooltip>
        
        <el-tooltip class="item" effect="dark" :content="search_visible ? '搜索框收起' : '搜索框展开'" placement="top">
          <el-button class="hidden-sm-and-down" size="small" @click="searchshow">
            <i class="el-icon-search"></i> 搜</el-button>
        </el-tooltip>
        
        <el-tooltip class="item" effect="dark" content="刷新" placement="top">
          <el-button class="hidden-sm-and-down" size="small" @click="refesh_list">
            <i class="el-icon-refresh"></i> 刷</el-button>
        </el-tooltip>
        
        <el-tooltip class="item" effect="dark" content="打印" placement="top">
          <el-button class="hidden-sm-and-down" size="small" @click="printer">
            <i class="el-icon-printer"></i> 印</el-button>
        </el-tooltip>
      </el-button-group>
    </div>
  `,
	props: {
		expand_status: {
			type: Boolean,
			default: false,
		},
		expand: {
			type: Boolean,
			default: false,
		},
		hsz_status: {
			type: Boolean,
			default: false,
		},
		hsz: {
			type: Boolean,
			default: false,
		},
		search_visible: {
			type: Boolean,
			default: false
		},
		tableid: {
			type: String,
		}
	},
	methods: {
		searchshow() {
			this.$emit('update:search_visible', !this.search_visible)
		},
		toggleHszList() {
			this.$emit('update:hsz', !this.hsz)
			this.$emit('toggle_list')
		},
		refesh_list() {
			this.$emit('refesh_list')
		},
		toggleRowExpansion() {
			this.$emit('toggle')
		},
		printer() {
			const html = document.querySelector('#' + this.tableid).innerHTML
			const div = document.createElement('div')
			const printDOMID = 'printDOMElement'
			div.id = printDOMID
			div.innerHTML = html
			const ths = div.querySelectorAll('.el-table__header-wrapper th')
			const rows = div.querySelectorAll('.el-table__body-wrapper table tr');
			
			const ThsTextArry = []
			for (let i = 0, len = ths.length; i < len; i++) {
				if (ths[i].innerText !== '' && ths[i].innerText !== '编号' && ths[i].innerText !== '操作') ThsTextArry.push(ths[i].innerText)
			}
			const ThsHtmlArry = [];
			for (let i = 0, len = rows.length; i < len; i++) {
				const tr = document.createElement('tr');
				const tds = rows[i].querySelectorAll('td');
				for (let j = 0, tdLen = tds.length; j < tdLen; j++) {
					if (j !== 0 && j !== 1 && j !== tdLen - 1) {
						if (tds[j].innerHTML.includes('img')) {
							tr.innerHTML += "<td>" + tds[j].innerHTML + "</td>";
						} else if (tds[j].innerHTML.includes('switch')) {
							if (tds[j].innerHTML.includes('is-checked')) {
								tr.innerHTML += "<td>正常</td>";
							} else {
								tr.innerHTML += "<td>禁用</td>";
							}
						} else {
							tr.innerHTML += "<td>" + tds[j].innerText + "</td>";
						}
					}
				}
				ThsHtmlArry.push(tr.outerHTML);
			}
			let trStr = ''
			for (let i = 0, len = ThsHtmlArry.length; i < len; i++) {
				trStr += ThsHtmlArry[i]
			}
			
			let newHTML = '<tr>'
			for (let i = 0, len = ThsTextArry.length; i < len; i++) {
				newHTML += '<td style="text-align: center; font-weight: bold">' + ThsTextArry[i] + '</td>'
			}
			newHTML += '</tr>'
			
			let printStr = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head>";
			const tabStyle = "<style>table.gridtable {width:100%;font-family: verdana,arial,sans-serif;font-size:11px;color:#606266;border-width: 1px;border-color: #ddd;border-collapse: collapse;}table.gridtable th {border-width: 1px;padding: 8px;border-style: solid;border-color: #ddd;background-color: #dedede;}table.gridtable td {border-width: 1px;padding: 15px 0;border-style: solid;border-color: #ddd;background-color: #ffffff;text-align:center;}img{width:50px; height:50px;}</style><body>";
			printStr = printStr + tabStyle + "<table class='gridtable'>" + newHTML + trStr + "</body></html>";
			
			let pwin = window.open("_blank");
			pwin.document.write(printStr);
			pwin.document.close();
			pwin.focus();
			setTimeout(() => {
				pwin.print();
				pwin.close();
			}, 500);
			div.remove()
		}
	},
});

//搜索按钮
Vue.component('SearchTool', {
	template: `
	<el-form-item>
	<el-button type="primary" size="small" icon="el-icon-search" @click="refesh_list">查询</el-button>
	<el-button size="small" icon="el-icon-refresh" @click="reset">重置</el-button>
	</el-form-item>
	`
	,
	props: {
		expand_status:{
			type:Boolean,
			default:false,
		},
		expand:{
			type:Boolean,
			default:false,
		},
		search_visible:{
			type:Boolean,
			default:false
		},
		is_clear:{
			type:Boolean,
			default:false
		},
		page_data:{
			type:Object,
		},
	},
	
	methods: {
		refesh_list() {
			this.$emit('update:page_data', {page:1,limit:this.page_data.limit,total:this.page_data.total})
			this.$emit('update:is_clear', false)
			this.$emit('refesh_list')
		},
		reset(){
			this.$emit('update:search_data', {})
			this.$emit('update:is_clear', true)
			this.$emit('refesh_list')
		}
	},
});


//省市区联动
Vue.component('shengshiqu', {
	template: `
	<el-cascader :disabled="disabled" :props="checkstrictly" @change="change" filterable style="width:100%" v-model="optiondata" clearable :options="addressOptions" cle></el-cascader>
	`
	,
	props: {
		type:{
			type:Number,
		},
		treeoption:{
			type:Array,
		},
		checkstrictly:{
			type:Object,
		},
		is_clear:{
			type:Boolean,
			default:false
		},
		disabled:{
			type:false,
		},
	},
	watch:{
		is_clear(val){
			if(val){
				this.addressOptions = []
			}
		}
	},
	data(){
		return{
			optiondata:this.treeoption,
			addressOptions:[],
		}
	},
	mounted(){
		this.init()
	},
	methods: {
		init(){
			var url
			if(this.type == 1){
				url = base_dir+'/assets/libs/shengshiqu/city-data-level3.json'
			}else if(this.type == 2){
				url = base_dir+'/assets/libs/shengshiqu/city-data-level2.json'
			}else if(this.type == 3){
				url = base_dir+'/assets/libs/shengshiqu/city-data-level1.json'
			}
			axios.get(url).then(response => {
				this.addressOptions = response.data
			})
		},
		change(val){
			this.$emit('update:is_clear', false)
			this.$emit('update:treeoption',val)
		}
	},
});


//列表页左侧列表
Vue.component('LeftTree', {
	template: `
	<el-card shadow="never" class="card" style="max-height:90vh;overflow:auto">
		<el-row :gutter="10">
			<el-col :span="22">
				<el-input  placeholder="输入关键字进行过滤" size="small" style="margin-bottom:10px;"  v-model="filterText"></el-input>
			</el-col>
			<el-col :span="2">
				<el-tooltip class="item" effect="dark" :content="expand ? '收起' : '展开'" placement="top">
					<i style="margin-top:8px; font-size:20px; cursor:pointer" @click="toggleRowExpansion" :class="expand ? 'el-icon-top' : 'el-icon-bottom'"></i>
				</el-tooltip>
			</el-col>
		</el-row>
		<el-tree :props="defaultProps" :data="treelist" :default-expand-all="expand" :filter-node-method="filterNode" @node-click="handleNodeClick" ref="tree"></el-tree>
	</el-card>
	`
	,
	props: {
		treelist:{
			type:Array,
		},
		fieldname:{
			type:String
		},
		left_expand:{
			type:Boolean,
			default:true
		}
	},
	data() {
		return {
			filterText: '',
			expand:this.left_expand,
			defaultProps: {
				children: 'children',
				label: 'key'
			}
		}
	},
	watch: {
		filterText(val) {
			this.$refs.tree.filter(val);
		},
	},
	methods: {
		filterNode(value, data) {
			return data.key.indexOf(value) !== -1;
		},
		handleNodeClick(data){
			this.$emit('update:search_data', {[this.fieldname]:data.val})
			this.$emit('refesh_list')
		},
		toggleRowExpansion(){
			this.expand = !this.expand
			this.changeTreeNodeStatus(this.$refs.tree.store.root)
		},
		changeTreeNodeStatus (node) {
			node.expanded = this.expand
			for (let i = 0; i < node.childNodes.length; i++) {
				node.childNodes[i].expanded = this.expand
				if (node.childNodes[i].childNodes.length > 0) {
					this.changeTreeNodeStatus(node.childNodes[i])
				}
			}
		}
	},
});


//导入excel组件
Vue.component('import', {
	template: `
	<el-dialog title="导入excel" style="margin-top:100px;" width="600px" :visible.sync="show" @close="closeForm" append-to-body>
		<el-upload v-if="!process" class="upload-demo" action :auto-upload="false" :show-file-list="false" :on-change="choose_file">
			<el-button size="mini" icon="el-icon-upload" type="primary">请选择导入excel</el-button>
			<span v-if="file.name" style="color:#ff0000">{{file.name}}</span>
			<span v-else-if="excel"><a style="color:#ff0000" :href="excel" @click.stop target="_blank">下载导入模板</a></span>
		</el-upload>
		<el-progress v-else :percentage="percentage"></el-progress>
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
			default: true
		},
		size: {
			type: String,
			default: 'mini'
		},
		import_url:{
			type:String
		},
		excel:{
			type:String
		},
	},
	data() {
		return {
			file: "",
			process:false,
			loading:false,
			excel_import_data:[],
			percentage:0,
			page:1,
			limit:200,
		}
	},
	methods: {
		choose_file(file) {
			this.file = file
			this.importExcel(file)
		},
		importExcel(file) {
			var excelData = []
			const fileReader = new FileReader()
			fileReader.onload = (ev) => {
				try{
					const data = ev.target.result
					const workbook = XLSX.read(data, { type: "binary" })
					Object.keys(workbook.Sheets).forEach((sheet) => {
						excelData.push(
							...XLSX.utils.sheet_to_json(workbook.Sheets[sheet])
						)
					})
					this.excel_import_data = excelData
				}catch(e){
					this.$message.danger('文件类型不正确')
				}
			}
			fileReader.readAsBinaryString(file.raw)
		},
		submit(){
			let data = this.getData()
			if(data.length == 0){
				this.$message.error("请选择导入excel文件!")
				return false
			}
			this.process = true
			this.loading = true
			let total_page = Math.ceil(this.excel_import_data.length/this.limit)
			this.percentage = Math.ceil(this.page*100/total_page)
			axios.post(base_url+this.import_url,data).then(res => {
				if(res.data.status == 200){
					if(this.page <= total_page-1){
						this.page = this.page +1
						this.submit()
					}else{
						this.$message({message: '导入完成', type: 'success'})
						this.$emit('refesh_list')
						this.closeForm()
					}
				}else{
					this.$message.error(res.data.msg)
					this.loading = false
					this.percentage = 0
					this.process = false
				}
			}).catch(()=>{
				this.loading = false
			})
		},
		getData(){
			let perdata = []
			for(let i=(this.page-1)*this.limit; i<this.page*this.limit; i++){
				if(this.excel_import_data[i]){
					perdata.push(this.excel_import_data[i])
				}
			}
			return perdata
		},
		closeForm(){
			this.$emit('update:show', false)
			this.file = ''
			this.process = false
			this.percentage = 0
			this.loading = false
			this.page = 1
			this.limit = 200
		}
	}
});


//标签页组件
Vue.component('TabTag', {
	template: `
	<div id="tags-view-container" class="tags-view-container">
        <el-row style="margin:0 15px; line-height:26px;">
            <el-col :span="22">
				<scrollbar ref="scrollPane" class="tags-view-wrapper">
					<a :href="item.fullurl" v-for="item in tags" tag="span" :class="isActive(item)?'active':''" :key="item.url" class="tags-view-item">
						{{item.title}}
						<span v-if="item.title !== '首页'" style="color:#606266; font-size:50%" class="el-icon-close" @click.prevent.stop="closeSelectedTag(item)" />
					</a>
				</scrollbar>
            </el-col>
            <el-col :span="2" class="moretag">
                <el-dropdown trigger="click" style="cursor: pointer;">
                    <span class="el-dropdown-link">
                    <span class="hidden-sm-and-down">其它操作</span> <i class="icontool el-icon-arrow-down"></i>
                    </span>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item icon="el-icon-back" @click.native.prevent="closeLeftTags">关闭左侧</el-dropdown-item>
                        <el-dropdown-item icon="el-icon-right" @click.native.prevent="closeRightTags">关闭右侧</el-dropdown-item>
                        <el-dropdown-item icon="el-icon-close" @click.native.prevent="closeOthersTags">关闭其它</el-dropdown-item>
                        <el-dropdown-item icon="el-icon-circle-close" @click.native.prevent="closeAllTags">全部关闭</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-col>
        </el-row>
    </div>
	`
	,
	data(){
		return{
			tags:[],
			showtag:1,
		}
	},
	mounted(){
		this.getTag()
	},
	methods: {
		getTag(){
			var home =[{
				title:'首页',
				url :base_url+'/Index/main.html',
				fullurl :base_url+'/Index/main.html',
			}]
			if(Cookies.get(base_url+'menu')){
				this.tags = home.concat(JSON.parse(Cookies.get(base_url+'menu')))
			}else{
				this.tags = home
			}
		},
		isActive(val) {
			var myURL = new URL(window.location.href)
			return myURL.pathname === val.url || myURL.href === val.url
		},
		closeSelectedTag(val){
			if(this.deleteTags(val)){
				if(this.isActive(val)){
					this.toLastView(val)
				}
			}
		},
		deleteTags(val){
			var tags = JSON.parse(Cookies.get(base_url+'menu'))
			tags.forEach((item,i)=>{
				if(item.url == val.url){
					tags.splice(i, 1)
				}
			})
			if(Cookies.set(base_url+'menu',JSON.stringify(tags))){
				this.getTag()
			}
			return true
		},
		closeAllTags() {
			Cookies.set(base_url+'menu','')
			location.href = base_url+"/Index/main.html"
		},
		closeRightTags() {
			var myURL = new URL(window.location.href)
			var tags = JSON.parse(Cookies.get(base_url+'menu'))
			if(myURL.pathname == base_url+'/Index/main.html'){
				if(Cookies.set(base_url+'menu','')){
					this.getTag()
				}
			}else{
				const index = tags.findIndex(v => v.url === myURL.pathname)
				if(index === -1) {
					return
				}
				tags = tags.filter((item, idx) => {
					if (idx <= index) {
						return true
					}
					return false
				})
				if(Cookies.set(base_url+'menu',JSON.stringify(tags))){
					this.getTag()
				}
			}
		},
		closeLeftTags() {
			var myURL = new URL(window.location.href)
			var tags = JSON.parse(Cookies.get(base_url+'menu'))
			const index = tags.findIndex(v => v.url === myURL.pathname)
			if (index === -1) {
				return
			}
			tags = tags.filter((item, idx) => {
				if (idx >= index) {
					return true
				}
				return false
			})
			if(Cookies.set(base_url+'menu',JSON.stringify(tags))){
				this.getTag()
			}
		},
		closeOthersTags(){
			var myURL = new URL(window.location.href)
			var tags = JSON.parse(Cookies.get(base_url+'menu'))
			tags = tags.filter(v => {
				return v.url === myURL.pathname
			})
			if(Cookies.set(base_url+'menu',JSON.stringify(tags))){
				this.getTag()
			}
		},
		toLastView(tags, view) {
			var tags = JSON.parse(Cookies.get(base_url+'menu'))
			const latestView = tags.slice(-1)[0]
			if(latestView) {
				if(latestView.title == '首页'){
					location.href = base_url+"/Index/main.html"
				}else{
					location.href = latestView.url
				}
			}else {
				location.href = base_url+"/Index/main.html"
			}
		},
	},
});


//滚动条
Vue.component('scrollbar', {
	template: `
	<el-scrollbar ref="scrollContainer" :vertical="false" class="scroll-container" @wheel.native.prevent="handleScroll">
		<slot />
	</el-scrollbar>
	`
	,
	data() {
		return {
			left: 0
		}
	},
	computed: {
		scrollWrapper() {
			return this.$refs.scrollContainer.$refs.wrap
		}
	},
	mounted() {
		this.scrollWrapper.addEventListener('scroll', this.emitScroll, true)
	},
	beforeDestroy() {
		this.scrollWrapper.removeEventListener('scroll', this.emitScroll)
	},
	methods: {
		handleScroll(e) {
			const eventDelta = e.wheelDelta || -e.deltaY * 40
			const $scrollWrapper = this.scrollWrapper
			$scrollWrapper.scrollLeft = $scrollWrapper.scrollLeft + eventDelta / 4
		},
		emitScroll() {
			this.$emit('scroll')
		},
		moveToTarget(currentTag) {
			const $container = this.$refs.scrollContainer.$el
			const $containerWidth = $container.offsetWidth
			const $scrollWrapper = this.scrollWrapper
			const tagList = this.$parent.$refs.tag
			
			let firstTag = null
			let lastTag = null
			
			if(tagList.length > 0) {
				firstTag = tagList[0]
				lastTag = tagList[tagList.length - 1]
			}
			
			if(firstTag === currentTag) {
				$scrollWrapper.scrollLeft = 0
			}else if (lastTag === currentTag) {
				$scrollWrapper.scrollLeft = $scrollWrapper.scrollWidth - $containerWidth
			}else {
				const currentIndex = tagList.findIndex(item => item === currentTag)
				const prevTag = tagList[currentIndex - 1]
				const nextTag = tagList[currentIndex + 1]
				
				const afterNextTagOffsetLeft = nextTag.$el.offsetLeft + nextTag.$el.offsetWidth + 4
				
				const beforePrevTagOffsetLeft = prevTag.$el.offsetLeft - 4
				
				if (afterNextTagOffsetLeft > $scrollWrapper.scrollLeft + $containerWidth) {
					$scrollWrapper.scrollLeft = afterNextTagOffsetLeft - $containerWidth
				} else if (beforePrevTagOffsetLeft < $scrollWrapper.scrollLeft) {
					$scrollWrapper.scrollLeft = beforePrevTagOffsetLeft
				}
			}
		}
	}
});


//下拉分页
Vue.component('select-page', {
	template: `
		<el-select :disabled="disabled" remote :remote-method="remoteSearch" style="width:100%" v-model="dataval" @clear="handleClear" @change="selectchange" filterable  clearable placeholder="请选择">
			<el-option v-for="(item,i) in list" :key="i" :label="item.key" :value="item.val"></el-option>
			<Page style="padding-left:15px;" :total="page_data.total" :background="background" :layout="layout" :page.sync="page_data.page" :limit.sync="page_data.limit" @pagination="init" />
		</el-select>
	`
	,
	props: {
		input_type: {
			type: Number,
		},
		url: {
			type: String,
		},
		selectval:{
			type: [Number, String],
			default:null,
		},
		is_clear:{
			type:Boolean,
			default:false
		},
		disabled:{
			type:false,
		},
	},
	watch:{
		is_clear(val){
			if(val){
				this.dataval = ''
			}
		}
	},
	data() {
		return {
			dataval:this.selectval,
			queryString:'',
			list:[],
			page_data: {
				limit: 20,
				page: 1,
				total:20,
			},
			layout:'total,sizes, prev, pager, next',
			background:false,
		}
	},
	mounted() {
		this.init()
	},
	methods: {
		init(){
			let param = {limit:this.page_data.limit,page:this.page_data.page,queryString:this.queryString}
			if(this.input_type !== 1 ){
				Object.assign(param, {val:this.dataval})
			}
			axios.post(base_url + this.url,param).then(res => {
				if(res.data.status == 200){
					this.list = res.data.data.data
					this.page_data.total = res.data.data.total
				}
			})
		},
		remoteSearch(val){
			this.queryString = val
			let param = {queryString:this.queryString}
			axios.post(base_url + this.url,param).then(res => {
				if(res.data.status == 200){
					this.list = res.data.data.data
					this.page_data.total = res.data.data.total
				}
			})
		},
		handleClear(){
			this.page_data.page = 1
			this.dataval = ''
			this.queryString = ''
			this.init()
		},
		selectchange(val){
			this.$emit('update:selectval',val)
		}
	}
});


//时间范围
Vue.component('timepiker', {
	template: `
		<el-date-picker value-format="yyyy-MM-dd HH:mm:ss" :picker-options="pickerOptions" @change="change" type="datetimerange" v-model="optiondata" clearable range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
	`
	,
	props: {
		is_clear:{
			type:Boolean,
			default:false
		},
	},
	watch:{
		is_clear(val){
			if(val){
				this.optiondata = []
			}
		}
	},
	data() {
		return {
			optiondata:[],
			pickerOptions: {
				shortcuts: [
					{
						text: '今日',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							start.setHours(0, 0, 0, 0);
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '昨日',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							start.setDate(start.getDate() - 1);
							start.setHours(0, 0, 0, 0);
							end.setDate(end.getDate() - 1);
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '本周',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							start.setDate(start.getDate() - start.getDay());
							start.setHours(0, 0, 0, 0);
							end.setDate(end.getDate() + (6 - end.getDay()));
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '上周',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							start.setDate(start.getDate() - start.getDay() - 6);
							start.setHours(0, 0, 0, 0);
							end.setDate(end.getDate() - end.getDay());
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '本月',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							start.setDate(1);
							start.setHours(0, 0, 0, 0);
							end.setMonth(end.getMonth() + 1);
							end.setDate(0);
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '上月',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							start.setMonth(start.getMonth() - 1);
							start.setDate(1);
							start.setHours(0, 0, 0, 0);
							end.setDate(0);
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '本季度',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							const currentMonth = end.getMonth();
							const quarterStartMonth = Math.floor(currentMonth / 3) * 3;
							start.setMonth(quarterStartMonth);
							start.setDate(1);
							start.setHours(0, 0, 0, 0);
							end.setMonth(quarterStartMonth + 3);
							end.setDate(0);
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '上季度',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							const currentMonth = end.getMonth();
							const quarterStartMonth = Math.floor(currentMonth / 3) * 3;
							const prevQuarterStartMonth = quarterStartMonth - 3;
							start.setMonth(prevQuarterStartMonth);
							start.setDate(1);
							start.setHours(0, 0, 0, 0);
							end.setMonth(quarterStartMonth);
							end.setDate(0);
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '本年度',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							start.setMonth(0);
							start.setDate(1);
							start.setHours(0, 0, 0, 0);
							end.setMonth(11);
							end.setDate(31);
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: '上一年',
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							start.setFullYear(start.getFullYear() - 1);
							start.setMonth(0);
							start.setDate(1);
							start.setHours(0, 0, 0, 0);
							end.setFullYear(end.getFullYear() - 1);
							end.setMonth(11);
							end.setDate(31);
							end.setHours(23, 59, 59, 999);
							picker.$emit('pick', [start, end]);
						}
					}
				]
			},
		}
	},
	methods: {
		change(val){
			this.$emit('update:is_clear', false)
			this.$emit('update:option',val)
		}
	}
});


//商品规格
Vue.component('guige', {
	template: `
		<div class="jzdItem">
			<div>
			<table class="guigetable" cellpadding="0" cellspacing="0" align="center" width="100%" style="word-break:break-all;font-size:13px;">
				<tr>
					<td align="center" v-for="(item,i) in configList">{{item.title}}</td>
					<td align="center">操作</td>
				</tr>
				<tr v-for="(vo,j) in list">
					<td align="center" v-for="(item,i) in configList">
						<el-form-item style="margin-bottom:0" v-if="item.type == 1" :prop="item.field">
							<el-input  v-model="list[j][item.field]" autoComplete="off" clearable  :placeholder="item.title"></el-input>
						</el-form-item>
						<el-form-item style="margin-bottom:0" v-if="item.type == 2" :prop="item.field">
							<el-select style="margin-bottom:0" v-model="list[j][item.field]" filterable clearable :placeholder="item.title">
								<el-option v-for="(vo,index) in item.item.split(',')" :key="index" :label="vo" :value="vo"></el-option>
							</el-select>
						</el-form-item>
						<el-form-item style="margin-bottom:0" v-if="item.type == 6" :prop="item.field">
							<el-switch :active-value="1" :inactive-value="0" v-model="list[j][item.field]"></el-switch>
						</el-form-item>
						<el-form-item style="margin-bottom:0" v-if="item.type == 9" :prop="item.field">
							<el-date-picker  style="margin-bottom:0" type="date" v-model="list[j][item.field]" autoComplete="off" clearable  :placeholder="item.title"></el-date-picker>
						</el-form-item>
						<el-form-item style="margin-bottom:0" v-if="item.type == 13" :prop="item.field">
							<Upload file_type="image" scene="guige" :image.sync="list[j][item.field]"></Upload>
						</el-form-item>
						<el-form-item style="margin-bottom:0" v-if="item.type == 17" :prop="item.field">
							<el-input-number controls-position="right" autoComplete="off" v-model="list[j][item.field]" clearable :min="0" :placeholder="item.title"/>
						</el-form-item>
						<el-form-item style="margin-bottom:0" v-if="item.type == 18" :prop="item.field">
							<Tag :tag_list.sync="list[j][item.field]"></Tag>
						</el-form-item>
					</td>
					<td align="center">
						<el-button type="danger" size="mini" icon="el-icon-close" @click="deleteItem(j)"></el-button>
					</td>
				</tr>
				
			</table>
			<div>
				<el-button type="info" icon="el-icon-plus" style="padding:5px 7px" size="mini" @click="addItem">追加</el-button>
				<el-button v-if="list.length > 0" type="warning" icon="el-icon-delete" style="padding:5px 7px" size="mini" @click="clearItem">清空</el-button>
			</div>
			</div>
		</div>
	`
	,
	props: {
		listitem:{
			type: Array,
		},
		config:{
			type: String,
		},
	},
	watch:{
		list(){
			this.$emit('update:listitem',this.list)
		},
	},
	data(){
		return{
			configList:[],
			list:this.listitem,
		}
	},
	mounted() {
		this.configList = JSON.parse(this.config);
	},
	methods: {
		addItem(){
			this.list.push({})
		},
		deleteItem(index){
			this.list.splice(index,1)
		},
		clearItem(){
			this.list = []
		},
	}
});



Vue.component('horizontal-select', {
	template: `
		<div style="width:100%;">
			<el-popover
				v-model="popoverVisible"
				placement="bottom-start"
				trigger="click"
				width="760"
				:disabled="disabled">
				<div style="max-height:420px;padding: 10px 14px;overflow-y:auto;background:#fff;box-sizing:border-box;">
					<div v-if="normalizedGroups.length === 0" style="padding:18px 10px;color:#909399;font-size:13px;text-align:center;">{{emptyText}}</div>
					<el-radio-group v-else :value="currentValue" @input="handleSelect" style="display:block;width:100%;">
						<div v-for="group in normalizedGroups" :key="group.key" style="margin-bottom:12px;border:1px solid #e7edf5;border-radius:10px;background:#fff;overflow:hidden;box-sizing:border-box;">
							<div @click="toggleGroupOpen(group.key)" style="display:flex;align-items:center;justify-content:space-between;min-height:35px;padding:0 14px;background:#f8fbff;cursor:pointer;box-sizing:border-box;">
								<span style="min-width:0;font-size:14px;font-weight:600;color:#303133;line-height:1.4;">{{group.label}}</span>
								<div style="display:flex;align-items:center;gap:10px;flex:0 0 auto;padding-left:12px;">
									<span style="font-size:12px;color:#909399;">{{group.leaves.length}}项</span>
									<i class="el-icon-arrow-down" :style="{fontSize:'12px',color:'#909399',transition:'transform .2s ease',transform:isGroupOpen(group.key) ? 'rotate(0deg)' : 'rotate(-90deg)'}"></i>
								</div>
							</div>
								<div v-show="isGroupOpen(group.key)" style="padding:14px;border-top:1px solid #edf1f7;box-sizing:border-box;">
									<div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:flex-start;gap:10px 12px;">
										<div v-for="leaf in group.leaves" :key="leaf.key" style="min-width:0;max-width:100%;box-sizing:border-box;">
											<el-radio :label="leaf.id" style="display:inline-block;max-width:100%;margin-right:0;line-height:1.4;white-space:normal;vertical-align:middle;word-break:break-all;">{{leaf.label}}</el-radio>
										</div>
									</div>
								</div>
						</div>
					</el-radio-group>
				</div>
				<div slot="reference" style="position:relative;display:flex;align-items:center;justify-content:space-between;width:100%;min-height:32px;padding:5px 34px 5px 11px;border:1px solid #dcdfe6;border-radius:4px;background:#fff;color:#606266;font-size:13px;line-height:20px;box-sizing:border-box;">
					<span style="min-width:0;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{displayText || placeholder}}</span>
					<span style="position:absolute;top:50%;right:10px;display:flex;align-items:center;transform:translateY(-50%);color:#c0c4cc;">
						<i
							v-if="clearable && currentValue !== '' && currentValue !== null && currentValue !== undefined && !disabled"
							class="el-icon-circle-close"
							style="cursor:pointer;margin-right:6px;"
							@click.stop="clearSingle"></i>
						<i class="el-icon-arrow-down" style="font-size:12px;"></i>
					</span>
				</div>
			</el-popover>
		</div>
	`,
	props: {
		value: {
			type: [String, Number, Array],
			default: undefined
		},
		selectval: {
			type: [String, Number, Array],
			default: undefined
		},
		options: {
			type: Array,
			default: function () {
				return [];
			}
		},
		placeholder: {
			type: String,
			default: '请选择'
		},
		disabled: {
			type: Boolean,
			default: false
		},
		clearable: {
			type: Boolean,
			default: true
		},
		emptyText: {
			type: String,
			default: '暂无可选项'
		}
	},
	data() {
		return {
			popoverVisible: false,
			openKeys: []
		}
	},
	watch: {
		options: {
			handler() {
				this.syncOpenKeys()
			},
			deep: true,
			immediate: true
		}
	},
	computed: {
		normalizedGroups() {
			return this.buildGroups(this.options)
		},
		leafMap() {
			return this.buildLeafMap(this.normalizedGroups)
		},
		currentValue() {
			let value = this.value !== undefined ? this.value : this.selectval
			return value === undefined ? '' : value
		},
		displayText() {
			let leaf = this.findLeafById(this.currentValue)
			return leaf ? leaf.label : ''
		}
	},
	methods: {
		syncOpenKeys() {
			let groupKeys = this.normalizedGroups.map(function (group) {
				return group.key
			})
			if (groupKeys.length === 0) {
				this.openKeys = []
				return
			}
			let nextOpenKeys = this.openKeys.filter(function (key) {
				return groupKeys.indexOf(key) > -1
			})
			if (nextOpenKeys.length === 0) {
				nextOpenKeys = [groupKeys[0]]
			}
			this.openKeys = nextOpenKeys
		},
		getNodeKey(value) {
			if (value === null || value === undefined) {
				return ''
			}
			return String(value)
		},
		getOptionValue(option, path) {
			if (option === null || option === undefined) {
				return path
			}
			if (typeof option !== 'object') {
				return option
			}
			if (Object.prototype.hasOwnProperty.call(option, 'id')) {
				return option.id
			}
			if (Object.prototype.hasOwnProperty.call(option, 'val')) {
				return option.val
			}
			if (Object.prototype.hasOwnProperty.call(option, 'value')) {
				return option.value
			}
			if (Object.prototype.hasOwnProperty.call(option, 'field')) {
				return option.field
			}
			return path
		},
		getOptionLabel(option, fallback) {
			if (option === null || option === undefined) {
				return fallback
			}
			if (typeof option !== 'object') {
				return option
			}
			if (option.label !== undefined && option.label !== null && option.label !== '') {
				return option.label
			}
			if (option.key !== undefined && option.key !== null && option.key !== '') {
				return option.key
			}
			if (option.title !== undefined && option.title !== null && option.title !== '') {
				return option.title
			}
			if (option.name !== undefined && option.name !== null && option.name !== '') {
				return option.name
			}
			return fallback
		},
		normalizeNodes(options, pathPrefix) {
			let vm = this
			if (!Array.isArray(options)) {
				return []
			}
			return options.map(function (option, index) {
				let node = option && typeof option === 'object' ? option : option
				let path = (pathPrefix || 'root') + '-' + index
				let value = vm.getOptionValue(node, path)
				let label = vm.getOptionLabel(node, value)
				let childrenSource = []
				if (node && typeof node === 'object') {
					if (Array.isArray(node.children)) {
						childrenSource = node.children
					} else if (Array.isArray(node.items)) {
						childrenSource = node.items
					} else if (Array.isArray(node.options)) {
						childrenSource = node.options
					}
				}
				return {
					id: value,
					key: vm.getNodeKey(value),
					label: label,
					children: vm.normalizeNodes(childrenSource, path)
				}
			})
		},
		collectLeaves(node) {
			let vm = this
			if (!node) {
				return []
			}
			if (!Array.isArray(node.children) || node.children.length === 0) {
				return [node]
			}
			let leaves = []
			node.children.forEach(function (child) {
				leaves = leaves.concat(vm.collectLeaves(child))
			})
			return leaves
		},
		buildGroups(options) {
			let vm = this
			return this.normalizeNodes(options).map(function (group) {
				let leaves = vm.collectLeaves(group)
				let leafKeys = []
				leaves.forEach(function (item) {
					leafKeys.push(item.key)
				})
				group.leaves = leaves
				group.leafKeys = leafKeys
				return group
			})
		},
		buildLeafMap(groups) {
			let map = {}
			groups.forEach(function (group) {
				group.leaves.forEach(function (leaf) {
					map[leaf.key] = leaf
				})
			})
			return map
		},
		findLeafById(id) {
			return this.leafMap[this.getNodeKey(id)] || null
		},
		isGroupOpen(groupKey) {
			return this.openKeys.indexOf(groupKey) > -1
		},
		toggleGroupOpen(groupKey) {
			let nextOpenKeys = this.openKeys.slice()
			let index = nextOpenKeys.indexOf(groupKey)
			if (index > -1) {
				nextOpenKeys.splice(index, 1)
			} else {
				nextOpenKeys.push(groupKey)
			}
			this.openKeys = nextOpenKeys
		},
		clearSingle() {
			if (this.disabled) {
				return
			}
			this.$emit('input', '')
			this.$emit('update:value', '')
			this.$emit('update:selectval', '')
			this.$emit('change', '', null)
		},
		handleSelect(value) {
			let leaf = this.findLeafById(value)
			this.$emit('input', value)
			this.$emit('update:value', value)
			this.$emit('update:selectval', value)
			this.$emit('change', value, leaf)
			this.popoverVisible = false
		},
	}
});

Vue.component('horizontal-multi-select', {
	template: `
		<div style="width:100%;">
			<el-popover
				v-model="popoverVisible"
				placement="bottom-start"
				trigger="click"
				width="760"
				:disabled="disabled">
				<div style="max-height:420px;padding: 10px 14px;overflow-y:auto;background:#fff;box-sizing:border-box;">
					<div v-if="normalizedGroups.length === 0" style="padding:18px 10px;color:#909399;font-size:13px;text-align:center;">{{emptyText}}</div>
					<div v-else>
						<div v-for="group in normalizedGroups" :key="group.key" style="margin-bottom:12px;border:1px solid #e7edf5;border-radius:10px;background:#fff;overflow:hidden;box-sizing:border-box;">
							<div @click="toggleGroupOpen(group.key)" style="display:flex;align-items:center;justify-content:space-between;min-height:35px;padding:0 14px;background:#f8fbff;cursor:pointer;box-sizing:border-box;">
								<div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1;">
									<span style="display:flex;align-items:center;flex:0 0 auto;" @click.stop>
										<el-checkbox
											:value="isGroupChecked(group)"
											:indeterminate="isGroupIndeterminate(group)"
											style="margin-right:0;line-height:1.4;"
											@change="toggleGroup(group, $event)">
										</el-checkbox>
									</span>
									<span style="min-width:0;font-size:14px;font-weight:600;color:#303133;line-height:1.4;">{{group.label}}</span>
								</div>
								<div style="display:flex;align-items:center;gap:10px;flex:0 0 auto;padding-left:12px;">
									<span style="font-size:12px;color:#909399;">{{groupSelectedCount(group)}}/{{group.leaves.length}}</span>
									<i class="el-icon-arrow-down" :style="{fontSize:'12px',color:'#909399',transition:'transform .2s ease',transform:isGroupOpen(group.key) ? 'rotate(0deg)' : 'rotate(-90deg)'}"></i>
								</div>
							</div>
								<div v-show="isGroupOpen(group.key)" style="padding:14px;border-top:1px solid #edf1f7;box-sizing:border-box;">
									<div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:flex-start;gap:10px 12px;">
										<div v-for="leaf in group.leaves" :key="leaf.key" style="min-width:0;max-width:100%;box-sizing:border-box;">
											<el-checkbox
												:value="isLeafSelected(leaf.id)"
												style="display:inline-block;max-width:100%;margin-right:0;line-height:1.4;white-space:normal;vertical-align:middle;word-break:break-all;"
												@change="toggleLeaf(leaf, $event)">
												{{leaf.label}}
											</el-checkbox>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div slot="reference" style="position:relative;display:flex;align-items:center;justify-content:space-between;width:100%;min-height:32px;padding:5px 34px 5px 11px;border:1px solid #dcdfe6;border-radius:4px;background:#fff;color:#606266;font-size:13px;line-height:20px;box-sizing:border-box;">
					<span style="min-width:0;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{displayText || placeholder}}</span>
					<span style="position:absolute;top:50%;right:10px;display:flex;align-items:center;transform:translateY(-50%);color:#c0c4cc;">
						<i
							v-if="clearable && selectedValues.length > 0 && !disabled"
							class="el-icon-circle-close"
							style="cursor:pointer;margin-right:6px;"
							@click.stop="clearMultiple"></i>
						<i class="el-icon-arrow-down" style="font-size:12px;"></i>
					</span>
				</div>
			</el-popover>
		</div>
	`,
	props: {
		value: {
			type: [String, Number, Array],
			default: undefined
		},
		selectval: {
			type: [String, Number, Array],
			default: undefined
		},
		options: {
			type: Array,
			default: function () {
				return [];
			}
		},
		placeholder: {
			type: String,
			default: '请选择'
		},
		disabled: {
			type: Boolean,
			default: false
		},
		clearable: {
			type: Boolean,
			default: true
		},
		emptyText: {
			type: String,
			default: '暂无可选项'
		}
	},
	data() {
		return {
			popoverVisible: false,
			openKeys: []
		}
	},
	watch: {
		options: {
			handler() {
				this.syncOpenKeys()
			},
			deep: true,
			immediate: true
		}
	},
	computed: {
		normalizedGroups() {
			return this.buildGroups(this.options)
		},
		leafMap() {
			return this.buildLeafMap(this.normalizedGroups)
		},
		flatLeaves() {
			let list = []
			this.normalizedGroups.forEach(function (group) {
				list = list.concat(group.leaves)
			})
			return list
		},
		selectedValues() {
			let value = this.value !== undefined ? this.value : this.selectval
			if (Array.isArray(value)) {
				return value.slice()
			}
			if (value === '' || value === null || value === undefined) {
				return []
			}
			return [value]
		},
		selectedLabels() {
			let vm = this
			return this.selectedValues.map(function (item) {
				let leaf = vm.findLeafById(item)
				return leaf ? leaf.label : ''
			}).filter(Boolean)
		},
		displayText() {
			if (this.selectedLabels.length === 0) {
				return ''
			}
			if (this.selectedLabels.length <= 2) {
				return this.selectedLabels.join('、')
			}
			return this.selectedLabels.slice(0, 2).join('、') + ' 等' + this.selectedLabels.length + '项'
		}
	},
	methods: {
		syncOpenKeys() {
			let groupKeys = this.normalizedGroups.map(function (group) {
				return group.key
			})
			if (groupKeys.length === 0) {
				this.openKeys = []
				return
			}
			let nextOpenKeys = this.openKeys.filter(function (key) {
				return groupKeys.indexOf(key) > -1
			})
			if (nextOpenKeys.length === 0) {
				nextOpenKeys = [groupKeys[0]]
			}
			this.openKeys = nextOpenKeys
		},
		getNodeKey(value) {
			if (value === null || value === undefined) {
				return ''
			}
			return String(value)
		},
		getOptionValue(option, path) {
			if (option === null || option === undefined) {
				return path
			}
			if (typeof option !== 'object') {
				return option
			}
			if (Object.prototype.hasOwnProperty.call(option, 'id')) {
				return option.id
			}
			if (Object.prototype.hasOwnProperty.call(option, 'val')) {
				return option.val
			}
			if (Object.prototype.hasOwnProperty.call(option, 'value')) {
				return option.value
			}
			if (Object.prototype.hasOwnProperty.call(option, 'field')) {
				return option.field
			}
			return path
		},
		getOptionLabel(option, fallback) {
			if (option === null || option === undefined) {
				return fallback
			}
			if (typeof option !== 'object') {
				return option
			}
			if (option.label !== undefined && option.label !== null && option.label !== '') {
				return option.label
			}
			if (option.key !== undefined && option.key !== null && option.key !== '') {
				return option.key
			}
			if (option.title !== undefined && option.title !== null && option.title !== '') {
				return option.title
			}
			if (option.name !== undefined && option.name !== null && option.name !== '') {
				return option.name
			}
			return fallback
		},
		normalizeNodes(options, pathPrefix) {
			let vm = this
			if (!Array.isArray(options)) {
				return []
			}
			return options.map(function (option, index) {
				let node = option && typeof option === 'object' ? option : option
				let path = (pathPrefix || 'root') + '-' + index
				let value = vm.getOptionValue(node, path)
				let label = vm.getOptionLabel(node, value)
				let childrenSource = []
				if (node && typeof node === 'object') {
					if (Array.isArray(node.children)) {
						childrenSource = node.children
					} else if (Array.isArray(node.items)) {
						childrenSource = node.items
					} else if (Array.isArray(node.options)) {
						childrenSource = node.options
					}
				}
				return {
					id: value,
					key: vm.getNodeKey(value),
					label: label,
					children: vm.normalizeNodes(childrenSource, path)
				}
			})
		},
		collectLeaves(node) {
			let vm = this
			if (!node) {
				return []
			}
			if (!Array.isArray(node.children) || node.children.length === 0) {
				return [node]
			}
			let leaves = []
			node.children.forEach(function (child) {
				leaves = leaves.concat(vm.collectLeaves(child))
			})
			return leaves
		},
		buildGroups(options) {
			let vm = this
			return this.normalizeNodes(options).map(function (group) {
				let leaves = vm.collectLeaves(group)
				let leafKeys = []
				leaves.forEach(function (item) {
					leafKeys.push(item.key)
				})
				group.leaves = leaves
				group.leafKeys = leafKeys
				return group
			})
		},
		buildLeafMap(groups) {
			let map = {}
			groups.forEach(function (group) {
				group.leaves.forEach(function (leaf) {
					map[leaf.key] = leaf
				})
			})
			return map
		},
		findLeafById(id) {
			return this.leafMap[this.getNodeKey(id)] || null
		},
		hasLeafKey(key) {
			for (let i = 0; i < this.selectedValues.length; i++) {
				if (this.getNodeKey(this.selectedValues[i]) === key) {
					return true
				}
			}
			return false
		},
		getSelectedKeys() {
			let keyList = []
			this.selectedValues.forEach(function (item) {
				let key = String(item)
				if (item === null || item === undefined) {
					key = ''
				}
				if (keyList.indexOf(key) === -1) {
					keyList.push(key)
				}
			})
			return keyList
		},
		buildValuesFromKeys(keyList) {
			let valueList = []
			this.flatLeaves.forEach(function (leaf) {
				if (keyList.indexOf(leaf.key) > -1) {
					valueList.push(leaf.id)
				}
			})
			return valueList
		},
		emitMultiValue(valueList) {
			let vm = this
			let emitList = Array.isArray(valueList) ? valueList.slice() : []
			let selectedList = emitList.map(function (item) {
				return vm.findLeafById(item)
			}).filter(Boolean)
			this.$emit('input', emitList)
			this.$emit('update:value', emitList)
			this.$emit('update:selectval', emitList)
			this.$emit('change', emitList, selectedList)
		},
		clearMultiple() {
			if (this.disabled) {
				return
			}
			this.emitMultiValue([])
		},
		isLeafSelected(leafId) {
			return this.hasLeafKey(this.getNodeKey(leafId))
		},
		groupSelectedCount(group) {
			let count = 0
			group.leafKeys.forEach((key) => {
				if (this.hasLeafKey(key)) {
					count++
				}
			})
			return count
		},
		isGroupChecked(group) {
			return group.leafKeys.length > 0 && this.groupSelectedCount(group) === group.leafKeys.length
		},
		isGroupIndeterminate(group) {
			let selectedCount = this.groupSelectedCount(group)
			return selectedCount > 0 && selectedCount < group.leafKeys.length
		},
		isGroupOpen(groupKey) {
			return this.openKeys.indexOf(groupKey) > -1
		},
		toggleGroupOpen(groupKey) {
			let nextOpenKeys = this.openKeys.slice()
			let index = nextOpenKeys.indexOf(groupKey)
			if (index > -1) {
				nextOpenKeys.splice(index, 1)
			} else {
				nextOpenKeys.push(groupKey)
			}
			this.openKeys = nextOpenKeys
		},
		toggleLeaf(leaf, checked) {
			let nextKeys = this.getSelectedKeys()
			let leafIndex = nextKeys.indexOf(leaf.key)
			if (checked && leafIndex === -1) {
				nextKeys.push(leaf.key)
			}
			if (!checked && leafIndex > -1) {
				nextKeys.splice(leafIndex, 1)
			}
			this.emitMultiValue(this.buildValuesFromKeys(nextKeys))
		},
		toggleGroup(group, checked) {
			let nextKeys = this.getSelectedKeys()
			group.leafKeys.forEach(function (key) {
				let index = nextKeys.indexOf(key)
				if (checked && index === -1) {
					nextKeys.push(key)
				}
				if (!checked && index > -1) {
					nextKeys.splice(index, 1)
				}
			})
			this.emitMultiValue(this.buildValuesFromKeys(nextKeys))
		},
	}
});
