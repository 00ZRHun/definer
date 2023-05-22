import Vue from 'vue'
import VueRouter from 'vue-router'
import index from '../views/index.vue'

Vue.use(VueRouter)

const originalPush = VueRouter.prototype.push;
VueRouter.prototype.push = function push (location) {
	return originalPush.call(this, location).catch(err => err)
}

const routes = [
    {
        path: '/',
        name: 'index',
        component: index,
		children: [
			{
				path: '/group',
				name: 'group',
				component: () => import('../views/group.vue')
			},
			{
				path: '/list',
				name: 'list',
				component: () => import('../views/list.vue')
			},
			{
				path: '/user',
				name: 'user',
				component: () => import('../views/user.vue')
			},
			{
				path: '/team',
				name: 'team',
				component: () => import('../views/team.vue')
			},
			{
				path: '/404',
				name: '404',
				component: () => import('../views/404.vue')
			}
		]
    },
	{
		path: '/project',
		name: 'project',
		component: () => import('../views/project.vue')
	},
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/login.vue')
    },
    {
        path: '/reg',
        name: 'reg',
        component: () => import('../views/reg.vue')
    }
]

const router = new VueRouter({
    mode: 'hash',
    base: process.env.BASE_URL,
    routes
})

// 路由拦截，自定义404
let routesNameArray = [];
routes.map(item => {
	routesNameArray.push(item.name);
	item.children && item.children.map(i => {
		routesNameArray.push(i.name);
	})
})

router.beforeEach((to, from, next) => {
	// 判断是否在登陆状态
	if(to.path != '/login' && to.path != '/reg' && to.query.custom != 1 && !localStorage.getItem('designer_user')){
		next({
			path: '/login'
		})
	}

	// 自定义404
	if(routesNameArray.indexOf(to.name) == -1){
		next({
			path: '/404'
		})
	}

	next();
})
export default router
