<template>
    <div class="index">
        <!-- 头部 -->
        <div class="head">
			<div class="menu">
				<span v-for="item in menu" :key="item.name" :class="item.name == currentMenu ? 'current' : ''" @click="menuClick(item)">
					<i :class="item.icon"></i> {{item.name}}
				</span>
			</div>
			<div class="user">
				<img :src="user.avatar" alt="">
				<p>{{hi}}</p>
				<span @click="logout"><i class="designer- designer-tuichu"></i> {{lang.logout}}</span>
				<el-dropdown @command="switchLang">
					<span class="el-dropdown-link">
						{{lang.lang}} <i class="el-icon-arrow-down el-icon--right"></i>
					</span>
					<el-dropdown-menu slot="dropdown">
						<el-dropdown-item command="0">繁體中文</el-dropdown-item>
						<el-dropdown-item command="1">简体中文</el-dropdown-item>
						<el-dropdown-item command="2">English</el-dropdown-item>
					</el-dropdown-menu>
				</el-dropdown>
			</div>
		</div>

        <!-- 内容区 -->
        <div class="content">
			<router-view />
		</div>
    </div>
</template>

<script>
	import global from '@/tools/global';
	export default {
		name: 'index',
		data (){
			return {
				lang: {},
				user: {},
				hi: '',
				menu: [],
				currentMenu: ''
			}
		},
		created (){
			// 多语言支持
			this.lang = this.$lang('index');
			this.menu = [ 
				{
					name: this.lang.menu.list,
					route: 'group',
					icon: 'el-icon-pie-chart'
				},
				{
					name: this.lang.menu.user,
					route: 'user',
					icon: 'el-icon-user'
				}
			];
			this.currentMenu = this.lang.menu.default;
		},
		mounted (){
			// 获取用户信息
			this.user = JSON.parse(localStorage.getItem('designer_user'));
			this.user.avatar = global.host + this.user.avatar;
			if(this.user.id == 0){
				this.menu = [];
			}
			if(this.user.role == 0 && this.user.id != 0){
				this.menu.push({
					name: this.lang.menu.team,
					route: 'team',
					icon: 'designer- designer-qunchengyuan-02'
				})
			}

			// 欢迎语
			if(this.user.role == 0){
				this.hi = `${this.lang.role.admin} ${this.user.user_name} ${this.lang.role.hi}`;
			}
			if(this.user.role == 1){
				this.hi = `${this.lang.role.designer} ${this.user.user_name} ${this.lang.role.hi}`;
			}
			if(this.user.role == 2){
				this.hi = `${this.lang.role.custom} ${this.user.user_name} ${this.lang.role.hi}`;
			}
			if(this.user.id == 0){
				this.hi = `Custom`;
			}

			// 默认进入 group 路由
			if(this.$route.name == 'index'){
				this.$router.push('/group');
			}

			// 刷新时菜单高亮
			switch (this.$route.name){
				case 'list':
					this.currentMenu = this.lang.menu.list;
					break;
				case 'user':
					this.currentMenu = this.lang.menu.user;
					break;
				case 'team':
					this.currentMenu = this.lang.menu.team;
					break;
			}
		},
		methods: {
			// 切换菜单
			menuClick (item){
				this.currentMenu = item.name;
				this.$router.push(item.route);
			},
			// 切换语言
			switchLang (index){
				if(index == 0){
					localStorage.setItem('lang', 'zht');
					this.$message.warning('語言已切換，請重新登入');
				}
				if(index == 1){
					localStorage.setItem('lang', 'zh');
					this.$message.warning('语言已切换，请重新登录');
				}
				if(index == 2){
					localStorage.setItem('lang', 'en');
					this.$message.warning('Language has been switched, please login again');
				}
				localStorage.removeItem('designer_user');
				this.$router.push('/login');
			},
			// 退出登录
			logout (){
				let that = this;
				this.$confirm(this.lang.tips.logout.title, this.lang.tips.logout.t, {
                    type: 'warning',
					cancelButtonText: this.lang.tips.logout.cancel,
					confirmButtonText: this.lang.tips.logout.confirm,
                    beforeClose (action, instance, done){
                        if(action == 'confirm'){
                            that.$router.push('/login');
							localStorage.removeItem('designer_user');
                            done();
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
    .index{
		width: 100%;
		height: 100%;

		/* 头部 */
		.head{
			width: calc(100% - 60px);
			height: 60px;
			border-bottom: 1px solid rgb(236, 236, 236);
			padding: 0 30px;
			background: #ffffff;
			position: absolute;
			top: 0;
			left: 0;
			z-index: 3;
			display: flex;
			justify-content: space-between;
			align-items: center;
			.user{
				display: flex;
				justify-content: flex-start;
				align-items: center;
				img{
					width: 45px;
					height: 45px;
					border-radius: 50%;
				}
				p{
					margin-left: 20px;
				}
				span{
					margin-left: 30px;
					cursor: pointer;
				}
			}
			.menu{
				display: flex;
				justify-content: flex-end;
				align-items: center;
				span{
					display: block;
					padding: 0 15px;
					height: 58px;
					line-height: 60px;
					margin: 0 10px;
					cursor: pointer;
					user-select: none;
					border-bottom: 2px solid transparent;
				}
				span.current{
					color: @color;
					border-bottom: 2px solid rgb(126, 172, 250);
				}
			}
		}


		/* 内容区 */
		.content{
			width: 100%;
			height: calc(100% - 61px);
			position: absolute;
			top: 60px;
			left: 0;
			z-index: 1;
		}
	}
</style>
