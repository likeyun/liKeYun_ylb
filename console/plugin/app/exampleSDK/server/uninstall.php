<?php

    /**
     * 状态码说明
     * 状态码：200 操作成功
     * 其它状态码自己定义就行
     * 源码用途：卸载程序，修改app.json的install=1就是卸载成功
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
        
        // 如果是2,则为已安装的状态
        // 则可以执行以下代码
        if($status == 2) {
            
            // 如果你需要操作数据库
            // 请在这里开始编写你操作数据库的逻辑
            // 例如下面删除表...

            // 例如这里删除 ylb_plugin_sdk 这个表
            $drop_ylb_plugin_sdk = "DROP TABLE ylb_plugin_sdk";
            
            if($conn->query($drop_ylb_plugin_sdk) === TRUE) {
                
                // 删除表成功
                // 修改app.json设置为未安装
                $data['install'] = 1;
                $appJsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                file_put_contents($jsonFile, $appJsonData);
                
                // 卸载成功
                $result = array(
        			'code' => 200,
                    'msg' => '卸载成功'
        		);
            }else {
                
                // 删除失败
                // 卸载失败
                $result = array(
        			'code' => 201,
                    'msg' => '卸载失败' . $conn->error
        		);
            }
        }else {
            
            // 未安装
            $result = array(
    			'code' => 201,
                'msg' => '卸载失败：插件未安装'
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