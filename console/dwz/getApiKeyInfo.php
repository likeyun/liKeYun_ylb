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
        $apikey_id = trim($_GET['apikey_id']);
        
        // 过滤参数
        if(empty($apikey_id) || $apikey_id == '' || $apikey_id == null || !isset($apikey_id)){
            
            // 非法请求
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
        	// 获取当前登录用户的管理权限
            $user_admin = json_decode(json_encode($db->set_table('huoma_user')->find(['user_name'=>$_SESSION["yinliubao"]])))->user_admin;
            if($user_admin == 2){
                
                // 没有管理权限
                $result = array(
                    'code' => 202,
                    'msg' => '没有管理权限'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
        
        	// 获取当前apikey_id的详情
        	$getapikeyInfo = ['apikey_id'=>$apikey_id];
            $getapikeyInfoResult = $db->set_table('huoma_dwz_apikey')->find($getapikeyInfo);
            
            // 返回数据
            if($getapikeyInfoResult && $getapikeyInfoResult > 0){
                
                // 有结果
                $result = array(
        		    'apikeyInfo' => $getapikeyInfoResult,
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