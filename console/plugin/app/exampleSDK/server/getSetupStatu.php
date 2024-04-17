<?php

    /**
     * 状态码说明
     * 状态码：200 操作成功
     * 其它状态码自己定义就行
     * 源码用途：获取安装状态，状态码200为未安装，读取app.json的install=1就是未安装，否则已安装
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
        
        // 这里仅仅是通过判断install是否等于1作为判断插件是否已安装
        // 建议你加入更多复杂逻辑去判断插件是否已安装
        // 例如安装日志、数据库判断等
        
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