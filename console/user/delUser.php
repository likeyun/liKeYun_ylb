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
    	$user_id = trim($_GET['user_id']);
    	
        // 过滤参数
        if(empty($user_id) || !isset($user_id)){
            
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
        	
            // 验证当前登录用户是否有管理员权限
            $checkUser = $db->set_table('huoma_user')->find(['user_name' => $LoginUser]);
            $user_admin = json_decode(json_encode($checkUser))->user_admin;
            $user_name = json_decode(json_encode($checkUser))->user_name;
            $user_id_bythisLoginUser = json_decode(json_encode($checkUser))->user_id;
            
            // 判断操作结果
            if($user_admin == 1){
                
                // 有管理员权限：允许操作
                // 最后一步：检查删除的账号是否为当前账号
                if($user_id == $user_id_bythisLoginUser){
                    
                    // 是
                    // 删除失败
                    $result = array(
                        'code' => 202,
                        'msg' => '删除失败，原因：无法删除自己！'
                    );
                }else{
                    
                    // 否
                    $delUserAccount = $db->set_table('huoma_user')->delete(['user_id' => $user_id]);
                    
                    // 操作结果
                    if($delUserAccount){
                        
                        // 删除成功
                        $result = array(
        			        'code' => 200,
                            'msg' => '已删除'
        		        );
                    }else{
                        
                        // 删除失败
                        $result = array(
                            'code' => 202,
                            'msg' => '删除失败'
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
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>