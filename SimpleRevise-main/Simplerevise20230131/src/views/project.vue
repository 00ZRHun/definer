<template>
    <div class="project">
        <div class="project_head">
            <div class="back">
                <span @click="$router.go(-1)">
                    <i class="el-icon-arrow-left"></i> {{lang.back}}
                </span>
            </div>
            <div class="title">{{info.title}}</div>
            <div class="share" @click="share">
                <i class="el-icon-share"></i>
            </div>
        </div>
        <div class="project_main" v-loading="loading">


            <div class="canvas">
              <!--
                <myCanvas :url="info.img_url" :datas="marks" @add="addMarks"></myCanvas>
              //-->

                <ul class="canvas_navbar">
                  <li @click="undoCanvas()">
                      <i class="el-icon-arrow-left"></i>UNDO
                  </li>
                  <li @click="redoCanvas()">
                      <i class="el-icon-arrow-right"></i>REDO
                  </li>
                  <li @click="saveCanvas">
                      <i class="el-icon-folder-add"></i>SAVE
                  </li>
                  <li  @click="resetCanvas"> 
                      <i class="el-icon-refresh"></i>CLEAR ALL
                  </li>
                    <ul>
                        <li v-for="(tool,index) in canvasTools" class="canvas_toolbar">
                          <i :key="index" :class="tool.class" @click="currentTool=tool.name"></i>
                        </li>
                        <li class="canvas_toolbar">
                          <el-color-picker v-model="brushColor" size="mini"></el-color-picker>
                        </li>
                    </ul>
                </ul>
                <div class="canvas_body">
                    <div class="canvas_img"></div>
                    <canvas @mousedown="onCanvasMouseDown" @mouseup="onCanvasMouseUp"></canvas>
                </div>
            </div>


            <div class="marks" v-if="marks.length > 0">
                <div class="mark_item" v-for="(item, index) in marks" :key="item.id">
                    <div class="mark_item_num">
                        # {{item.num}}
                        <span v-if="item.pos_x == 0"></span>
                        <span class="mark_canvas" v-if="item.pos_x > 0"></span>
                    </div>
                    <div class="mark_item_user">Name Here</div>
                    <div class="mark_item_time">
                            <span>{{item.create_time}}</span>
                            <!--
                            <span>{{item.status}}</span>
                            //-->
                    </div>
                    <div class="mark_item_main">
                        <pre class="mark_item_title">{{item.content}}</pre>
                    </div>
                    <div class="mark_item_menu" v-if="user.id != 0">
                        <i class="el-icon-edit-outline" @click="edit(item.id, index)"></i>
                        <i class="el-icon-microphone" @click="edit(item.id, index)"></i>
                        <i class="el-icon-delete" @click="del(item.id, index)"></i>
                    </div>
                </div>
            </div>
            <div class="marks nodata" v-if="marks.length == 0">
                {{lang.nodata}}
            </div>
            <div class="form" v-if="this.user.id != 0">
                <el-input type="textarea" rows="8" v-model="form" :placeholder="lang.inp"></el-input>
                <el-button :loading="forming" size="small" type="primary" style="margin-top: 0px; width: 100%; background-color: #345CA0; padding: 15px 0px;" @click="submitForm">{{lang.inpSub}}</el-button>
            </div>
        </div>
        
        <!-- 修改标记弹窗 -->
        <el-dialog :title="lang2.edit" :visible.sync="editForm.show" width="30%" :before-close="() => {}" :show-close="false">
            <div>記點 : <span>{{editForm.num}}</span></div>
            <!--
            <el-input v-model="editForm.title" :placeholder="lang2.addTitle"></el-input>
            -->
            <el-input type="textarea" :autosize="{minRows:3,maxRows:6}" v-model="editForm.content" style="margin-top: 20px" :placeholder="lang2.addContent"></el-input>
            <div style="margin-top: 20px;">
                <!--
                <vue-record-audio @result="saveClip" />
                //-->
                <audio style="height: 40px;margin-left: 20px;" class="playback" :src="editForm.rec" controls />
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="cancelEdit" size="small">{{lang2.tips19}}</el-button>
                <el-button type="primary" @click="editSubmit" size="small">{{lang2.tips20}}</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
    import timer from '../tools/timer';
    import global from '@/tools/global';
    export default {
        name: 'project',
        data (){
            return {
                loading: true,
                info: {},
                marks: [],
                uploading: false,
                lang: {},
                lang2: {},
                form: '',
                forming: false,
                editForm: {
                    show: false,
                    num: 0,
                    title: '',
                    content: '',
                    id: 0
                },
                httping: false,
                user: {},


                canvasBody: {
                    width: 0,
                    height: 0
                },
                canvasImg: {
                    width: 0,
                    height: 0
                },
                currentCanvas: {
                    width: 0,
                    height: 0,
                    top: 0,
                    left: 0,

                },
                isCanvasMouseDown: false,
                canvasContext: null,
                canvasTools: [
                  {
                    class:'el-icon-edit',
                    name:'paint-brush'
                  },{
                    class:'el-icon-scissors',
                    name:'eraser'
                  },{
                    class:'el-icon-news',
                    name:'square'
                  },{
                    class:'el-icon-zoom-in',
                    name:'zoom-in'
                  },{
                    class:'el-icon-zoom-out',
                    name:'zoom-out'
                  }],
                currentTool: 'paint-brush',
                tempPosition: null,
                tempCanvas: null,
                brushColor: '#000000'
            }
        },
        /*
        components: {
            myCanvas
        },
        */
        beforeMount (){
            this.lang = this.$lang('project');
            this.lang2 = this.$lang('canvas');
            this.$ajax.get(`/project/get/?id=${this.$route.query.id}`).then(r => {
                this.marks = r.marks.map(item => {
                    item.create_time = timer.time('y-m-d h:i:s', item.create_time * 1000);
                    item.status = this.lang.status[item.status];
                    item.rec = '' ;
                    return item;
                })
                console.log(this.marks) ;
                r.img_url = r.img_url.match('http') ? r.img_url : global.host + r.img_url;
                this.info = r;
                this.loading = false;
                this.setCanvas();
            })
        },
        mounted (){
            this.user = JSON.parse(localStorage.getItem('designer_user'));
            //this.setCanvas();
            this.setWindowEvent();
        },
        methods: {
            // 分享
            share (){
                this.$copy(this.info.group_id, this.info.title);
            },
            // 添加标记点监听
            addMarks (r){
                let data = this.marks.map(item => item);
                data.push(r);
                this.marks = data;
            },
            // 删除标记点
            del (id, index){
                this.$ajax.get(`/marks/del/?id=${id}`).then(result => {
                    this.marks.splice(index, 1);
                    this.$message.success('success');
                })
            },
            // 编辑标记点
            edit (id, index){
                this.editForm = {
                    show: true,
                    num: this.marks[index].num,
                    title: this.marks[index].title,
                    content: this.marks[index].content,
                    rec: this.marks[index].rec,
                    id
                }
            },
            editSubmit (){
                if(this.httping) return;
                this.httping = true;
                let data = {
                    title: this.editForm.title,
                    content: this.editForm.content,
                    rec: this.editForm.rec,
                    id: this.editForm.id
                }
                this.$ajax.post('/marks/edit/', data).then(result => {
                    this.marks = this.marks.map(item => {
                        if(item.id == this.editForm.id){
                            item.title = data.title;
                            item.content = data.content;
                            item.rec = data.rec;
                        }
                        return item;
                    })
                    this.httping = false;
                    this.editForm = {
                        show: false,
                        title: '',
                        content: '',
                        rec:'',
                        id: 0
                    }
                })
            },
            // 取消修改标记点
            cancelEdit (){
                this.editForm = {
                    show: false,
                    num: 0,
                    title: '',
                    content: '',
                    id: 0
                }
            },
            // 添加描述
            submitForm (){
                if(this.user.id == 0){
                    this.$message.warning('Please login before');
                    return;
                }
                if(this.form == ''){
                    this.$message.warning("Can't submit empty text");
                    return;
                }
                this.forming = true;
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
                    title: this.form,
                    content: this.form,
                    projectId: this.$route.query.id,
                    createrId: JSON.parse(localStorage.getItem('designer_user')).id,
                    x: 0,
                    y: 0,
                    num
                }
                
                this.$ajax.post('/marks/add/', data).then(result => {
                    result.create_time = timer.time('y-m-d h:i:s', result.create_time * 1000);
                    result.status = this.lang.status[result.status];
                    this.marks.push(result);
                    this.form = '';
                    this.forming = false;
                })
            },


            // GGG
            saveClip (data) {
                this.editForm.rec = window.URL.createObjectURL(data) ;
            },


            setCanvas(){       
              let canvas_body = document.querySelector('.canvas_body') ;
              let canvas_img = document.querySelector('.canvas_img') ;

              this.canvasBody = {
                  width: canvas_body.clientWidth,
                  height: canvas_body.clientHeight
              }
              
              let image = new Image() ;
              image.onload = () => {
                  //this.loading = false;
                  this.canvasImg = {
                      width: image.offsetWidth,
                      height: image.offsetHeight
                  }

                  let canvas_img = document.querySelector('.canvas_img') ;
                  let canvas = document.querySelector('canvas') ;
                  let ctx =  canvas.getContext('2d') ;
                  ctx.lineCap = "round" ;
                  ctx.lineJoin = "round" ;
                  this.canvasContext = ctx ;

                  if (this.canvasImg.width + 100>= this.canvasBody.width || this.canvasImg.height + 100 >= this.canvasBody.height) {
                      if (this.canvasImg.width >= this.canvasImg.height) {
                        this.currentCanvas.height = (this.canvasImg.height > this.canvasBody.height / 2) ? this.canvasBody.height / 2 : this.canvasImg.height;
                        this.currentCanvas.width  = (this.currentCanvas.height / this.canvasImg.height) * this.canvasImg.width ;
                      } else {
                        this.currentCanvas.width = (this.canvasImg.width > this.canvasBody.width / 2) ? this.canvasBody.width / 2 : this.canvasImg.width;
                        this.currentCanvas.height = (this.currentCanvas.width / this.canvasImg.width) * this.canvasImg.height ;
                      }
                  } else {
                      this.currentCanvas.width = this.canvasImg.width ;
                      this.currentCanvas.height = this.canvasImg.height ;
                  }
                  
                  this.currentCanvas.top = 100 ;
                  this.currentCanvas.left = this.canvasBody.width / 2 - this.currentCanvas.width  / 2 ;
                  canvas_img.style.cssText = 'top: ' + this.currentCanvas.top + 'px; left: ' + this.currentCanvas.left + 'px; width: ' + this.currentCanvas.width + 'px; height: ' + this.currentCanvas.height + 'px'  ;
                  canvas.style.cssText = 'top: ' + this.currentCanvas.top + 'px; left: ' + this.currentCanvas.left + 'px' ;
                  canvas.width  = this.currentCanvas.width ;
                  canvas.height  = this.currentCanvas.height ;
              }
              image.src = this.info.img_url ;
              image.style.width = '100%';
              canvas_img.appendChild(image) ;
            }, 

            saveCanvas(){
              /*
              let url =  this.$refs['sketchpad'].toDataURL("image/png", 1.0) ;
              const link = document.createElement('a') ;
              link.innerText = 'Download' ;
              link.href = url  ;
              link.download = 'circle.png'      ;
              link.click() ;
              */
              this.$notify({
                  title: 'Save Canvas',
                  message: 'Function is under construction',
                  type: 'warning'
              });
            },

            resetCanvas(){
              /*
              let canvas = this.canvasContext.canvas ;
              this.canvasContext.clearRect(0, 0, canvas.width, canvas.height);
              this.saveCanvasToHistory() ;
              */
              this.$notify({
                  title: 'Reset Canvas',
                  message: 'Function is under construction',
                  type: 'warning'
              });
            },

            undoCanvas() {
              window.history.back() ;
            },

            redoCanvas() {
              window.history.forward() ;
            },
  
            onCanvasMouseDown(){ 
              this.isCanvasMouseDown = true ;
              this.setTempCanvas() ;
              
            },
            onCanvasMouseUp(){
              this.saveCanvasToHistory() ;
              this.resetToolState() ;
            },

            setTempCanvas(){ 
              let ctx = this.canvasContext
              let canvas = ctx.canvas 
              let tempCanvas = ctx.getImageData(0, 0, canvas.width, canvas.height);
              this.tempCanvas = tempCanvas
              console.log('in') ;
            }, 

            saveCanvasToHistory(){
              let ctx = this.canvasContext
              let canvas = ctx.canvas 
              let tempCanvas = ctx.getImageData(0, 0, canvas.width, canvas.height);
              window.history.pushState(tempCanvas, null);
              console.log('out') ;
            },

            setWindowEvent(){
              window.addEventListener('mousemove',(event)=>{
                  
                  let currentPos = this.getCanvasMousePosition(event.clientX,event.clientY) ;
                  /*
                  if(this.isSizing && this.tempPosition ){
                    let dragValue= currentPos.y- this.tempPosition.y 
                    this.checkSizeDrag(dragValue)
                  }
                  */
                  if(this.isCanvasMouseDown && this.tempPosition){
                    let pos = this.getCanvasMousePosition(event.clientX,event.clientY)
                    switch(this.currentTool){
                      case 'paint-brush' : 
                            this.drawCanvas((ctx)=>{
                                ctx.strokeStyle = this.brushColor ;
                                ctx.moveTo(this.tempPosition.x,this.tempPosition.y) ;
                                ctx.lineTo(pos.x,pos.y) ;
                            })
                            break;
                      case 'eraser' :
                            this.drawCanvas((ctx)=>{
                              ctx.strokeStyle = this.backgroundColor
                              ctx.moveTo(this.tempPosition.x,this.tempPosition.y)
                              ctx.lineTo(pos.x,pos.y)
                              }) 
                            break;  
                      case 'square' :
                            this.drawCanvas((ctx)=>{
                              // ctx.moveTo(this.tempPosition.x,this.tempPosition.y)
                              // ctx.lineTo(pos.x,pos.y)
                              let currentPos = this.getCanvasMousePosition(event.clientX,event.clientY)
                              
                              ctx.strokeStyle =  this.currentColor.code
                              let tempx = this.tempPosition.x
                              let tempy = this.tempPosition.y
                              let width = currentPos.x - tempx
                              let height = currentPos.y - tempy
                              
                              ctx.putImageData(this.tempCanvas,0, 0)                      
                              ctx.rect(tempx, tempy, width, height);
                            })
                            
                            break;  
                    }
                    
                }
                
                //if(this.currentTool=='square' && this.isCanvasMouseDown )return
                this.setCanvasTempPosition(currentPos.x,currentPos.y) ;
                
                
              })
              window.addEventListener('popstate',(e)=>{
                this.canvasContext.putImageData(e.state, 0, 0);
              })
            },

            getCanvasMousePosition(clientX,clientY){
              let canvasPosition = this.canvasContext.canvas.getBoundingClientRect() ;
              let x = clientX-canvasPosition.x ;
              let y = clientY-canvasPosition.y ;
              return {x,y} ;
            },

            setCanvasTempPosition(x,y){
              this.tempPosition={x,y} ;
            },

            drawCanvas(action){
              let canvasContext = this.canvasContext ;
              canvasContext.beginPath() ;
              canvasContext.lineWidth = 2 ;        
              action(canvasContext) ;
              canvasContext.stroke() ;
            },

            resetToolState(){
                this.tempPosition = null ;
                this.isCanvasMouseDown = false ;
            }, 

        }
    }
</script>

<style lang="less" scoped>
    .project{
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: relative;
        .project_head{
            width: calc(100% - 40px);
            height: 65px;
            background: #FFFFFF;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #000000;
            font-weight: 500;
            padding: 0 15px;
            border-bottom: 1px solid #E6E6E6;
            .back{
                cursor: pointer; 
            }
            .share{
                i{
                    font-size: 16px;
                    cursor: pointer;
                }
            }
        }
        .project_main{
            width: 100%;
            height: calc(100% - 66px);
            position: absolute;
            top: 66px;
            left: 0;
            .imgs{
                width: 220px;
                height: 100%;
                overflow: hidden;
                overflow-y: auto;
                background: #ffffff;
                position: absolute;
                top: 0;
                left: 0;
                p{
                    width: 100%;
                    text-indent: 30px;
                    padding: 10px 0;
                    cursor: pointer;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                p.current{
                    background: #EBEDEE;
                }
            }
            .imgs::-webkit-scrollbar{
                display: none;
            }
            .canvas{
                width: calc(100% - 299px);
                height: 100%;
                overflow: hidden;
                position: absolute;
                top: 0;
                left: 299px;
                .canvas_navbar {
                    height: 40px;
                    border-bottom: 1px solid  #E6E6E6;
                    background-color: #FFFFFF;
                    li{
                        display: inline-block;
                        padding:12px;
                        cursor: pointer;
                        i{
                            margin-right: 8px;
                        }
                    }
                    li:hover{
                        background-color: #F1F1F1;
                    }
                    ul {
                        position: absolute;
                        top: 0;
                        right: 25px;
                        .canvas_toolbar {
                            font-size: 20px;
                            padding-top:7px;
                            padding-bottom:10px;
                          i{
                              line-height: 20px;
                              margin-right: 0px;
                          }
                        }
                        li:last-child{
                            padding:0px;
                        }
                        li:last-child:hover{
                            background-color: #FFFFFF;
                        }
                    }
                }
                .canvas_body {
                    width: 100%;
                    height: 100%;
                    overflow: scroll;
                    position: absolute;
                    top: 41px;
                    left: 0;
                    .canvas_img {
                        position: absolute;
                        z-index: 10;
                    }
                    canvas {
                        position: absolute;
                        z-index: 20;
                    }
                }
                .canvas_body::-webkit-scrollbar {
                    width : 6px;
                    height: 1px;
                }
                .canvas_body::-webkit-scrollbar-thumb {
                    border-radius: 10px;
                    box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
                    background: rgb(216, 216, 216);
                }
                .canvas_body::-webkit-scrollbar-track {
                    box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
                    border-radius: 10px;
                    background: rgb(241, 241, 241);
                }
            }
            .form{
                width: 298px;
                height: 224px;
                padding: 0px;
                position: absolute;
                bottom: 0px;
                background: #FFFFFF;
                border-right: 1px solid #E6E6E6;
            }
            .marks{
                width: 298px;
                height: calc(100% - 224px);
                overflow: hidden;
                overflow-y: auto;
                background: #ffffff;
                position: absolute;
                top: 0;
                left: 0;
                border-right: 1px solid #E6E6E6;
                .mark_item{
                    padding: 25px;
                    border-bottom: 1px solid #E6E6E6;
                    position: relative;
                    .mark_item_num{
                        color: #86868B;
                        span{
                            display: inline-block;
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: #345CA0;
                            margin-left: 5px;
                        }
                        span.mark_canvas{
                            background: #F84D46;
                        }
                    }
                    .mark_item_user{
                        font-weight: 500;
                        margin-top: 15px;
                    }
                    .mark_item_time{
                        color: #86868B;
                        margin-top: 5px;
                        margin-bottom: 15px;
                    }
                    .mark_item_main{
                        .mark_item_title{
                            color: #000000;
                        }
                    }
                    .mark_item_menu{
                        position: absolute;
                        top: 25px;
                        right: 15px;
                        i{
                            font-size: 14px;
                            color: #000000;
                            cursor: pointer;
                            border-radius: 50%;
                            padding: 8px;
                        }
                        i:hover{
                            background-color: #FFFFFF;
                        }
                    }
                }
                .mark_item:hover{
                  background-color: #F1F1F1;
                }
            }
            .marks::-webkit-scrollbar {
                width : 6px;
                height: 1px;
            }
            .marks::-webkit-scrollbar-thumb {
                border-radius: 10px;
                box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
                background: rgb(216, 216, 216);
            }
            .marks::-webkit-scrollbar-track {
                box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
                border-radius: 10px;
                background: rgb(241, 241, 241);
            }
            .nodata{
                text-align: center;
                padding-top: 50px;
                color: #a1a1a1;
            }
            .canvas.nodata{
                display: flex;
                justify-content: center;
                align-items: center;
                padding-top: 0;
                input{
                    width: 100px;
                    height: 34px;
                    opacity: 0;
                    cursor: pointer;
                    position: absolute;
                    z-index: 2;
                    top: calc(50% - 17px);
                    left: calc(50% - 50px);
                }
            }
        }
    }
</style>