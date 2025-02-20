<?php

    /**
     * 状态码说明
     * 状态码：200 操作成功
     * 其它状态码自己定义就行
     * 源码用途：卸载程序，修改 app.json 的 install=1 就是卸载成功
     * 作者：TANKING
     */

	// 编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 读取JSON文件内容
        $jsonFile = '../app.json';
        $jsonData = file_get_contents($jsonFile);
        
        // 解码JSON数据
        $data = json_decode($jsonData, true);
        
        // 连接数据库
        include '../../../../Db.php';
        $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        
        // 验证当前登录用户是否为管理员
        $check_admin = "SELECT user_admin FROM huoma_user WHERE user_name = '$LoginUser'";
        $check_admin_result = $conn->query($check_admin)->fetch_assoc();
        
        // 如果不是管理员，不允许卸载
        if($check_admin_result['user_admin'] == 2) {
            
            $result = array(
        		'code' => 202,
                'msg' => '卸载失败：没有管理权限！'
        	);
        	echo json_encode($result,JSON_UNESCAPED_UNICODE);
        	exit;
        }
        
        // 获取安装状态
        $status = $data['install'];
        
        if($status == 2) {
            
            // 已安装
            // 删除 ylbPlugin_sdk 表
            mysqli_query($conn,"DROP TABLE ylbPlugin_sdk");
            
            // 设置为未安装
            $data['install'] = 1;
            $data['install_time'] = "";
            $data['current_status'] = "未安装";
            
            // 编码为JSON格式
            // JSON_PRETTY_PRINT：格式化JSON
            // JSON_UNESCAPED_UNICODE：不对中文编码
            // JSON_UNESCAPED_SLASHES：不对斜杠进行反斜杠编码
            $appJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // 写回JSON文件
            file_put_contents($jsonFile, $appJsonData);
            
            // 安装成功
            $result = array(
    			'code' => 200,
                'msg' => '已卸载'
    		);
            
        }else {
            
            // 已安装
            $result = array(
    			'code' => 201,
                'msg' => '卸载失败'
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