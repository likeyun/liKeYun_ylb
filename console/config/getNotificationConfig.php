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
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);

    	// 获取当前登录账号的管理员权限
    	$user_admin = $db->set_table('huoma_user')->getField(['user_name'=>$LoginUser],'user_admin');
    	if($user_admin == 1){
    	    
            // 获得管理权限
            // 获取所有配置信息
            $getNotificationConfig = $db->set_table('huoma_notification')->find(['id'=>1]);
            
            if($getNotificationConfig && $getNotificationConfig > 0){
                
                // 获取成功
                $result = array(
                    'notificationConfig' => $getNotificationConfig,
                    'code' => 200,
                    'msg' => '获取成功'
                );
            }else{
                
                // 暂无配置
                $result = array(
                    'code' => 204,
                    'msg' => '暂无配置'
                );
            }
            
    	}else{
    	    
    	   // 没有管理权限
    	   $result = array(
                'code' => 204,
                'msg' => '没有管理权限'
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