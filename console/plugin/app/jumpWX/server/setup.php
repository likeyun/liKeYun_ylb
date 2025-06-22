<?php

    /**
     * 安装程序：2.4.1
     */

	// 编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        $LoginUser = $_SESSION["yinliubao"];
        
        // 读取JSON文件内容
        $jsonFile = '../app.json';
        $jsonData = file_get_contents($jsonFile);
        
        // 检测配置文件目录权限
        if(!installPermission('../../')) {
            
            // 无755权限
            $result = array(
        		'code' => 202,
                'msg' => '安装失败，原因：console/plugin/app 目录没有755权限！请前往服务器修改权限！'
        	);
        	echo json_encode($result,JSON_UNESCAPED_UNICODE);
        	exit;
        }
        
        // 解码JSON数据
        $data = json_decode($jsonData, true);
        
        // 连接数据库
        include '../../../../Db.php';
        $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        
        // 验证当前登录用户是否为管理员
        $check_admin = "SELECT user_admin FROM huoma_user WHERE user_name = '$LoginUser'";
        $check_admin_result = $conn->query($check_admin)->fetch_assoc();
        
        // 如果不是管理员，不允许安装
        if($check_admin_result['user_admin'] == 2) {
            
            $result = array(
        		'code' => 202,
                'msg' => '安装失败：没有安装权限！'
        	);
        	echo json_encode($result,JSON_UNESCAPED_UNICODE);
        	exit;
        }
        
        // 获取安装状态
        $status = $data['install'];
        
        // 1 为未安装
        if($status == 1) {
            
            // 设置为已安装
            $data['install'] = 2;
            $data['install_time'] = date('Y-m-d H:i:s');
            $data['current_status'] = "已安装";
            
            // 编码为JSON格式
            // JSON_PRETTY_PRINT：格式化JSON
            // JSON_UNESCAPED_UNICODE：不对中文编码
            // JSON_UNESCAPED_SLASHES：不对斜杠进行反斜杠编码
            $appJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // 写回JSON文件
            file_put_contents($jsonFile, $appJsonData);
            
            // 如果你需要操作数据库
            // 请在这里编写你操作数据库的逻辑

            // ylb_jumpWX 表
            $ylb_jumpWX = "CREATE TABLE `ylb_jumpWX` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `jw_id` int(10) DEFAULT NULL COMMENT 'ID',
              `jw_title` varchar(64) DEFAULT NULL COMMENT '标题',
              `jw_beizhu` varchar(64) DEFAULT NULL COMMENT '副标题&摘要&描述',
              `jw_icon` text DEFAULT NULL COMMENT '分享图',
              `jw_common_landpage` text DEFAULT NULL COMMENT '通用落地页',
              `jw_douyin_landpage` text DEFAULT NULL COMMENT '抖音落地页',
              `jw_url` text DEFAULT NULL COMMENT '目标链接',
              `jw_pv` int(9) DEFAULT '0' COMMENT '访问次数',
              `jw_fwl_limit` int(10) DEFAULT '100000' COMMENT '访问次数限制',
              `jw_clickNum` int(9) DEFAULT '0' COMMENT '点击次数',
              `jw_platform` VARCHAR(10) DEFAULT NULL COMMENT '投放平台',
              `jw_original_content` TEXT DEFAULT NULL COMMENT '目标链接原始内容',
              `jw_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `jw_expire_time` VARCHAR(32) DEFAULT '2036-12-31 23:59:59' COMMENT '到期时间',
              `jw_token` varchar(64) DEFAULT NULL COMMENT 'ToKen',
              `jw_key` varchar(6) DEFAULT NULL COMMENT 'Key',
              `jw_status` int(1) DEFAULT '1' COMMENT '状态 1正常 2停用',
              `jw_beizhu_msg` varchar(64) DEFAULT NULL COMMENT '后台显示的备注',
              `jw_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信外链2.4.1'";
            
            if($conn->query($ylb_jumpWX) === TRUE) {
                
                // 创建表成功
                // 操作成功
                $result = array(
        			'code' => 200,
                    'msg' => '安装成功'
        		);
            }else {
                
                // 创建表失败
                // 操作失败
                $result = array(
        			'code' => 201,
                    'msg' => '安装失败：' . $conn->error
        		);
            }
            
        }else {
            
            // 操作失败
            $result = array(
    			'code' => 201,
                'msg' => '安装失败：当前插件已安装！'
    		);
        }
    }else {
        
        // SESSION过期
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }
    
    // 检测配置文件目录权限
    function installPermission($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        $perms = fileperms($dir);
        return ($perms & 0x1FF) >= 0755;
    }
	
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>