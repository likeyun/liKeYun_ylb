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
            
            // 安装成功
            $result = array(
    			'code' => 200,
                'msg' => '安装成功'
    		);
            
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