<template>
    <div class="team">
        <div class="head">
            <div class="left">
                <p>{{lang.title}}</p>
                <p>{{lang.title2}}<span>{{count}}</span>{{lang.title3}}</p>
            </div>
            <div class="right">
                <el-button type="primary" size="small" style="margin-left: 10px" @click="showAdd = true">{{lang.add}}</el-button>
            </div>
        </div>
        <el-table class="table" border :data="list" size="small" v-loading="loading">
            <el-table-column prop="id" label="ID" width="100"></el-table-column>
            <el-table-column :label="lang.table.nick">
                <template slot-scope="scope">
                    <img :src="scope.row.avatar">
                    <span>{{scope.row.user_name}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="create_time" :label="lang.table.regTime"></el-table-column>
            <el-table-column prop="last_time" :label="lang.table.lastTime"></el-table-column>
            <el-table-column prop="role" :label="lang.table.role" width="160"></el-table-column>
            <el-table-column prop="statusText" :label="lang.table.status" width="160"></el-table-column>
            <el-table-column :label="lang.table.dost" width="160">
                <template slot-scope="scope">
                    <el-tooltip v-if="scope.row.status == 1" class="item" effect="dark" :content="lang.tips9" placement="top">
                        <el-button type="text" icon="el-icon-unlock" @click="off(scope.row.id, 0)"></el-button>
                    </el-tooltip>
                    <el-tooltip v-if="scope.row.status == 0" class="item" effect="dark" :content="lang.tips10" placement="top">
                        <el-button type="text" icon="el-icon-lock" @click="off(scope.row.id, 1)"></el-button>
                    </el-tooltip>
                    <el-tooltip class="item" effect="dark" :content="lang.tips8" placement="top">
                        <el-button type="text" icon="el-icon-delete" style="margin-left: 20px" @click="del(scope.row.id)"></el-button>
                    </el-tooltip>
                </template>
            </el-table-column>
        </el-table>
        <div class="pagelist">
            <el-pagination background :page-size="50" layout="prev, pager, next" :total="count"></el-pagination>
        </div>
        <el-dialog :title="lang.tips11" append-to-body :visible.sync="showAdd" width="500px" :show-close="false" :before-close="() => {}">
            <el-form :model="addForm" label-width="80px" size="small">
                <el-form-item :label="lang.tips12">
                    <el-input v-model="addForm.name" :placeholder="lang.tips13" style="width: 360px"></el-input>
                </el-form-item>
                <el-form-item :label="lang.tips14">
                    <el-select v-model="addForm.role" :placeholder="lang.tips15" style="width: 360px">
                        <el-option :label="lang.tips16" value="2"></el-option>
                        <el-option :label="lang.tips17" value="1"></el-option>
                        <el-option :label="lang.tips18" value="0"></el-option>
                    </el-select>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="cancelAdd">{{lang.tips19}}</el-button>
                <el-button size="small" type="primary" @click="add">{{lang.tips20}}</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
    import timer from '../tools/timer';
    import global from '../tools/global';
    export default {
        name: 'team',
        data (){
            return {
                key: '',
                list: [],
                count: 0,
                page: 1,
                loading: false,
                showAdd: false,
                addForm: {
                    name: '',
                    role: '2'
                }
            }
        },
        created (){
            this.lang = this.$lang('team');
        },
        mounted (){
            this.getlist();
        },
        methods: {
            // 获取数据列表
            getlist (page = 0){
                if(page > 0){
                    this.page = page;
                }
                this.loading = true;
                this.$ajax.get(`/user/list/?page=${this.page}`).then(result => {
                    console.log(result)
                    const role = this.lang.role;
                    this.count = Number(result.count);
                    this.list = result.list.map(item => {
                        item.id = Number(item.id) + 1000000;
                        item.last_time = timer.time('y-m-d h:i:s', item.last_time * 1000);
                        item.create_time = timer.time('y-m-d h:i:s', item.create_time * 1000);
                        item.statusText = item.status == 0 ? this.lang.status1 : this.lang.status2;
                        item.role = role[item.role];
                        item.avatar = `${global.host}${item.avatar}`;
                        return item;
                    })
                    this.loading = false;
                })
            },
            // 锁定/解锁账户
            off (id, status){
                this.$ajax.get(`/user/off/?id=${id - 1000000}&status=${status}`).then(result => {
                    this.$message.success(this.lang.tips1);
                    this.getlist();
                })
            },
            // 取消添加账户
            cancelAdd (){
                this.showAdd = false;
                this.addForm = {
                    name: '',
                    role: '2'
                }
            },
            // 删除
            del (id){
                this.$confirm(this.lang.tips2, this.lang.tips3, {
                    confirmButtonText: this.lang.tips5,
                    cancelButtonText: this.lang.tips4,
                    type: 'warning'
                }).then(() => {
                    this.$ajax.get(`/user/del/?id=${id - 1000000}`).then(result => {
                        this.$message.success(this.lang.tips6);
                        this.getlist(1);
                    })
                }).catch(() => {});
            },
            // 添加账户
            add (){
                if(this.addForm.name == ''){
                    this.$message.warning(this.lang.tips7);
                    return;
                }
                this.$ajax.post('/user/add', {
                    account: this.addForm.name,
                    role: Number(this.addForm.role)
                }).then(result => {
                    this.showAdd = false;
                    this.addForm = {
                        name: '',
                        role: '2'
                    }
                    this.getlist(1);
                })
            }
        }
    }
</script>

<style lang="less" scoped>
    @import url('../tools/common.less');
    .team{
        width: calc(100% - 60px);
        height: calc(100% - 60px);
        padding: 30px;
        background: #ffffff;
        .head{
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            .left{
                p:first-child{
                    font-size: 18px;
                }
                p:last-child{
                    font-size: 14px;
                    margin-top: 8px;
                    color: #999999;
                    span{
                        color: @color;
                    }
                }
            }
            .right{
                display: flex;
                justify-content: flex-end;
                align-items: center;
            }
        }
        .table{
            width: 100%;
            max-height: calc(100% - 160px);
            margin-top: 50px;
            img{
                width: 38px;
                height: 38px;
                vertical-align: middle;
                border-radius: 50%;
                margin-right: 10px;
            }
        }
        .pagelist{
            text-align: center;
            margin-top: 20px;
        }
    }
</style>