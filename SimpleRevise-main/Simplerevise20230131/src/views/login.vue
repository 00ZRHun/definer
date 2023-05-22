<template>
	<div class="login">
		<div class="box">
			<div class="title">{{lang.title}}</div>
			<div class="title">Welcome To Login</div>
			<div class="form">
				<el-input style="margin-top: 20px" v-model="account" :placeholder="lang.inputs[0]"></el-input>
				<el-input style="margin-top: 20px" type="password" v-model="password" :placeholder="lang.inputs[1]"></el-input>
				<div class="form_foot">
					<el-checkbox v-model="remenberPassword">{{lang.foot[0]}}</el-checkbox>
					<span @click="submit(0)">{{lang.foot[1]}}</span>
				</div>
			</div>
			<el-button type="primary" class="submit" :loading="submitting" @click="submit(1)">{{lang.submit}}</el-button>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'login',
		data (){
			return {
				lang: {},
				account: '',
				password: '',
				remenberPassword: true,
				submitting: false
			}
		},
		created (){
			// 多语言支持
			this.lang = this.$lang('login');
		},
		methods: {
			// 提交登录
			submit (type){
				// 注册
				if(type == 0){
					this.$router.push('/reg');
					return;
				}

				// 表单验证
				if(this.account == ''){
					this.$message.warning(this.lang.tips.form.account);
					return;
				}
				if(this.password == ''){
					this.$message.warning(this.lang.tips.form.passwd);
					return;
				}

				// 登录请求
				this.submitting = true;
				this.$ajax.post('/user/login', {
					account: this.account,
					password: this.password
				}).then(r => {
					localStorage.setItem('designer_user', JSON.stringify(r));
					this.$router.push('/');
				}).catch(() => {
					this.submitting = false;
				})
			}
		}
	}
</script>

<style lang="less" scoped>
	@import url('../tools/common.less');
	.login{
		width: 100%;
		height: 100%;
		position: absolute;
		background: url('@{imgurl}/img/login-bg.jpg') #ffffff;
		background-size: cover;
		background-position: center center;
		.box{
			width: 600px;
			height: calc(100% - 120px);
			padding-top: 120px;
			position: absolute;
			top: 0;
			right: 0;
			background: #ffffff;
			box-shadow: 0 0 30px 0 rgba(65, 95, 217, 0.3);
			.title{
				width: 400px;
				margin: 10px auto;
				font-size: 30px;
				color: #555555;
			}
			.form{
				width: 400px;
				margin: 100px auto;
				input{
					width: 100%;
					margin: 10px 0;
				}
				.form_foot{
					width: 100%;
					display: flex;
					margin-top: 10px;
					justify-content: space-between;
					color: #666666;
					span{
						cursor: pointer;
					}
				}
			}
			.submit{
				width: 400px;
				display: block;
				margin: 0 auto;
			}
			.reg{
				margin-top: 20px;
			}
		}
	}
</style>