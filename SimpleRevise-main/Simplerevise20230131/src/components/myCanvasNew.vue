<template>
    <div class="myCanvas" v-loading="loading" :element-loading-text="lang.loading + '...'">
        <div class="menu" v-show="showMenu" @mouseleave="showMenu = false" :style="{top: menuY + 'px', left: menuX + 'px'}">
            <p v-show="!showDel && !pening" @click="addForm.show = true">
                <i class="designer- designer-biaozhu_f" style="font-size: 14px;"></i> {{lang.add}}
            </p>
            <p v-show="showDel" @click="del">
                <i class="designer- designer-shanchu-yichu-01" style="font-size: 14px;"></i> {{lang.del}}
            </p>
            <p v-show="showDel" @click="edit">
                <i class="designer- designer-xiugai" style="font-size: 12px;"></i> {{lang.edit}}
            </p>
            <p @click="pen">
                <i class="designer- designer-huabigongju-tuya" style="font-size: 14px;"></i> {{pening ? lang.close : lang.open}}
            </p>
            <p @click="cancelHistory">
                <i class="designer- designer-chehui" style="font-size: 14px;"></i> {{lang.back}}
            </p>
            <p @click="showMenu = false">
                <i class="designer- designer-quxiao" style="font-size: 14px;"></i> {{lang.cancel}}
            </p>
        </div>
        <div class="img">
            <!--
            <template v-for="(item, index) in marks">
                <i v-if="item.pos_x > 0" class="designer- designer-map-thumbtack-full" :style="{left: item.pos_x + 'px', top: item.pos_y + 'px'}" :key="index">
            //-->
                
                <i class="designer- designer-map-thumbtack-full" v-for="(item, index) in marks" :key="index" :style="{left: item.pos_x + 'px', top: item.pos_y + 'px'}">
                
                    <span :id="item.id" @click="editMark(item.id)">{{item.num}}</span>
                </i>
            <!--
            </template>
            //-->
        </div>
        <canvas id="canvas"></canvas>

        <!-- 添加标记弹窗 -->
        <el-dialog :title="lang.add" class="form" :visible.sync="addForm.show" width="30%" :before-close="() => {}" :show-close="false">
            <el-input type="textarea" :autosize="{minRows:3,maxRows:6}" v-model="addForm.content" style="margin-top: 20px" :placeholder="lang.addContent"></el-input>
            <span slot="footer" class="dialog-footer">
                <el-button @click="cancelAdd" size="small">{{lang.tips19}}</el-button>
                <el-button type="primary" @click="add" size="small">{{lang.tips20}}</el-button>
            </span>
        </el-dialog>

        <!-- 修改标记弹窗 -->
        <el-dialog :title="lang.edit" class="form" :visible.sync="editForm.show" width="30%" :before-close="() => {}" :show-close="false">
            <div>記點 : <span>{{editForm.num}}</span></div>
            <!--
            <el-input v-model="editForm.content" style="margin-top: 20px" :placeholder="lang.addContent"></el-input>
            -->
            <el-input type="textarea" :autosize="{minRows:3,maxRows:6}" v-model="editForm.content" style="margin-top: 20px" :placeholder="lang.addContent"></el-input>
            <span slot="footer" class="dialog-footer">
                <el-button @click="cancelEdit" size="small">{{lang.tips19}}</el-button>
                <el-button type="primary" @click="editSubmit" size="small">{{lang.tips20}}</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
    // TO Work
    import timer from '../tools/timer';
    export default {
        name: 'myCanvas',
        props: ['url', 'datas', 'imgId'],
        data (){
            return {
                user: {},
                loading: true,
                marks: [],
                box: {
                    width: 0,
                    height: 0
                },
                img: {
                    width: 0,
                    height: 0
                },
                scale: 1,
                maxScale: 4,
                minScale: 0.1,
                scaleUnit: 0.1,
                top: 0,
                transTop: 0,
                left: 0,
                transLeft: 0,
                transUnit: 2,
                showMenu: false,
                menuX: 0,
                menuY: 0,
                showDel: false,
                addForm: {
                    show: false,
                    title: '',
                    content: ''
                },
                editForm: {
                    show: false,
                    num: 0,
                    title: '',
                    content: ''
                },
                currentMarkId: 0,
                currentMArkPosX: 0,
                currentMArkPosY: 0,
                httping: false,
                pening: false,
                lang: {},
                lang2: {},
            }
        },
        mounted (){
			this.lang = this.$lang('canvas');
            this.lang2 = this.$lang('project');
            this.user = JSON.parse(localStorage.getItem('designer_user'));
            // 获取图片地址
            let timer = setInterval(() => {
                if(this.url){
                    clearInterval(timer);
                    this.init();

                    // 标注
                    this.marks = this.datas;
                }
            }, 200);

            this.getCanvas() ;
        },
        methods: {
            // 初始化图片
            init (){
                this.$nextTick(() => {
                    let box = document.querySelector('.myCanvas');
                    let img = document.querySelector('.img');
                    // 容器尺寸
                    this.box = {
                        width: box.clientWidth,
                        height: box.clientHeight
                    }

                    // 图片尺寸
                    let image = new Image();
                    image.onload = () => {
                        this.loading = false;
                        this.img = {
                            width: image.clientWidth,
                            height: image.clientHeight
                        }
                        this.defaultSize();
                    }
                    image.src = this.url;
                    img.appendChild(image);
                    box.appendChild(img);
                })
            },
            // 控制图片默认尺寸
            defaultSize (){
                // 如果图片宽度大于容器宽度，缩小一半
                console.log(this.img, this.box)
                if(this.img.width > this.box.width){
                    this.img.width = this.img.width/2;
                    this.img.height = this.img.height/2;
                }

                // 如果高度大于容器高度，缩小一半
                if(this.img.height > this.box.height){
                    this.img.width = this.img.width/2;
                    this.img.height = this.img.height/2;
                }

                // 位置
                this.defaultPosition();
            },
            // 控制图片默认位置
            defaultPosition (){
                this.top = this.box.height/2 - this.img.height/2;
                this.left = this.box.width/2 - this.img.width/2;

                // 确认图片尺寸
                this.$nextTick(() => {
                    document.querySelector('.img img').style.cssText = `width: ${this.img.width}px; height: ${this.img.height}px`;
                })

                // 设置图片
                this.mouseSlide();
            },
            // 监听鼠标滚轮操作
            mouseSlide (){
                this.$nextTick(() => {
                    let img = document.querySelector('.img');
                    img.onmouseover = () => {
                        img.onmousewheel = (e) => {
                            this.scale = e.deltaY > 0 ? this.scale - this.scaleUnit : this.scale + this.scaleUnit;
                            
                            // 超大 && 超小
                            if(this.scale > this.maxScale){
                                this.scale = this.scale - this.scaleUnit;
                                return;
                            }
                            if(this.scale < this.minScale){
                                this.scale = this.scale + this.scaleUnit;
                                return;
                            }
                            
                            this.set();
                        }
                    }
                })
                this.draw();
            },
            // 监听拖拽操作
            draw (e){
                this.$nextTick(() => {
                    let img = document.querySelector('.img');
                    let canvas = document.querySelector('canvas');
                    let ctx = canvas.getContext('2d');
                    let path = [];

                    // 绘制路径
                    const ctxFill = (x, y) => {
                        ctx.beginPath();
                        ctx.arc(x, y, 2, 0, 2 * Math.PI); // 填充一个半径为15px的圆形区域
                        path.push({x, y})
                        ctx.fillStyle = 'rgb(203, 50, 50)';
                        ctx.fill();
                        ctx.closePath();
                    }

                    // 还原已经保存的路径
                    /**
                    if(localStorage.getItem('path') && JSON.parse(localStorage.getItem('path')).length > 0){
                        let a = JSON.parse(localStorage.getItem('path'));
                        for(let i=0; i<a.length; i++){
                            setTimeout(() => {
                                ctxFill(a[i].x, a[i].y);
                            }, 0)
                        }
                    }
                    **/
                   this.drawCanvas(ctxFill) ;

                    // 右键菜单
                    img.oncontextmenu = (e) => {
                        /*
                        if(this.user.id == 0){
                            return;
                        }
                        */
                        //this.showDel = e.path[0].nodeName == 'SPAN' ? true : false;
                        this.showDel = e.target.localName == 'span' ? true : false;
                        
                        console.log(e) ;
                        if(this.showDel){
                            this.currentMarkId = e.target.id;
                        }
                        this.menuX = e.pageX - 220 - 20;
                        this.menuY = e.pageY - 50 - 20;
                        this.currentMArkPosX = e.offsetX;
                        this.currentMArkPosY = e.offsetY;
                        this.showMenu = true;
                        document.querySelector('.menu').focus();
                        return false;
                    }

                    // 点击移动
                    img.onmousedown = (e) => {

                        if(this.showMenu){
                            this.showMenu = false;
                        }
                        e.preventDefault();
                        let cando = true;
                        if(e.button == 2){
                            return false;
                        }
                        
                        if(this.pening){
                            img.onmousemove = (ee) => {
                                ctxFill(ee.offsetX, ee.offsetY);
                            }
                        }else{
                            let x = e.screenX;
                            let y = e.screenY;

                            img.onmousemove = (ee) => {
                                if(!cando) return;
                                cando = false;

                                setTimeout(() => {
                                    if(ee.screenX > x){
                                        this.left += ee.screenX - x;
                                    }else{
                                        this.left -= x - ee.screenX;
                                    }

                                    if(ee.screenY > y){
                                        this.top += ee.screenY - y;
                                    }else{
                                        this.top -= y - ee.screenY;
                                    }

                                    this.set();
                                    cando = true;
                                    x = ee.screenX;
                                    y = ee.screenY;
                                }, 10);
                            }
                        }
                    }

                    // 取消移动
                    img.onmouseup = () => {
                        img.onmousemove = null;
                        if(this.pening){
                            path.push({x: -1, y: -1});
                            localStorage.setItem('path', JSON.stringify(path));
                        }
                    }
                    img.onmouseleave = () => {
                        img.onmousemove = null;
                    }
                })
                this.set();
            },
            // 渲染图片 和 canvas
            set (){
                this.$nextTick(() => {
                    let img = document.querySelector('.img');
                    let canvas = document.querySelector('canvas');
                    img.style.cssText = `
                        transform: scale(${this.scale});
                        width: ${this.img.width}px;
                        height: ${this.img.height}px;
                        top: ${this.top}px;
                        left: ${this.left}px;
                    `;

                    // canvas 的 width 和 height 被重新赋值时，会重置 canvas，导致绘制的路径丢失，所以禁止重复设置
                    if(canvas.getAttribute('width') == null){
                        canvas.setAttribute('width', this.img.width);
                        canvas.setAttribute('height', this.img.height);
                    }
                    canvas.style.cssText = `
                        transform: scale(${this.scale});
                        position: absolute;
                        top: ${this.top}px;
                        left: ${this.left}px;
                        z-index: 11;
                    `;
                })
            },
            // 添加标记点
            add (){
                if(this.httping) return;
                this.httping = true;
                // 计算 num
                let num = 0;
                this.marks.map(item => {
                    if(item.num >= num){
                        num = Number(item.num);
                    }
                })
                num++;

                // 添加的数据
                let data = {
                    title: this.addForm.content,
                    content: this.addForm.content,
                    projectId: this.$route.query.id,
                    createrId: JSON.parse(localStorage.getItem('designer_user')).id,
                    x: this.currentMArkPosX - 20,
                    y: this.currentMArkPosY - 40,
                    num
                }
                
                this.$ajax.post('/marks/add/', data).then(result => {
                    result.create_time = timer.time('y-m-d h:i:s', result.create_time * 1000);
                    result.status = this.lang2.status[result.status];
                    this.addForm = {
                        show: false,
                        title: '',
                        content: ''
                    }
                    this.marks.push(result);
                    this.httping = false;
                })
            },
            // 取消添加标记点
            cancelAdd (){
                this.addForm = {
                    show: false,
                    title: '',
                    content: ''
                }
            },
            // 修改标记点
            edit (){
                this.marks.map(item => {
                    if(item.id == this.currentMarkId){
                        this.editForm = {
                            show: true,
                            num: item.num,
                            title: item.title,
                            content: item.content
                        }
                        this.showMenu = false;
                    }
                })
            },
            editSubmit (){
                console.log(this.httping)
                if(this.httping) return;
                this.httping = true;
                let data = {
                    title: this.editForm.title,
                    content: this.editForm.content,
                    id: this.currentMarkId
                }
                this.$ajax.post('/marks/edit/', data).then(result => {
                    this.editForm = {
                        show: false,
                        title: '',
                        content: ''
                    }
                    this.marks = this.marks.map(item => {
                        if(item.id == this.currentMarkId){
                            item.title = data.title;
                            item.content = data.content;
                        }
                        return item;
                    })
                    this.httping = false;
                })
            },
            // 取消修改标记点
            cancelEdit (){
                this.editForm = {
                    show: false,
                    title: '',
                    content: ''
                }
            },
            // 删除标记点
            del (){
                if(this.httping) return;
                this.httping = true;
                this.$ajax.get(`/marks/del/?id=${this.currentMarkId}`).then(result => {
                    let index = 0;
                    this.marks.map((item, i) =>{
                        if(item.id == this.currentMarkId){
                            index = i;
                        }
                    })
                    this.marks.splice(index, 1);
                    this.$message.success('success');
                    this.showDel = false;
                    this.currentMarkId = 0;
                    this.showMenu = false;
                    this.httping = false;
                })
            },
            // 进入画笔模式
            pen (){
                if (this.pening) {
                    this.saveCanvas() ;
                }
                this.pening = !this.pening;
                this.$nextTick(() => {
                    document.querySelector('.img').style.cursor = this.pening ? 'crosshair' : 'pointer';
                })
                this.showMenu = false;
            },
            // 撤回上一步的操作
            cancelHistory (){
                this.$message.error('暂未开放！');
            },



            editMark (id) {
                this.currentMarkId = id;
                this.edit();
            },

            drawCanvas (ctxFill) {
                if(localStorage.getItem('path') && JSON.parse(localStorage.getItem('path')).length > 0){
                    let a = JSON.parse(localStorage.getItem('path'));
                    for(let i=0; i<a.length; i++){
                        setTimeout(() => {
                            ctxFill(a[i].x, a[i].y);
                        }, 0)
                    }
                }
            },

            saveCanvas () {
                let data = {
                    projectId: this.$route.query.id,
                    createrId: JSON.parse(localStorage.getItem('designer_user')).id,
                    canvasPath: localStorage.getItem('path')
                }
                this.$ajax.post('/canvas/update/', data).then(result => {
                    
                })
            },

            getCanvas () {
                this.$ajax.get(`/canvas/get/?id=${this.$route.query.id}`).then(result => {
                    if (result.length > 0) {
                        localStorage.setItem('path', result[0].path);
                    } else {
                        localStorage.setItem('path', '[]');
                    }
                })
            }
        }
    }
</script>

<style lang="less" scoped>
    @import url('../tools/common.less');
    .myCanvas{
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        .menu{
            width: 140px;
            background: #ffffff;
            box-shadow: 0px 3px 7px 0px rgba(0,0,0,0.0500);
            border-radius: 5px;
            overflow: hidden;
            position: absolute;
            z-index: 999;
            padding: 10px 0;
            p{
                padding: 10px 15px;
                color: #797979;
                cursor: pointer;
                user-select: none;
                font-size: 13px;
            }
            p:hover{
                color: @color;
                background: rgb(233, 239, 247);
            }
        }
        .img{
            cursor: pointer;
            position: absolute;
            z-index: 10;
            transition: transform 0.3s;
            i{
                display: inline-block;
                width: 40px;
                height: 40px;
                font-size: 40px;
                border-radius: 50%;
                text-align: center;
                color: #FD4F4F;
                position: absolute;
                z-index: 2;
                span{
                    display: inline-block;
                    width: 40px;
                    height: 40px;
                    color: #ffffff;
                    font-size: 12px;
                    line-height: 25px;
                    text-align: center;
                    position: absolute;
                    top: 0;
                    left: 0;
                }
            }
        }
        canvas{
            transition: transform 0.3s;
            pointer-events: none;
        }
        .form{
            height: 80%;
            padding-bottom: 20%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    }
</style>