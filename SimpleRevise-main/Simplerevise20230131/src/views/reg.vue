<template>
	<div class="reg">
		<div class="box">
			<div class="title">{{lang.titleReg}}</div>
			<div class="title">Create Your Account</div>
			<div class="form">
				<el-input style="margin-top: 20px" v-model="account" :placeholder="lang.inputs[0]"></el-input>
				<el-input style="margin-top: 20px" type="password" v-model="password" :placeholder="lang.inputs[1]"></el-input>
				<div class="form_foot">
					<span @click="login">{{lang.foot[2]}}</span>
				</div>
			</div>
			<el-button type="primary" class="submit" :loading="submitting" plain @click="submit">{{lang.reg}}</el-button>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'reg',
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
			submit (){
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
				this.$ajax.post('/user/reg', {
					account: this.account,
					passwd: this.password
				}).then(r => {
					this.$message.success('注册成功，请登录以激活账户！');
					this.$router.push('/login');
				}).catch(() => {
					this.submitting = false;
				})
			},
			// 去登录
			login (){
				this.$router.push('/login');
			}
		}
	}
</script>

<style lang="less" scoped>
	@import url('../tools/common.less');
	.reg{
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
				margin: 50px auto;
				margin-top: 100px;
				input{
					width: 100%;
					margin: 10px 0;
				}
				.form_foot{
					width: 100%;
					display: flex;
					margin-top: 10px;
					justify-content: space-between;
					color: #9c9a9a;
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
		}
	}
</style>