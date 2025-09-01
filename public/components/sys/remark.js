// 添加CSS样式
const remarkStyle = document.createElement('style');
remarkStyle.textContent = `
/* 全屏对话框样式 */
/* 强制全屏对话框样式 - 最高优先级 */
.el-dialog.is-fullscreen {
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    margin: 0 !important;
    top: 0 !important;
    left: 0 !important;
    border-radius: 0 !important;
}

/* 对话框内容区域 - 使用更具体的选择器 */
.el-dialog.is-fullscreen > .el-dialog__body {
    height: calc(100vh - 54px) !important;  /* 减去标题栏高度 */
    padding: 0 !important;
    margin-top: 54px !important;  /* 标题栏高度 */
    overflow: auto;
    background: #1e1e1e;
    display: flex;
    flex-direction: column;
}

.fullscreen-remark-dialog {
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    top: 0 !important;
    left: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden;
}
.fullscreen-remark-dialog .el-dialog {
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
    max-width: none !important;
    border-radius: 0 !important;
    background: #1e1e1e;
}

.fullscreen-remark-dialog .el-dialog__header {
    padding: 15px 20px;
    border-bottom: 1px solid #333;
    background: #252526;
    position: fixed;
    width: 100%;
    z-index: 10;
}

.fullscreen-remark-dialog .el-dialog__headerbtn {
    top: 15px;
    right: 20px;
    font-size: 20px;
}

.fullscreen-remark-dialog .el-dialog__title {
    color: #e0e0e0;
    font-size: 16px;
}

.fullscreen-remark-dialog .el-dialog__body {
    padding: 0 !important;
    height: 100vh !important;
    overflow: hidden;
    background: #1e1e1e;
    padding-top: 54px; /* 标题栏高度 */
}

.fullscreen-dialog-container {
    display: flex;
    height: 100%;
}

.left-margin {
    width: 255px;
    background: #252526;
    height: 100%;
    border-right: 1px solid #1a1a1a;
}

.content-wrapper {
    flex: 1;
    height: 100%;
    overflow: auto;
    padding: 15px;
}

/* 宝塔风格编辑器样式 */
.editor-container {
    display: flex;
    height: calc(100% - 90px); /* 减去底部按钮高度 */
}

.version-list {
    width: 220px;
    padding-right: 15px;
    border-right: 1px solid #333;
    overflow: hidden;
    background: #252526;
    border-radius: 4px;
    margin-right: 15px;
}

.version-list .section-title {
    color: #e0e0e0;
    font-size: 14px;
    padding: 28px 30px;
    border-bottom: 1px solid #333;
    background: #2d2d2d;
    margin: -15px -15px 10px -15px;
}

.content-area {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.code-editor {
    flex: 1;
    margin-bottom: 15px;
    position: relative;
    border-radius: 4px;
    overflow: hidden;
    border: 1px solid #333;
    background: #1e1e1e;
}

.code-editor .editor-header {
    padding: 10px 10px 10px 10px;
    background: #2d2d2d;
    border-bottom: 1px solid #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.description-area {
    height: 180px;
    background: #252526;
    border-radius: 4px;
    overflow: hidden;
    border: 1px solid #333;
}

.description-area .section-title {
    color: #e0e0e0;
    font-size: 14px;
    padding: 10px 15px;
    border-bottom: 1px solid #333;
    background: #2d2d2d;
}

/* 宝塔风格代码编辑器 */
.CodeMirror {
    height: 89% !important;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.5;
    background: #1e1e1e;
    color: #d4d4d4;
}

.CodeMirror-gutters {
    background: #252526 !important;
    border-right: 1px solid #333 !important;
}

.CodeMirror-linenumber {
    color: #858585 !important;
    padding: 0 5px !important;
}

.CodeMirror-selected {
    background: #264f78 !important;
}

/* PHP语法高亮 - 宝塔风格 */
.cm-s-bt-default .cm-comment { color: #6a9955 !important; } /* 注释 */
.cm-s-bt-default .cm-variable { color: #9cdcfe !important; } /* 变量 */
.cm-s-bt-default .cm-string { color: #ce9178 !important; } /* 字符串 */
.cm-s-bt-default .cm-number { color: #b5cea8 !important; } /* 数字 */
.cm-s-bt-default .cm-keyword { color: #569cd6 !important; } /* 关键字 */
.cm-s-bt-default .cm-def { color: #dcdcaa !important; } /* 定义 */
.cm-s-bt-default .cm-operator { color: #d4d4d4 !important; } /* 操作符 */
.cm-s-bt-default .cm-builtin { color: #4ec9b0 !important; } /* 内置函数 */
.cm-s-bt-default .cm-tag { color: #569cd6 !important; } /* HTML标签 */
.cm-s-bt-default .cm-attribute { color: #9cdcfe !important; } /* HTML属性 */
.cm-s-bt-default .cm-property { color: #9cdcfe !important; } /* 对象属性 */

/* 时间线样式 - 宝塔风格 */
.el-timeline {
    padding-left: 10px;
}

.el-timeline-item {
    cursor: pointer;
    padding: 8px 10px;
    margin-bottom: 5px;
    border-radius: 4px;
    transition: all 0.3s;
    color: #fff !important;
}

.el-timeline-item:hover {
    background: #333;
}

.el-timeline-item__timestamp {
    color: #999;
    font-size: 12px;
    margin-bottom: 5px;
}

.el-timeline-item__content {
    color: #7f7f7f;
}

/* 底部按钮 */
.dialog-footer {
    padding: 15px 0;
    text-align: right;
    border-top: 0px solid #333;
    margin-top: 15px;
}

/* 响应式调整 */
@media (max-width: 1200px) {
    .version-list {
        width: 180px;
    }
}

@media (max-width: 768px) {
    .left-margin {
        width: 180px;
    }

    .version-list {
        width: 150px;
    }

    .editor-container {
        flex-direction: column;
    }

    .version-list {
        width: 100%;
        margin-right: 0;
        margin-bottom: 15px;
        border-right: none;
        border-bottom: 1px solid #333;
    }
}

/* 修改光标颜色 */
.CodeMirror-cursor {
    border-left: 1px solid #ffffff !important;
}

/* 修改功能描述文本域为暗色调 */
.description-area .el-textarea__inner {
    background-color: #252526;
    color: #d4d4d4;
    border: 1px solid #333;
    height: 100%;
}

/* 修改文本域placeholder颜色 */
.description-area .el-textarea__inner::placeholder {
    color: #666;
}

/* 修改文本域聚焦时的边框颜色 */
.description-area .el-textarea__inner:focus {
    border-color: #409eff;
}

/* 文本域滚动条样式 */
.description-area .el-textarea__inner::-webkit-scrollbar {
    width: 6px;
}

.description-area .el-textarea__inner::-webkit-scrollbar-thumb {
    background: #4a4a4a;
    border-radius: 3px;
}

.description-area .el-textarea__inner::-webkit-scrollbar-track {
    background: #2d2d2d;
}

.CodeMirror-scroll {
    margin-bottom:-120px !important;
}

/* 宝塔编辑器主题 - 统一使用.cm-s-bt-default前缀 */
.cm-s-bt-default .cm-comment { color: #6A9955 !important; font-style: italic !important; }
.cm-s-bt-default .cm-keyword { color: #569CD6 !important; font-weight: bold !important; }
.cm-s-bt-default .cm-string { color: #CE9178 !important; }
.cm-s-bt-default .cm-number { color: #B5CEA8 !important; }
.cm-s-bt-default .cm-variable { color: #9CDCFE !important; }
.cm-s-bt-default .cm-variable-2 { color: #4FC1FF !important; }
.cm-s-bt-default .cm-def { color: #DCDCAA !important; }
.cm-s-bt-default .cm-operator { color: #D4D4D4 !important; }
.cm-s-bt-default .cm-property { color: #9CDCFE !important; }
.cm-s-bt-default .cm-builtin { color: #4EC9B0 !important; }
.cm-s-bt-default .cm-tag { color: #569CD6 !important; }
.cm-s-bt-default .cm-attribute { color: #9CDCFE !important; }
.cm-s-bt-default .cm-meta { color: #9CDCFE !important; }

/* PHP特定语法 */
.cm-s-bt-default .cm-variable-php { color: #9CDCFE !important; }
.cm-s-bt-default .cm-string-php { color: #CE9178 !important; }
.cm-s-bt-default .cm-comment-php { color: #6A9955 !important; font-style: italic !important; }
.cm-s-bt-default .cm-keyword-php { color: #569CD6 !important; font-weight: bold !important; }

/* 确保编辑器容器样式 */
.CodeMirror {
    font-family: 'Consolas', 'Courier New', monospace !important;
    font-size: 13px !important;
    line-height: 1.5 !important;
    background: #1e1e1e !important;
    color: #d4d4d4 !important;
}
`;
document.head.appendChild(remarkStyle);

Vue.component('Remark', {
    template: `
        <el-dialog
            title="编辑备注"
            :visible.sync="internalShow"
            fullscreen
            custom-class="fullscreen-remark-dialog"
            :modal="false"
            :close-on-click-modal="false"
            :show-close="true"
            :append-to-body="true"
            @close="closeDialog">
            
            <div class="remark-dialog-container">
                <div class="editor-container">
                    <!-- 左侧版本记录 -->
                    <div class="version-list">
                        <div class="section-title">版本记录</div>
                        <el-scrollbar style="height:calc(100vh - 200px)">
                            <el-timeline>
                                <el-timeline-item
                                    v-for="(version, index) in versions"
                                    :key="index"
                                    :timestamp="formatTime(version.create_time)"
                                    @click.native="loadVersionContent(version)">
                                    {{ version.version_desc || '无描述' }}
                                </el-timeline-item>
                            </el-timeline>
                        </el-scrollbar>
                    </div>

                    <!-- 右侧内容区 -->
                    <div class="content-area">
                        <!-- 代码编辑器 -->
                        <div class="code-editor">
                            <div class="editor-header">
                                <el-select
                                    v-model="language"
                                    @change="changeEditorLanguage"
                                    size="small">
                                    <el-option value="php" label="PHP"></el-option>
                                    <el-option value="text" label="纯文本"></el-option>
                                    <el-option value="javascript" label="JavaScript"></el-option>
                                    <el-option value="html" label="HTML"></el-option>
                                    <el-option value="x-vue" label="Vue"></el-option>
                                </el-select>
                                <el-button type="text" icon="el-icon-finished">代码编辑器</el-button>
                            </div>
                            <textarea id="remark-editor"></textarea>
                        </div>

                        <!-- 功能描述 -->
                        <div class="description-area">
                            <div class="section-title">功能描述</div>
                            <el-input
                                type="textarea"
                                :rows="5"
                                resize="none"
                                v-model="description">
                            </el-input>
                        </div>
                    </div>
                </div>

                <div slot="footer" class="dialog-footer">
                    <el-button @click="closeDialog">取消</el-button>
                    <el-button type="primary" @click="saveRemark">保存</el-button>
                </div>
            </div>
        </el-dialog>
        `,
    props: {
        show: {
            type: Boolean,
            default: false
        },
        row: {
            type: Object,
            default: () => ({})
        }
    },
    data() {
        return {
            internalShow: this.show,
            versions: [],
            language: 'php',
            description: '',
            editor: null,
            base_url: window.base_url || ''
        };
    },
    watch: {
        show(newVal) {
            this.internalShow = newVal;
            if (newVal) {
                this.$nextTick(() => {
                    this.initEditor();
                    this.loadVersions();
                });
            }
        },
        internalShow(newVal) {
            this.$emit('update:show', newVal);
        }
    },
    methods: {
        initEditor() {
            if (this.editor) {
                this.editor.setValue(this.row.remark || '');
                return;
            }

            this.editor = CodeMirror.fromTextArea(
                document.getElementById('remark-editor'),
                {
                    mode: 'application/x-httpd-php',
                    theme: 'bt-default', // 使用宝塔主题
                    lineNumbers: true,
                    lineWrapping: true,
                    indentUnit: 4,
                    tabSize: 4,
                    matchBrackets: true,
                    autoCloseBrackets: true
                }
            );

            this.editor.setValue(this.row.remark || '');
            this.description = this.row.remark_desc || '';
        },

        changeEditorLanguage(lang) {
            this.language = lang;
            if (!this.editor) return;

            let mode;
            switch(lang) {
                case 'text':
                    mode = 'text';
                    break;
                case 'html':
                    mode = 'htmlmixed';
                    break;
                case 'x-vue':
                    mode = {
                        name: 'htmlmixed',
                        vue: true
                    };
                    break;
                case 'javascript':
                    mode = 'javascript';
                    break;
                case 'php':
                default:
                    mode = 'application/x-httpd-php';
            }
            this.editor.setOption('mode', mode);
        },

        formatTime(timestamp) {
            if (!timestamp) return '未知时间';
            if (/^\d+$/.test(timestamp)) {
                return new Date(parseInt(timestamp) * 1000).toLocaleString();
            }
            return timestamp;
        },

        loadVersions() {
            if (!this.row.id) return;

            axios.post(this.base_url + '/admin/Sys.Base/getRemarkVersions', {
                actionId: this.row.id,
                menu_id: this.row.menu_id
            }).then(res => {
                this.versions = res.data.data || [];
            }).catch(error => {
                this.$message.error('加载版本记录失败');
            });
        },

        loadVersionContent(version) {
            if (this.editor) {
                this.editor.setValue(version.content);
                this.description = version.description;
            }
        },

        saveRemark() {
            const content = this.editor.getValue();
            axios.post(this.base_url + '/admin/Sys.Base/updateActionExt', {
                id: this.row.id,
                remark: content,
                remark_desc: this.description,
                menu_id: this.row.menu_id
            }).then(res => {
                if (res.data.status === 200) {
                    this.$message.success('备注保存成功');
                    this.$emit('saved');
                    this.closeDialog();
                } else {
                    this.$message.error(res.data.msg);
                }
            }).catch(error => {
                this.$message.error('保存失败: ' + error.message);
            });
        },

        closeDialog() {
            this.internalShow = false;
            if (this.editor) {
                this.editor.toTextArea();
                this.editor = null;
            }
        }
    },

    beforeDestroy() {
        if (this.editor) {
            this.editor.toTextArea();
        }
    }
});
