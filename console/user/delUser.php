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
            
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
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
        	
            // 验证当前登录用户是否有管理员权限
            $getLoginUserAdmin = ['user_name'=>$LoginUser];
            $getLoginUserAdminResult = $huoma_user->find($getLoginUserAdmin);
            $user_admin = json_decode(json_encode($getLoginUserAdminResult))->user_admin;
            $user_name = json_decode(json_encode($getLoginUserAdminResult))->user_name;
            
            // 判断操作结果
            if($user_admin == 1){
                
                // 有管理员权限：允许操作
                // 最后一步：检查删除的账号是否为当前账号
                if($user_name == $LoginUser){
                    
                    // 是
                    // 删除失败
                    $result = array(
                        'code' => 202,
                        'msg' => '删除失败，原因：无法删除自己！'
                    );
                }else{
                    
                    // 否
                    $delThisUser = ['user_id'=>$user_id];
                    $delThisUserResult = $huoma_user->delete($delThisUser);
                    
                    // 判断操作结果
                    if($delThisUserResult){
                        
                        // 删除成功
                        $result = array(
        			        'code' => 200,
                            'msg' => '删除成功'
        		        );
                    }else{
                        
                        // 删除失败
                        $errorInfo = json_decode(json_encode($delThisUserResult,true))[2];
                        if(!$errorInfo){
                        
                            // 如果没有报错信息
                            $errorInfo = '未知';
                        }
                        
                        // 删除失败
                        $result = array(
                            'code' => 202,
                            'msg' => '删除失败，原因：'.$errorInfo
                        );
                    }
                }
            }else{
                
                // 非管理员
                $result = array(
			        'code' => 202,
                    'msg' => '删除失败，没有管理员权限！'
		        );
            }
        }
    	
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录或登录失效'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>