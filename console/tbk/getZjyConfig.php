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
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
    
    	// 获取当前登录的用户的中间页配置
        $getThisUserZjyConfig = $db->set_table('huoma_tbk_config')->find(['zjy_config_user'=>$LoginUser]);
        
        // 返回数据
        if($getThisUserZjyConfig && $getThisUserZjyConfig > 0){
            
            // 有结果
            $result = array(
    		    'zjyConfigInfo' => $getThisUserZjyConfig,
    		    'code' => 200,
    		    'msg' => '获取成功'
		    );
        }else{
            
            // 无结果
            $result = array(
    		    'code' => 204,
    		    'msg' => '暂无配置信息'
		    );
        }
    	
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录或登录过期'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>