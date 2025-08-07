<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
  
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
        
        // 获取当前登录账号的管理员权限
    	$user_admin = $db->set_table('huoma_user')->getField(['user_name' => $LoginUser],'user_admin');
    	
        // 获取7天的IP数据
    	$get7DaysIpData = $db->set_table('huoma_ip')->findAll(
    	    $conditions=null,
    	    $order='id desc',
    	    $fields=null,
    	    $limit='1,7'
    	);
    	
        // 管理员才返回数据
    	if($get7DaysIpData && $user_admin == 1) {
    	    
    	    $result = array(
			    'code' => 200,
			    'sevenDaysIpData' => $get7DaysIpData,
                'msg' => '获取成功'
		    );
    	}else if($user_admin == 2) {
    	    
    	    $result = array(
			    'code' => 202,
                'msg' => '无查看数据的权限'
		    );
    	}else {
    	    
    	    $result = array(
			    'code' => 203,
                'msg' => '暂无IP数据'
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