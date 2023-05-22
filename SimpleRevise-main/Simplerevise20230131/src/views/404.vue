<template>
	<div class="welcome">
		<p class="face"><i class="osadmin osadminyepian"></i></p>
		<p class="hi">当前页面未找到</p>
		<p class="vsrsion">404 Not Found</p>
		<el-button type="info" plain style="width: 200px; margin-top: 20px" @click="back">返 回</el-button>
	</div>
</template>

<script>
	export default {
		methods: {
			back (){
				this.$router.go(-1);
			}
		}
	}
</script>

<style lang="less" scoped>
	@topHeight: 150px;
	.welcome{
		width: 100%;
		height: calc(100% - @topHeight);
		padding-top: @topHeight;
		font-size: 32px;
		text-align: center;
		user-select: none;
		i{
			font-size: 300px;
			color: rgb(233, 233, 233);
		}
		.hi{
			color: rgb(189, 189, 189);
		}
		.vsrsion{
			font-size: 18px;
			margin-top: 20px;
			color: rgb(189, 189, 189);
		}
	}
</style>