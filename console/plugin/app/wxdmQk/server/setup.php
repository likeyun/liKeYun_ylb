<?php

    /**
     * 状态码说明
     * 状态码：200 操作成功
     * 其它状态码自己定义就行
     * 源码用途：安装程序，修改app.json的install=2就是安装成功
     * 作者：TANKING
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
                'msg' => '安装失败，失败原因：console/plugin/app 目录没有755权限！请前往服务器修改权限！'
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
            
            // 未安装
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

            // ylbPlugin_wxdmQk 表
            $ylbPlugin_sdk = "CREATE TABLE `ylbPlugin_wxdmQk` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `data_id` int(10) DEFAULT NULL COMMENT '页面ID',
              `data_title` varchar(64) DEFAULT NULL COMMENT '标题',
              `data_pic` text DEFAULT NULL COMMENT '图片',
              `data_pv` int(10) DEFAULT '0' COMMENT '访问次数',
              `data_status` int(1) DEFAULT '1' COMMENT '状态 1正常 2停用',
              `data_dxccym` text DEFAULT NULL COMMENT '落地域名',
              `data_jumplink` text DEFAULT NULL COMMENT '目标地址',
              `data_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `data_key` varchar(10) DEFAULT NULL COMMENT 'Key',
              `data_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='链接列表'";
            
            if($conn->query($ylbPlugin_sdk) === TRUE) {
                
                // 创建表成功
                // 安装成功
                $result = array(
        			'code' => 200,
                    'msg' => '安装成功'
        		);
            }else {
                
                // 创建表失败
                // 安装失败
                $result = array(
        			'code' => 201,
                    'msg' => '安装失败：' . $conn->error
        		);
            }
            
        }else {
            
            // 已安装
            $result = array(
    			'code' => 201,
                'msg' => '安装失败：当前插件已安装！'
    		);
        }
    }else {
        
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