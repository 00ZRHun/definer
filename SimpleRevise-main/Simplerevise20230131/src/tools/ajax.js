/**
 * ajax 封装
 */

import { Message } from "element-ui";
import { MessageBox } from 'element-ui';
import router from "@/router";

const apiHost = 'http://simplerevise.com/api';
const ajax = {
    get(url) {
        return new Promise((reslove, reject) => {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', `${apiHost}${url}`, true);
            xhr.send();
            xhr.onreadystatechange = () => {
                if (xhr.status == 200 && xhr.readyState == 4){
					let data = JSON.parse(xhr.response);
					if(data.code == 200){
						reslove(data.data);
					}else{
						Message.error({
							message: data.msg
						})
						reject(data);
					}
                }
            }
            xhr.onerror = () => {
				MessageBox({
					title: 'System Error: #500',
					message: '网络错误，请联系管理员！错误代码：500',
					type: 'error',
					closeOnPressEscape: false,
					closeOnClickModal: false,
					showClose: false,
					confirmButtonClass: 'el-button--danger'
				})
			}
        })
    },
    post(url, data = {}, progress = () => {}) {
        return new Promise((reslove, reject) => {
            let xhr = new XMLHttpRequest();
            let form = new FormData();
            Object.keys(data).map(item => {
                form.append(item, data[item]);
            })
			xhr.upload.onprogress = (e) => {
                progress( parseFloat(e.loaded / e.total * 100).toFixed(2) );
            }
            xhr.open('POST', `${apiHost}${url}`, true);
            xhr.send(form);
            xhr.onreadystatechange = () => {
                if (xhr.status == 200 && xhr.readyState == 4){
					let data = JSON.parse(xhr.response);
					if(data.code == 200){
						reslove(data.data);
					}else{
						Message.error({
							message: data.msg
						})
						reject(data);
					}
                }
            }
            xhr.onerror = () => {
				MessageBox({
					title: 'System Error: #500',
					message: '网络错误，请联系管理员！错误代码：500',
					type: 'error',
					closeOnPressEscape: false,
					closeOnClickModal: false,
					showClose: false,
					confirmButtonClass: 'el-button--danger',
					callback (){
						window.location.reload();
					}
				})
			}
        })
    },
	uploadPDF (data = {}, progress = () => {}){
		return new Promise((reslove, reject) => {
            let xhr = new XMLHttpRequest();
            let form = new FormData();
			form.append('file', data);
			xhr.upload.onprogress = (e) => {
                progress( parseFloat(e.loaded / e.total * 100).toFixed(2) );
            }
            xhr.open('POST', 'http://8.210.236.73:39400', true);
            xhr.send(form);
            xhr.onreadystatechange = () => {
                if (xhr.status == 200 && xhr.readyState == 4){
					let data = JSON.parse(xhr.response);
					if(data.code == 200){
						reslove(data.data);
					}else{
						Message.error({
							message: data.msg
						})
						reject(data);
					}
                }
            }
            xhr.onerror = () => {
				MessageBox({
					title: 'System Error: #500',
					message: '网络错误，请联系管理员！错误代码：500',
					type: 'error',
					closeOnPressEscape: false,
					closeOnClickModal: false,
					showClose: false,
					confirmButtonClass: 'el-button--danger',
					callback (){
						window.location.reload();
					}
				})
			}
        })
	}
}
export default ajax;