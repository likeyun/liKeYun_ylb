<?php

	// 页面编码
	header("Content-type:application/json");

	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $jw_id = trim($_GET['jw_id']);
        
        // 过滤参数
        if(empty($jw_id) || !isset($jw_id)){
            
            // 非法请求
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 检查当前账号是否为超管
            $checkUser = $db->set_table('huoma_user')->find(['user_name'=>$_SESSION["yinliubao"]]);
            $user_admin = $checkUser['user_admin'];
        
        	// 获取当前jw_id的详情
            $getJwInfo = $db->set_table('ylb_jumpWX')->find(['jw_id'=>$jw_id]);
            
            // 返回数据
            if($getJwInfo){
                
                // 有结果
                $result = array(
        		    'jwInfo' => $getJwInfo,
        		    'user_admin' => $user_admin,
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
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>