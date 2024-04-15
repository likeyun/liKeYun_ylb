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
    	$usergroup_id = trim(intval($_GET['usergroup_id']));
    	
        // 过滤参数
        if(empty($usergroup_id) || !isset($usergroup_id)){
            
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
            $getAdmin = $db->set_table('huoma_user')->find(['user_name' => $LoginUser])['user_admin'];
            
            // 管理员权限获取结果
            if($getAdmin == 1){
                
                // 获取这个用户组ID的用户组名称
                $getUsergroupName = $db->set_table('ylb_usergroup')->find(['usergroup_id' => $usergroup_id])['usergroup_name'];
                
                // 判断是否还有用户在使用这个用户组
                $getUsergroupIsUsed = $db->set_table('huoma_user')->find(['user_group' => $getUsergroupName]);
                
                if($getUsergroupIsUsed) {
                    
                    // 还有用户在用
                    // 删除失败
                    $result = array(
                        'code' => 202,
                        'msg' => '该用户组还有用户在使用，如需删除，请将用户的用户组设置为其它用户组再删除。'
                    );
                }else {
                    
                    // 没有用户在用
                    // 执行
                    $delUsergroup = $db->set_table('ylb_usergroup')->delete(['usergroup_id' => $usergroup_id]);
                    
                    // 结果
                    if($delUsergroup){
                        
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