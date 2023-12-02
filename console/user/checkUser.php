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
    	$user_name = $_POST['user_name'];
    	
        // 过滤参数
    	if(empty($user_name) || !isset($user_name)){
    	    
    	    $result = array(
    		    'code' => 203,
    		    'msg' => '请输入你要查询的账号'
    		);
    	}else{
    	    
    	    // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 数据库huoma_user表
        	$huoma_user = $db->set_table('huoma_user');
        	
            // 获取当前登录用户的账号权限
            $getuserAdmin = ['user_name'=>$LoginUser];
            $getuserAdminResult = $huoma_user->find($getuserAdmin);
            // 权限 1管理 2非管理
            $user_admin = json_decode(json_encode($getuserAdminResult))->user_admin;
            
            // 根据权限选择返回用户列表
            if($user_admin == 1){
                
                // 管理员
                // 允许查询
                $getCheckUserInfo = $huoma_user->findAll(
        	        $conditions = ['user_name'=>$user_name],
        	        $order = 'ID ASC',
        	        $fields = 'user_id,user_name,user_email,user_creat_time,user_admin,user_status,user_manager,user_beizhu,user_group',
        	        $limit = null
        	    );
        	    
        	    // 判断获取结果
        	    if($getCheckUserInfo && $getCheckUserInfo > 0){
        	        
                    // 获取成功
            		$result = array(
            		    'userList' => $getCheckUserInfo,
            		    'code' => 200,
            		    'msg' => '查询成功'
            		);
        	    }else{
        	        
                    // 获取失败
                    $result = array(
                        'code' => 202,
                        'msg' => '无法查询到相关结果'
                    );
        	    }
                
            }else{
                
                // 非管理员
                // 没有查询权限
                // 获取失败
        		$result = array(
        		    'code' => 202,
        		    'msg' => '没有管理员权限无法进行查询！'
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