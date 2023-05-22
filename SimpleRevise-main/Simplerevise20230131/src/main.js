import Vue from 'vue'
import App from './App.vue'
import router from './router'
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
//import VueRecord from '@codekraft-studio/vue-record';
import lang from './tools/lang';
import ajax from './tools/ajax';


Vue.use(ElementUI);
//Vue.use(VueRecord);
Vue.config.productionTip = false;

// 多语言支持
Vue.prototype.$lang = moduleName => {
    let type = localStorage.getItem('lang') || 'zht';
    return lang[type][moduleName];
}

// 复制分享链接
Vue.prototype.$copy = (id, title) => {
    let user = JSON.parse(localStorage.getItem('designer_user')).user_name;
    let input = document.createElement('textarea');
    input.value = `${user} shared a project for you. Come and have a look! \n Project address: http://simplerevise.com/${id} \n entry name: ${title}`;
    document.body.append(input);
    input.select();
    document.execCommand('copy');
    input.remove();
    ElementUI.Message({
        type: 'success',
        message: 'Copied project link !'
    })
}

// ajax
Vue.prototype.$ajax = ajax;

new Vue({
    router,
    render: function (h) { return h(App) }
}).$mount('#app')
