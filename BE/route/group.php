<?php
    /**
     * 项目模块
     */

    include __DIR__.'/sql.php';
    class Group extends Sql {
        
        /**
         * 创建分组
         */
        public function add (){
            // 参数
            $name = $_POST['name'];
            $createrId = $_POST['createrId'];
            $time = time();
            $id = '9'.substr($time, 1);

            // 执行sql
            $this -> db -> query("
                insert into project_group (id, group_name, creater_id, create_time) 
                values ('$id', '$name', '$createrId', '$time')
            ");

            // 更新个人项目信息
            $this -> db -> query("update user_list set projects = concat(projects, ',$id') where id = '$createrId'");

            // 获取全部结果
            $result = $this -> db -> query("select projects from user_list where id = '$createrId'");
            $result = Common::fetch($result)[0]['projects'];

            Common::response(200, $result);
        }

        /**
         * 获取分组列表
         */
        public function list (){
            // 获取项目id列表
            $ids = $_GET['ids'];
			$createrId = $_GET['createrId'];
			if ($createrId == 1 || $createrId == 50) {
				$result = $this -> db -> query("select p.*, u.user_name from project_group p, user_list u where p.creater_id = u.id");
			} else {
				$result = $this -> db -> query("select p.*, u.user_name from project_group p, user_list u where p.creater_id = u.id and p.id in ($ids)");
			}
            $result = Common::fetch($result);
            Common::response(200, $result);
        }

        /**
         * 重命名
         */
        public function rename (){
            $id = $_POST['id'];
            $name = $_POST['name'];
            $this -> db -> query("update project_group set group_name = '$name' where id = '$id'");
            Common::response();
        }

        /**
         * 移除项目
         */
        public function del (){
            $id = $_GET['id'];
            $this -> db -> query("delete from project_group where id = '$id'");
            Common::response();
        }

        /**
         * 用户和项目绑定
         */
        public function bind (){
            $uid = $_GET['uid'];
            $pid = $_GET['pid'];
            $this -> db -> query("update user_list set projects = concat(projects, ',$pid') where id = '$uid'");
            Common::response();
        }
    }
?>