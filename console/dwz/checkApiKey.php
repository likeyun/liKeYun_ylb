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
        // 接收参数
    	$apikey = $_POST['apikey'];
    	
        // 过滤参数
    	if(empty($apikey) || !isset($apikey)){
    	    
    	    $result = array(
    		    'code' => 203,
    		    'msg' => '请输入你要查询的ApiKey'
    		);
    	}else{
    	    
    	    // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
        	// 获取当前登录用户的管理权限
            $user_admin = json_decode(json_encode($db->set_table('huoma_user')->find(['user_name'=>$LoginUser])))->user_admin;
            if($user_admin == 2){
                
                // 没有管理权限
                $result = array(
                    'code' => 202,
                    'msg' => '没有管理权限'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 执行查询
            $checkApiKeyResult = $db->set_table('huoma_dwz_apikey')->find(['apikey'=>$apikey]);
            
            if($checkApiKeyResult){
                
                // 查询成功
                $result = array(
        			'code' => 200,
                    'msg' => '查询成功',
                    'apikeyInfo' => $checkApiKeyResult
        		);
            }else{
                
                // 暂无数据
                $result = array(
        			'code' => 204,
                    'msg' => '无法查询到该ApiKey'
        		);
            }
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