<?php
    /**
     * 项目模块
     */

    include __DIR__.'/sql.php';
    class Project extends Sql {

        /**
         * 上传文件，自动创建项目
         */
        public function upload (){
            // 参数
            $createrId = $_POST['createrId'];
            $time = time();
            $groupId = $_POST['groupId'];
			$fileType = strtolower($_POST['fileType']);
            $filePath = '';

            // 处理文件
			/*
            if($_POST['file'] == 'nofile'){
                $filePath = $_POST['filePath'];
            }else{
                $lastName = array_pop(explode('.', $_FILES["file"]["name"]));
                $fileName = $time.'.'.$lastName;
                $path = './img/project/'.$fileName;
                move_uploaded_file($_FILES["file"]["tmp_name"], $path);
                $filePath = "/img/project/$fileName";
            }
			*/

			// Save Raw File
			$lastName = array_pop(explode('.', $_FILES["file"]["name"])) ;
			$fileName = $time ;
			$fileName = $time.'-0' ;
			$path = './img/project/v2/raw/'.$fileName.'.'.$lastName;
			move_uploaded_file($_FILES["file"]["tmp_name"], $path);
				
			if ($fileType == 'pdf') {
				$oImgK = new Imagick() ;
				$oImgK->setResolution(96,96);
				$oImgK->SetColorspace(Imagick::COLORSPACE_SRGB);
				$oImgK->readimage($path);
				$oImgK->setImageFormat( "jpg" );
				
				//$imgNum = $oImgK->getNumberImages() ; 
				$imgNum = 1 ;
				
				$oImgK->resetIterator();
				$oImgK = $oImgK->appendImages(true);
				//$oImgK->setImageCompression(imagick::COMPRESSION_JPEG);
				//$oImgK->setImageCompressionQuality(90);
				$oImgK->writeImages('./img/project/v2/content/'.$fileName.'.jpg', true);
				$oImgK->clear() ;
				
				$oImgK_Thumbnail = new Imagick() ;
				$oImgK_Thumbnail->setResolution(96,96);
				$oImgK_Thumbnail->SetColorspace(Imagick::COLORSPACE_SRGB);
				$oImgK_Thumbnail->readimage($path.'[0]');
				$oImgK_Thumbnail->setImageFormat( "jpg" );
				$oImgK_Thumbnail->thumbnailImage(300, 300, true, true);
				$oImgK_Thumbnail->writeImages('./img/project/v2/thumbnail/'.$fileName.'.jpg', true);
				$oImgK_Thumbnail->clear() ;
			} else {
				$oImgK = new Imagick() ;
				$oImgK->setResolution(96,96);
				$oImgK->SetColorspace(Imagick::COLORSPACE_SRGB);
				$oImgK->readimage($path);
				$oImgK->setImageFormat( "jpg" );
				$imgNum = 1 ;
				//$oImgK->setImageCompression(imagick::COMPRESSION_JPEG);
				//$oImgK->setImageCompressionQuality(90);
				$oImgK->writeImages('./img/project/v2/content/'.$fileName.'.jpg', true);
				$oImgK->thumbnailImage(300, 300, true, true);
				$oImgK->writeImages('./img/project/v2/thumbnail/'.$fileName.'.jpg', true);
				$oImgK->clear() ;
			}
			

            // 插入数据
            $this -> db -> query("
                insert into project_list (creater_id, title, create_time, img_url, pages, group_id) 
                values ('$createrId', '未命名的圖片', '$time', '$fileName', '$imgNum', '$groupId')
            ");

            // 更新项目封面
            $this -> db -> query("update project_group set cover = '$fileName' where id = '$groupId'");
            Common::response();
        }

        
        /**
         * 获取项目列表
         */
        public function list (){
            $id = $_GET['id'];
            $result = $this -> db -> query("select p.*, u.user_name from project_list p, user_list u where p.creater_id = u.id and group_id = '$id'");
            $result = Common::fetch($result);
            Common::response(200, $result);
        }

        /**
         * 移除项目
         */
        public function del(){
            $id = $_GET['id'];

            // 获取组ID
            $gid = $this -> db -> query("select group_id from project_list where id = '$id'");
            $gid = Common::fetch($gid)[0]['group_id'];

            // 删除当前记录
            $this -> db -> query("delete from project_list where id = '$id'");

            // 重新选择一张封面
            $cover = $this -> db -> query("select img_url from project_list where group_id = '$gid' order by create_time desc limit 1");
            $cover = Common::fetch($cover)[0]['img_url'];
            $this -> db -> query("update project_group set cover = '$cover' where id = '$gid'");

            Common::response();
        }

        /**
         * 重命名
         */
        public function rename (){
            $id = $_POST['id'];
            $title = $_POST['title'];
            $this -> db -> query("update project_list set title = '$title' where id = '$id'");
            Common::response(200);
        }

        /**
         * 获取项目详情
         */
        public function get (){
            $id = $_GET['id'];
            $result = $this -> db -> query("select * from project_list where id = '$id'");
            $result = Common::fetch($result)[0];

            $pid = $result['id'];
            //$marks = $this -> db -> query("select * from mark_list where project_id = '$pid'");
			$marks = $this -> db -> query("SELECT m.*, u.user_name, r.content AS playback FROM mark_list m
											LEFT JOIN user_list u ON m.creater_id = u.id
											LEFT JOIN mark_recording r ON m.id = r.mark_id
											where project_id = '$pid'");
            $marks = Common::fetch($marks);

            $result['marks'] = $marks;
            Common::response(200, $result);
        }

    }
?>