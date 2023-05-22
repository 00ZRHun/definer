<template>
    <div class="group" v-loading="loading">
        <div class="group_head" v-if="user.role != 2">
            <el-button type="primary" size="small" icon="el-icon-circle-plus-outline" @click="addProject">{{lang.add}}</el-button>
        </div>
        <div class="group_item">
            <div class="nodata" v-if="group.length == 0"><el-empty :description="lang.nodata"></el-empty></div>
            <div class="box" v-for="item in group" :key="item.id">
                <div class="main">
                    <div class="menu">
                        <i class="el-icon-share" @click="share(item.id)"></i>
                        <i class="el-icon-edit" @click="rename(item.id)" v-if="item.creater_id == user.id || user.role == 0"></i>
                        <i class="el-icon-delete" @click="del(item.id)" v-if="item.creater_id == user.id || user.role == 0"></i>
                    </div>
                    <div class="cover" @click="goProject(item.id)" v-loading="item.loading" :element-loading-text="lang.loading + '...'">
                        <img class="project_cover" :src="item.cover.match('http') ? item.cover : global.host + item.cover" alt="">
                    </div>
                </div>
                <div class="title">
                    <p>{{item.group_name}}</p>
                    <p>{{item.user_name}} · {{item.create_time}}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import timer from '../tools/timer';
    import global from '@/tools/global';
    export default {
        name: 'group',
        data (){
            return {
                user: {},
                group: [],
                loading: true,
                global,
                httping: false,
                lang: {}
            }
        },
        created (){
			this.lang = this.$lang('group');
            this.user = JSON.parse(localStorage.getItem('designer_user'));

            // 没有参与项目
            if(this.user.projects.substring(1).length == 0){
                this.group = [];
                this.loading = false;
                return;
            }

            this.getgroup();
            
        },
        methods: {
            // 获取项目组列表
            getgroup (){
                // 根据id列表获取项目信息
                this.user.projects = this.user.projects.substring(1);
                if(this.user.projects[0] == ','){
                    this.user.projects = this.user.projects.substring(1);
                }
                this.$ajax.get('/group/list/?ids=' + this.user.projects + '&createrId=' + this.user.id).then(r => {
                    this.group = r.map(item => {
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
                                this.group[index].loading = false;
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
            // 分享项目名
            share (id){
                this.group.map(item => {
                    if(item.id == id){
                        this.$copy(id, item.group_name);
                    }
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
                        if(action == 'confirm'){
                            if(that.httping) return;
                            that.httping = true;
                            that.$ajax.post('/group/rename', {
                                id,
                                name: instance.inputValue
                            }).then(r => {
                                that.$message.success('success');
                                that.group.map(item => {
                                    if(item.id == id){
                                        item.group_name = instance.inputValue;
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
                            that.group.map((item, i) => {
                                if(item.id == id){
                                    index = i;
                                }
                            })
                            that.$ajax.get('/group/del/?id=' + id).then(r => {
                                that.group.splice(index, 1);
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
                    path: '/list',
                    query: { id }
                })
            },
            // 添加新项目
            addProject (){
                let that = this;
                that.$prompt(that.lang.tips5, that.lang.add, {
                    inputPattern: /\S/,
                    inputErrorMessage: that.lang.tips5,
                    closeOnPressEscape: false,
                    closeOnClickModal: false,
                    cancelButtonText: that.lang.tips3,
                    confirmButtonText: that.lang.tips4,
                    beforeClose (action, instance, done){
                        if(action == 'confirm'){
                            if(that.httping) return;
                            that.httping = true;
                            that.$ajax.post('/group/add', {
                                name: instance.inputValue,
                                createrId: JSON.parse(localStorage.getItem('designer_user')).id
                            }).then(r => {
                                done();
                                that.setGroup(r);
                                that.$message.success('success');
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
            // 更新项目id
            setGroup (r){
                this.user.projects = r;
                localStorage.setItem('designer_user', JSON.stringify(this.user));
                this.getgroup();
            }
        }
    }
</script>

<style lang="less" scoped>
    @import url('../tools/common.less');
    .group{
        width: calc(100% - 60px);
        height: 100%;
        padding: 0 30px;
        display: flex;
        justify-content: flex-start;
        align-content: flex-start;
        flex-wrap: wrap;
        overflow-x: hidden;
        overflow-y: auto;
        .group_head{
            width: calc(100% - 20px);
            height: 40px;
            padding: 20px 10px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        .group_item{
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
            
        .group_item::-webkit-scrollbar{
            display: none;
        }
    }
</style>