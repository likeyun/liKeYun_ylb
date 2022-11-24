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
        if(empty($user_id) || !isset($user_id)){
            
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
        	// 获取当前登录账号的操作权限
            $getLoginUserAdmin = $db->set_table('huoma_user')->find(['user_name'=>$LoginUser]);
            $user_admin = json_decode(json_encode($getLoginUserAdmin))->user_admin;
            
            if($user_admin == 1){
                
                // 管理员：可进入下一步操作
                // 获取当前点击的user_id的状态
                $getThisUseridStatus = $db->set_table('huoma_user')->find(['user_id'=>$user_id]);
                $user_status = json_decode(json_encode($getThisUseridStatus))->user_status;
                
                if($user_status == 1){
                    
                    // 更新的数据
                    $updateUserData = [
                        'user_status' => 2
                    ];
                    
                    $statusText = '已停用';
                }else{
                    
                    // 更新的数据
                    $updateUserData = [
                        'user_status' => 1
                    ];
                    
                    $statusText = '已启用';
                }
                
                // 更新的条件
                $updateUserCondition = [
                    'user_id' => $user_id
                ];
                
                // 提交更新
                $updateUser = $db->set_table('huoma_user')->update($updateUserCondition,$updateUserData);
                
                // 验证更新结果
                if($updateUser){
                    
                    // 更新成功
                    $result = array(
			            'code' => 200,
                        'msg' => $statusText
		            );
                }else{
                    
                    // 更新失败
                    $result = array(
			            'code' => 202,
                        'msg' => '更新失败'
		            );
                }
                
            }else{
                
                // 非管理员：禁止直接操作，需要进一步鉴权
                // 验证当前要操作的user_id是否与当前登录账号相符
                // 根据user_id获取user_name
                $getThisUseridUserName = $db->set_table('huoma_user')->find(['user_id'=>$user_id]);
                $user_name = json_decode(json_encode($getThisUseridUserName))->user_name;
                
                if($user_name == $LoginUser){
                    
                    // user_id与当前登录的账号相符：允许下一步操作
                    // 获取当前user_id的状态
                    $user_status = json_decode(json_encode($getThisUseridUserName))->user_status;
                    
                    if($user_status == 1){
                        
                        // 更新的数据
                        $updateUserData = [
                            'user_status' => 2
                        ];
                        
                        $statusText = '已停用';
                    }else{
                        
                        // 更新的数据
                        $updateUserData = [
                            'user_status' => 1
                        ];
                        
                        $statusText = '已启用';
                    }
                    
                    // 更新的条件
                    $updateUserCondition = [
                        'user_id' => $user_id
                    ];
                    
                    // 提交更新
                    $updateUser = $db->set_table('huoma_user')->update($updateUserCondition,$updateUserData);
                    
                    // 验证更新结果
                    if($updateUser){
                        
                        // 更新成功
                        $result = array(
    			            'code' => 200,
                            'msg' => $statusText
    		            );
                    }else{
                        
                        // 更新失败
                        $result = array(
    			            'code' => 202,
                            'msg' => '更新失败'
    		            );
                    }
                }else{
                    
                    // 不相符
                    $result = array(
		                'code' => 202,
                        'msg' => '非法操作！没有操作权限！'
	                );
                }
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