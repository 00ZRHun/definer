<template>
    <div class="project">
        <div class="project_head">
            <div class="back">
                <span @click="$router.go(-1)">
                    <i class="el-icon-arrow-left"></i> {{lang.back}}
                </span>
            </div>
            <div class="title">{{info.title}}</div>
            <div class="share" @click="share">
                <i class="el-icon-share"></i>
            </div>
        </div>
        <div class="project_main" v-loading="loading">
            <div class="canvas">
                <myCanvas :url="info.img_url" :datas="marks" @add="addMarks"></myCanvas>
            </div>
            <div class="marks" v-if="marks.length > 0">
                <div class="mark_item" v-for="(item, index) in marks" :key="item.id">
                    <div class="mark_item_num">
                        <span v-if="item.pos_x == 0">{{item.num}}</span>
                        <span class="mark_canvas" v-if="item.pos_x > 0">{{item.num}}</span>
                    </div>
                    <div class="mark_item_main">
                        <!--
                        <p class="mark_item_title">{{item.title}}</p>
                        -->
                        <pre class="mark_item_title">{{item.content}}</pre>
                        <p class="mark_item_time">
                            <span>{{item.create_time}}</span>
                            <span>{{item.status}}</span>
                        </p>
                    </div>
                    <div class="mark_item_menu" v-if="user.id != 0">
                        <i class="designer- designer-shanchu-yichu-01" @click="del(item.id, index)"></i>
                        <i class="designer- designer-xiugai" @click="edit(item.id, index)"></i>
                    </div>
                </div>
            </div>
            <div class="marks nodata" v-if="marks.length == 0">
                {{lang.nodata}}
            </div>
            <div class="form" v-if="this.user.id != 0">
                <el-input type="textarea" rows="3" v-model="form" :placeholder="lang.inp"></el-input>
                <el-button :loading="forming" size="small" type="primary" style="margin-top: 10px; width: 100%" @click="submitForm">{{lang.inpSub}}</el-button>
            </div>
        </div>
        
        <!-- 修改标记弹窗 -->
        <el-dialog :title="lang2.edit" class="form" :visible.sync="editForm.show" width="30%" :before-close="() => {}" :show-close="false">
            <div>記點 : <span>{{editForm.num}}</span></div>
            <!--
            <el-input v-model="editForm.title" :placeholder="lang2.addTitle"></el-input>
            -->
            <el-input type="textarea" :autosize="{minRows:3,maxRows:6}" v-model="editForm.content" style="margin-top: 20px" :placeholder="lang2.addContent"></el-input>
            <div style="margin-top: 20px;">
                <!--
                <vue-record-audio @result="saveClip" />
                //-->
                <audio style="height: 40px;margin-left: 20px;" class="playback" :src="editForm.rec" controls />
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="cancelEdit" size="small">{{lang2.tips19}}</el-button>
                <el-button type="primary" @click="editSubmit" size="small">{{lang2.tips20}}</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
    import timer from '../tools/timer';
    import global from '@/tools/global';
    import myCanvas from '../components/myCanvas.vue';
    export default {
        name: 'project',
        data (){
            return {
                loading: true,
                info: {},
                marks: [],
                uploading: false,
                lang: {},
                lang2: {},
                form: '',
                forming: false,
                editForm: {
                    show: false,
                    num: 0,
                    title: '',
                    content: '',
                    id: 0
                },
                httping: false,
                user: {}
            }
        },
        components: {
            myCanvas
        },
        beforeMount (){
			this.lang = this.$lang('project');
			this.lang2 = this.$lang('canvas');
            this.$ajax.get(`/project/get/?id=${this.$route.query.id}`).then(r => {
                this.marks = r.marks.map(item => {
                    item.create_time = timer.time('y-m-d h:i:s', item.create_time * 1000);
                    item.status = this.lang.status[item.status];
                    item.rec = '' ;
                    return item;
                })
                console.log(this.marks) ;
                r.img_url = r.img_url.match('http') ? r.img_url : global.host + r.img_url;
                this.info = r;
                this.loading = false;
            })
        },
        mounted (){
            this.user = JSON.parse(localStorage.getItem('designer_user'));
        },
        methods: {
            // 分享
            share (){
                this.$copy(this.info.group_id, this.info.title);
            },
            // 添加标记点监听
            addMarks (r){
                let data = this.marks.map(item => item);
                data.push(r);
                this.marks = data;
            },
            // 删除标记点
            del (id, index){
                this.$ajax.get(`/marks/del/?id=${id}`).then(result => {
                    this.marks.splice(index, 1);
                    this.$message.success('success');
                })
            },
            // 编辑标记点
            edit (id, index){
                this.editForm = {
                    show: true,
                    num: this.marks[index].num,
                    title: this.marks[index].title,
                    content: this.marks[index].content,
                    rec: this.marks[index].rec,
                    id
                }
            },
            editSubmit (){
                if(this.httping) return;
                this.httping = true;
                let data = {
                    title: this.editForm.title,
                    content: this.editForm.content,
                    rec: this.editForm.rec,
                    id: this.editForm.id
                }
                this.$ajax.post('/marks/edit/', data).then(result => {
                    this.marks = this.marks.map(item => {
                        if(item.id == this.editForm.id){
                            item.title = data.title;
                            item.content = data.content;
                            item.rec = data.rec;
                        }
                        return item;
                    })
                    this.httping = false;
                    this.editForm = {
                        show: false,
                        title: '',
                        content: '',
                        rec:'',
                        id: 0
                    }
                })
            },
            // 取消修改标记点
            cancelEdit (){
                this.editForm = {
                    show: false,
                    num: 0,
                    title: '',
                    content: '',
                    id: 0
                }
            },
            // 添加描述
            submitForm (){
                if(this.user.id == 0){
                    this.$message.warning('Please login before');
                    return;
                }
                if(this.form == ''){
                    this.$message.warning("Can't submit empty text");
                    return;
                }
                this.forming = true;
                // 计算 num
                let num = 0;
                this.marks.map(item => {
                    if(item.num >= num){
                        num = Number(item.num);
                    }
                })
                num++;

                // 添加的数据
                let data = {
                    title: this.form,
                    content: this.form,
                    projectId: this.$route.query.id,
                    createrId: JSON.parse(localStorage.getItem('designer_user')).id,
                    x: 0,
                    y: 0,
                    num
                }
                
                this.$ajax.post('/marks/add/', data).then(result => {
                    result.create_time = timer.time('y-m-d h:i:s', result.create_time * 1000);
                    result.status = this.lang.status[result.status];
                    this.marks.push(result);
                    this.form = '';
                    this.forming = false;
                })
            },

            saveClip (data) {
                this.editForm.rec = window.URL.createObjectURL(data) ;
            }
        }
    }
</script>

<style lang="less" scoped>
    .project{
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: relative;
        .project_head{
            width: calc(100% - 40px);
            height: 50px;
            background: #3B3755;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
            padding: 0 20px;
            .back{
                cursor: pointer; 
            }
            .share{
                i{
                    font-size: 16px;
                    cursor: pointer;
                }
            }
        }
        .project_main{
            width: 100%;
            height: calc(100% - 50px);
            position: absolute;
            top: 50px;
            left: 0;
            .imgs{
                width: 220px;
                height: 100%;
                overflow: hidden;
                overflow-y: auto;
                background: #ffffff;
                position: absolute;
                top: 0;
                left: 0;
                p{
                    width: 100%;
                    text-indent: 30px;
                    padding: 10px 0;
                    cursor: pointer;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                p.current{
                    background: #EBEDEE;
                }
            }
            .imgs::-webkit-scrollbar{
                display: none;
            }
            .canvas{
                width: calc(100% - 260px);
                height: 100%;
                overflow: hidden;
                position: absolute;
                top: 0;
                left: 260px;
            }
            .form{
                width: 220px;
                height: 100px;
                padding: 20px;
                position: absolute;
                bottom: 40px;
                background: #ffffff;
                .playback{
                    height: 40px;
                    width: 200px;
                    margin-left: 20px;
                }
                vue-record-audio{
                    width: 40px;
                    height: 40px;
                }
            }
            .marks{
                width: 260px;
                height: calc(100% - 180px);
                overflow: hidden;
                overflow-y: auto;
                background: #ffffff;
                position: absolute;
                top: 0;
                left: 0;
                .mark_item{
                    display: flex;
                    justify-content: flex-start;
                    align-items: flex-start;
                    padding: 15px 0;
                    border-bottom: 1px solid rgb(238, 238, 238);
                    position: relative;
                    .mark_item_num{
                        width: 40px;
                        text-align: center;
                        span{
                            display: inline-block;
                            width: 22px;
                            height: 22px;
                            border-radius: 50%;
                            background: #399AF2;
                            color: #ffffff;
                            line-height: 22px;
                            font-size: 12px;
                            position: relative;
                            top: -2px;
                        }
                        span.mark_canvas{
                            background: #FD4F4F;
                        }
                    }
                    .mark_item_main{
                        width: 200px;
                        .mark_item_title{
                            color: #555555;
                        }
                        .mark_item_text{
                            font-size: 12px;
                            margin-top: 5px;
                            line-height: 24px;
                            color: #999999;
                        }
                        .mark_item_time{
                            font-size: 12px;
                            color: #999999;
                            margin-top: 10px;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        }
                    }
                    .mark_item_menu{
                        position: absolute;
                        top: 10px;
                        right: 10px;
                        i{
                            font-size: 14px;
                            color: #b9b9b9;
                            margin-left: 10px;
                            cursor: pointer;
                        }
                        i:hover{
                            color: #409eff;
                        }
                        i.designer-xiugai{
                            font-size: 12px;
                        }
                    }
                }
            }
            .marks::-webkit-scrollbar {
                width : 6px;
                height: 1px;
            }
            .marks::-webkit-scrollbar-thumb {
                border-radius: 10px;
                box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
                background: rgb(216, 216, 216);
            }
            .marks::-webkit-scrollbar-track {
                box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
                border-radius: 10px;
                background: rgb(241, 241, 241);
            }
            .nodata{
                text-align: center;
                padding-top: 50px;
                color: #a1a1a1;
            }
            .canvas.nodata{
                display: flex;
                justify-content: center;
                align-items: center;
                padding-top: 0;
                input{
                    width: 100px;
                    height: 34px;
                    opacity: 0;
                    cursor: pointer;
                    position: absolute;
                    z-index: 2;
                    top: calc(50% - 17px);
                    left: calc(50% - 50px);
                }
            }
        }
    }
</style>