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
        $user_id = trim($_GET['user_id']);
        
        // 过滤参数
        if(empty($user_id) || $user_id == '' || $user_id == null || !isset($user_id)){
            
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
        
        	// 数据库huoma_user表
        	$huoma_user = $db->set_table('huoma_user');
        
        	// 获取当前user_id的详情
            $getuserInfoResult = $huoma_user->findAll(
    	        $conditions = ['user_id'=>$user_id],
    	        $order = null,
    	        $fields = 'user_id,user_name,user_email,user_mb_ask,user_mb_answer,user_status,user_beizhu',
    	        $limit = null
    	    );
            
            // 返回数据
            if($getuserInfoResult && $getuserInfoResult > 0){
                
                // 有结果
                $result = array(
        		    'userInfo' => $getuserInfoResult,
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