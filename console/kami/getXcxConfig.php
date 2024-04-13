<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    
    	// 获取小程序配置
        $getXcxConfig = $db->set_table('ylb_kamiConfig')->find(['id' => 1]);
        
        // 返回数据
        if($getXcxConfig){
            
            // 有结果
            $result = array(
    		    'xcxConfig' => $getXcxConfig,
    		    'code' => 200,
    		    'msg' => '获取成功'
		    );
        }else{
            
            // 无结果
            $result = array(
    		    'code' => 204,
    		    'msg' => '获取失败'
		    );
        }
    	
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>