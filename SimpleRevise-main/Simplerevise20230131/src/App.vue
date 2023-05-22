<template>
	<div id="app">
		<router-view />
	</div>
</template>

<script>
    export default {
        mounted (){
            this.$nextTick(() => {
                document.body.oncontextmenu = (e) => {
                    return false;
                }
            })
        }
    }
</script>

<style lang="less">
    *{
        margin: 0;
        padding: 0;
    }
    html, body{
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        font-family: Avenir, Helvetica, Arial, sans-serif;
    }
    #app {
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: absolute;
        top: 0;
        left: 0;
        font-size: 14px;
        color: rgb(66, 66, 66);
        font-family: Avenir, Helvetica, Arial, sans-serif;
        background: #f5f6f9;
    }

    // 重置 element 样式
    // .el-message-box{
    //     position: relative;
    //     top: -10%;
    // }
</style>
