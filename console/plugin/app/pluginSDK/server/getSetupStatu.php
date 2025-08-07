<?php

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
            $result = array(
    			'code' => 200,
                'msg' => '插件未安装'
    		);
            
        }else {
            
            // 已安装
            $result = array(
    			'code' => 201,
                'msg' => '插件已安装'
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