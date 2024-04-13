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
        
        // 读取JSON文件内容
        $jsonFile = '../app.json';
        $jsonData = file_get_contents($jsonFile);
        
        // 解码JSON数据
        $data = json_decode($jsonData, true);
        
        // 获取安装状态
        $status = $data['install'];
        
        if($status == 1) {
            
            // 未安装
            // 设置为已安装
            $data['install'] = 2;
            
            // 编码为JSON格式
            $appJsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            // 写回JSON文件
            file_put_contents($jsonFile, $appJsonData);
            
            // 如果你需要操作数据库
            // 请在这里编写你操作数据库的逻辑
            
            // 连接数据库
            include '../../../../Db.php';
            $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
            
            // 这里演示向数据库创建一个名为：ylb_plugin_sdk 的表
            $ylb_plugin_sdk = "CREATE TABLE `ylb_plugin_sdk` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `sdk_id` int(10) DEFAULT NULL COMMENT 'ID',
              `sdk_title` varchar(64) DEFAULT NULL COMMENT '标题'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='SDK测试表'";
            
            if($conn->query($ylb_plugin_sdk) === TRUE) {
                
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
                'msg' => '安装失败'
    		);
        }
    }else {
        
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }
	
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>