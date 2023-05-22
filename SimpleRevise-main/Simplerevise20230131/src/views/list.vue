<template>
    <div class="list" v-loading="loading">
        <div class="list_head" v-if="user.role != 2 && user.id != 0">
            <el-button type="primary" size="small" icon="el-icon-arrow-left" plain @click="$router.push('/group')">{{lang.back}}</el-button>
            <div class="upload">
                <el-button type="primary" size="small" icon="el-icon-upload">{{lang.up}}</el-button>
                <input type="file" @change="upload">
            </div>
        </div>
        <div class="list_item">
            <div class="nodata" v-if="list.length == 0"><el-empty :description="lang.nodata"></el-empty></div>
            <div class="box" :id="item.id" v-for="item in list" :key="item.id">
                <div class="main">
                    <div class="menu">
                        <i class="el-icon-edit" @click="rename(item.id)" v-if="item.create_id == user.id"></i>
                        <i class="el-icon-delete" @click="del(item.id)" v-if="item.create_id == user.id"></i>
                    </div>
                    <div class="cover" @click="goProject(item.id)" v-loading="item.loading" :element-loading-text="lang.loading + '...'">
                        <img class="project_cover" :src="item.img_url.match('http') ? item.img_url : 'http://simplerevise.com/' + item.img_url" alt="">
                    </div>
                </div>
                <div class="title">
                    <p>{{item.title}}</p>
                    <p>{{item.user_name}} · {{item.create_time}}</p>
                </div>
            </div>
        </div>

        <!-- 上传状态 -->
        <div class="uploading" v-if="uploading">
            <div class="box">
                <el-progress type="circle" :percentage="uploadProgress"></el-progress>
                <p v-for="item in uploadTips" :key="item">{{item}}...</p>
            </div>
        </div>
    </div>
</template>

<script>
    import timer from '../tools/timer';
    import global from '@/tools/global';
    export default {
        name: 'list',
        data (){
            return {
                user: {},
                list: [],
                loading: true,
                uploadProgress: 0,
                uploadTips: ['正在上傳文件'],
                uploading: false,
                httping: false,
                lang: {}
            }
        },
        created (){
			this.lang = this.$lang('list');
            this.getList();
        },
        mounted (){
            this.user = JSON.parse(localStorage.getItem('designer_user'));
            console.log(this.user)
        },
        methods: {
            // 获取列表
            getList (){
                // 根据id列表获取项目信息
                this.$ajax.get('/project/list/?id=' + this.$route.query.id).then(r => {
                    this.list = r.map(item => {
                        item.create_time = timer.time('y-m-d', item.create_time * 1000);
                        item.loading = true;
                        return item;
                    });
                    
                    // 处理封面展示方式，横图 = row，竖图 = col
                    this.$nextTick(() => {
                        let imgs = document.querySelectorAll('.project_cover');
                        imgs.forEach((item, index) => {
                            let img = new Image();
                            img.onload = () => {
                                this.list[index].loading = false;
                                if(img.width > img.height){
                                    item.classList.add('row');
                                }else{
                                    item.classList.add('col');
                                }
                            }
                            img.src = item.src;
                        })
                        this.loading = false;
                    })
                })
            },
            // 重命名
            rename (id){
                let that = this;
                that.$prompt(that.lang.tips2, that.lang.tips1, {
                    inputPattern: /\S/,
                    inputErrorMessage: that.lang.tips5,
                    closeOnPressEscape: false,
                    closeOnClickModal: false,
                    cancelButtonText: that.lang.tips3,
                    confirmButtonText: that.lang.tips4,
                    beforeClose (action, instance, done){
                        if(that.httping) return;
                        that.httping = true;
                        if(action == 'confirm'){
                            that.$ajax.post('/project/rename', {
                                id,
                                title: instance.inputValue
                            }).then(r => {
                                that.$message.success('success');
                                that.list.map(item => {
                                    if(item.id == id){
                                        item.title = instance.inputValue;
                                    }
                                })
                                done();
                                setTimeout(() => {
                                    that.httping = false;
                                }, 300)
                            })
                        }else{
                            done();
                        }
                    }
                }).catch(() => {});
            },
            // 删除项目
            del (id){
                let that = this;
                this.$confirm(that.lang.tips7, that.lang.tips6, {
                    type: 'warning',
                    closeOnPressEscape: false,
                    closeOnClickModal: false,
                    cancelButtonText: that.lang.tips3,
                    confirmButtonText: that.lang.tips4,
                    beforeClose (action, instance, done){
                        if(action == 'confirm'){
                            if(that.httping) return;
                            that.httping = true;
                            let index = 0;
                            that.list.map((item, i) => {
                                if(item.id == id){
                                    index = i;
                                }
                            })
                            that.$ajax.get('/project/del/?id=' + id).then(r => {
                                that.list.splice(index, 1);
                                that.$message.success('success');
                                done();
                                setTimeout(() => {
                                    that.httping = false;
                                }, 300)
                            })
                        }else{
                            done();
                        }
                    }
                }).catch(() => {});
            },
            // 查看项目
            goProject (id){
                this.$router.push({
                    path: '/project',
                    query: { id }
                })
            },
            // 上传文件
            upload (){
                this.$nextTick(() => {
                    let input = document.querySelector('input');

                    // 如果是PDF文件
                    let fileTypes = ['jpg', 'jpeg', 'png', 'pdf'];
                    let lastName = global.getFileLastName(input.files[0].name);
                    if(fileTypes.indexOf(lastName) > -1){
                        this.uploading = true;
                        let data = {
                            createrId: JSON.parse(localStorage.getItem('designer_user')).id,
                            groupId: this.$route.query.id,
                            file: null
                        }

                        // PDF
                        if(lastName == 'pdf'){
                            this.$ajax.uploadPDF(input.files[0], (r) => {
                                this.uploadProgress = parseInt(r);
                            }).then(pdf => {
                                data.file = 'nofile';
                                data.filePath = pdf;
                                this.uploadTips.push('正在解析檔案');
                                this.$ajax.post('/project/upload/', data).then(result => {
                                    this.uploadTips.push('正在保存數據');
                                    setTimeout(() => {
                                        this.uploading = false;
                                        this.$router.go(0);
                                    }, 1000)
                                })
                            })
                            input.value = '';
                            return;
                        }else{
                            data.file = input.files[0];
                            this.uploadPost(data);
                        }
                    }else{
                        this.$message.error('不支持的檔案類型');
                        input.value = '';
                        return;
                    }
                    
                })
            },
            // 上传图片文件
            uploadPost (data){
                this.$ajax.post('/project/upload/', data, (r) => {
                    this.uploadProgress = parseInt(r);
                }).then(result => {
                    setTimeout(() => {
                        this.uploadTips.push('正在解析文件');
                    }, 1000);
                    setTimeout(() => {
                        this.uploadTips.push('正在保存数据');
                    }, 2000);
                    setTimeout(() => {
                        this.uploading = false;
                        this.$router.go(0);
                    }, 3000)
                })
            }
        }
    }
</script>

<style lang="less" scoped>
    @import url('../tools/common.less');
    .list{
        width: calc(100% - 60px);
        height: 100%;
        padding: 0 30px;
        display: flex;
        justify-content: flex-start;
        align-content: flex-start;
        flex-wrap: wrap;
        overflow-x: hidden;
        overflow-y: auto;
        .uploading{
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            .box{
                width: 300px;
                padding: 50px 0;
                background: #ffffff;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
                border-radius: 5px;
                p{
                    width: 100%;
                    text-align: center;
                    margin-top: 10px;
                    color: #888888;
                }
            }
        }
        .list_head{
            width: calc(100% - 20px);
            height: 40px;
            padding: 20px 10px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            .upload{
                margin-left: 20px;
                position: relative;
                input{
                    width: 100px;
                    height: 35px;
                    opacity: 0;
                    cursor: pointer;
                    position: absolute;
                    top: 0;
                    left: 0;
                    z-index: 99;
                }
            }
        }
        .list_item{
            width: 100%;
            height: calc(100% - 80px);
            display: flex;
            justify-content: flex-start;
            align-content: flex-start;
            flex-wrap: wrap;
            overflow-x: hidden;
            overflow-y: auto;
            .nodata{
                width: 100%;
                height: 70%;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .box{
                width: 240px;
                height: 300px;
                background: #ffffff;
                border-radius: 5px;
                margin: 10px;
                box-shadow: 0px 3px 7px 0px rgba(0,0,0,0.0500);
                .main{
                    width: 220px;
                    height: 220px;
                    background: #F5F5F7;
                    border-radius: 5px;
                    margin: 10px;
                    transition: all 0.3s;
                    .menu{
                        width: 220px;
                        height: 30px;
                        line-height: 30px;
                        display: flex;
                        justify-content: flex-end;
                        align-items: center;
                        i{
                            display: block;
                            font-size: 14px;
                            margin-right: 15px;
                            color: #888888;
                            cursor: pointer;
                        }
                        i:hover{
                            color: @color;
                        }
                        i.designer-gengduo{
                            font-size: 20px;
                        }
                    }
                    .cover{
                        width: 200px;
                        height: 170px;
                        padding: 10px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        cursor: pointer;
                        overflow: hidden;
                        img.row{
                            width: 100%;
                            border-radius: 10px;
                        }
                        img.col{
                            height: 100%;
                            border-radius: 10px;
                        }
                    }
                }
                .title{
                    width: 240px;
                    height: 60px;
                    text-align: center;
                    p:last-child{
                        margin-top: 5px;
                        font-size: 12px;
                        color: #888888;
                    }
                }
                &:hover{
                    .main{
                        background: #F8F5F2;
                    }
                }
            }
        }
            
        .list_item::-webkit-scrollbar{
            display: none;
        }
    }
</style>