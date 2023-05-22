<template>
    <div class="user">
        <div class="head">
            <p>{{lang.title}}</p>
            <p>{{lang.title2}}</p>
        </div>
        <div class="content">
            <div class="item itemImg">
                <img style="margin-left: 30px" :src="user.avatar" alt="">
            </div>
            <div class="item" style="margin-top: 20px">
                <p>{{lang.form.account}}：{{user.user_name}}</p>
                <p><i class="el-icon-edit-outline" @click="rename"></i></p>
            </div>
            <div class="item">
                <p>{{lang.form.passwd}}：******</p>
                <el-button type="text" style="margin-left: 20px" @click="repass">{{lang.form.reset}}</el-button>
            </div>
        </div>
    </div>
</template>

<script>
    import global from '@/tools/global';
    export default {
        name: 'user',
        data (){
            return {
                lang: {},
                user: {},
                httping: false
            }
        },
        created (){
            this.lang = this.$lang('user');
        },
        mounted (){
            this.user = JSON.parse(localStorage.getItem('designer_user'));
			this.user.avatar = global.host + this.user.avatar;
        },
        methods: {
            // 重命名
            rename (id){
                let that = this;
                that.$prompt(that.lang.alert.account.title2, that.lang.alert.account.title, {
                    inputPattern: /\S/,
                    inputErrorMessage: that.lang.alert.account.error,
                    closeOnPressEscape: false,
                    closeOnClickModal: false,
					cancelButtonText: this.lang.alert.account.cancel,
					confirmButtonText: this.lang.alert.account.confirm,
                    beforeClose (action, instance, done){
                        if(action == 'confirm'){
                            if(that.httping) return;
                            that.httping = true;
                            that.$ajax.post('/user/rename', {
                                id: that.user.id,
                                name: instance.inputValue
                            }).then(r => {
                                done();
                                that.$message.success(that.lang.alert.account.success);
                                setTimeout(() => {
                                    that.$message.warning(that.lang.alert.account.success2);
                                    that.httping = false;
                                }, 200);
                                setTimeout(() => {
                                    that.$router.push('/login');
                                }, 1000);
                            })
                        }else{
                            done();
                        }
                    }
                }).catch(() => {});
            },
            
            // 重置密码
            repass (id){
                let that = this;
                that.$prompt(that.lang.alert.passwd.title2, that.lang.alert.passwd.title, {
                    inputPattern: /\S/,
                    inputErrorMessage: that.lang.alert.passwd.error,
                    closeOnPressEscape: false,
                    closeOnClickModal: false,
					cancelButtonText: this.lang.alert.passwd.cancel,
					confirmButtonText: this.lang.alert.passwd.confirm,
                    beforeClose (action, instance, done){
                        if(action == 'confirm'){
                            if(that.httping) return;
                            that.httping = true;
                            that.$ajax.post('/user/repass', {
                                id: that.user.id,
                                password: instance.inputValue
                            }).then(r => {
                                done();
                                that.$message.success(that.lang.alert.passwd.success);
                                setTimeout(() => {
                                    that.$message.warning(that.lang.alert.passwd.success2);
                                    that.httping = false;
                                }, 200);
                                setTimeout(() => {
                                    that.$router.push('/login');
                                }, 1000);
                            })
                        }else{
                            done();
                        }
                    }
                }).catch(() => {});
            }
        }
    }
</script>

<style lang="less" scoped>
    @import url('../tools/common.less');
    .user{
        width: 100%;
        height: 100%;
        .head{
            width: 100%;
            height: 200px;
            background: url('@{imgurl}/img/userbg.png');
            text-align: center;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-content: center;
            flex-wrap: wrap;
            p{
                width: 100%;
            }
            p:first-child{
                font-size: 30px;
            }
            p:last-child{
                font-size: 14px;
                margin-top: 10px;
            }
        }
        .content{
            width: calc(100% - 60px);
            height: calc(100% - 240px);
            margin: 20px auto;
            box-shadow: 0px 3px 7px 0px rgba(0,0,0,0.0500);
            border-radius: 10px;
            background: #ffffff;
            .item{
                width: 200px;
                margin: 0 auto;
                padding: 5px 50px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                img{
                    width: 100px;
                    cursor: pointer;
                }
                i{
                    margin-left: 20px;
                    font-size: 18px;
                    cursor: pointer;
                }
                i:hover{
                    color: @color;
                }
            }
            .itemImg{
                justify-content: center;
                padding-top: 50px;
            }
        }
    }
</style>