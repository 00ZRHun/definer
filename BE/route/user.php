<?php
    /**
     * 用户模块
     */

    include __DIR__.'/sql.php';
    class User extends Sql {

        /**
         * 登录
         */
        public function login (){
            $user_name = $_POST['account'];
            $password = md5($_POST['password']);
            $result = $this -> db -> query("select * from user_list where user_name = '$user_name' and passwd = '$password'");
            $rows = Common::fetch($result);
            if(count($rows) > 0){
                if($rows[0]['status'] == '0'){
                    $r = $rows[0];
                    unset($r['passwd']);
                    $time = time();
                    $id = $r['id'];
                    $this -> db -> query("update user_list set last_time = $time where id = '$id'");
                    Common::response(200, $r, '登录成功');
                }else{
                    Common::response(401, '', '该账户已被冻结');
                }
            }else{
                Common::response(401, '', '用户名或密码错误');
            }
        }
        
        
        /**
         * 注册新账户
         */
        public function reg (){
            $userName = $_POST['account'];
            $passwd = md5($_POST['passwd']);
            $time = time();
            
            $result = $this -> db -> query("select user_name from user_list where user_name = '$userName'");
            $result = Common::fetch($result);
            if(count($result) > 0){
                Common::response(201, '', '用户已存在');
            }else{
                $this -> db -> query("insert into user_list (user_name, passwd, create_time, role, projects) values ('$userName', '$passwd', '$time', '1', ',')");
                Common::response(200);
            }
        }

        /**
         * 获取用户列表
         */
        public function list (){
            $limit = ((int)$_GET['page'] - 1) * 50;
            $result = $this -> db -> query("select * from user_list order by id limit $limit,50");
            $result = Common::fetch($result);
            $count = $this -> db -> query("select count(id) from user_list");
            $count = Common::fetch($count)[0]['count(id)'];
            Common::response(200, [
                'count' => $count,
                'list' => $result
            ]);
        }

        /**
         * 更改账户状态
         */
        public function off (){
            $id = $_GET['id'];
            $status = $_GET['status'];
            $this -> db -> query("update user_list set status = '$status' where id in ($id)");
            Common::response(200);
        }

        /**
         * 删除用户
         */
        public function del (){
            $id = $_GET['id'];
            $this -> db -> query("delete from user_list where id in ($id)");
            Common::response(200);
        }
        
        /**
         * 修改用户名
         */
        public function rename (){
            $id = $_POST['id'];
            $name = $_POST['name'];

            // 查询是否已存在
            $result = $this -> db -> query("select id from user_list where user_name = '$name'");
            $result = Common::fetch($result);
            if(count($result) > 0){
                Common::response(201, '', 'Account already exists.');
            }else{
                $this -> db -> query("update user_list set user_name = '$name' where id = '$id'");
                Common::response(200);
            }
        }

        /**
         * 修改密码
         */
        public function repass (){
            $id = $_POST['id'];
            $password = md5($_POST['password']);
            $this -> db -> query("update user_list set passwd = '$password' where id = '$id'");
            Common::response(200);
        }


        /**
         * 添加账户
         */
        public function add (){
            $userName = $_POST['account'];
            $role = $_POST['role'];
            $password = md5('000000');
            $time = time();
            
            $result = $this -> db -> query("select user_name from user_list where user_name = '$userName'");
            $result = Common::fetch($result);
            if(count($result) > 0){
                Common::response(201, $result, '用户已存在');
            }else{
                $this -> db -> query("
                    insert into user_list (user_name, passwd, create_time, role) 
                    values ('$userName', '$passwd', '$time', $role)
                ");
                Common::response(200);
            }
        }
        

    }
?>