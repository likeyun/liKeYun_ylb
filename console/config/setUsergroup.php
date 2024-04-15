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
    	$newUsergroupArray = trim($_GET['newUsergroupArray']);
    	$domain_id = trim(intval($_GET['domain_id']));
    	
        // 过滤参数
        if(empty($domain_id) || !isset($domain_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求'
            );
        }else if(empty($newUsergroupArray) || !isset($newUsergroupArray)) {
            
            $result = array(
                'code' => 203,
                'msg' => '至少要选择一个用户组'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
        	// 获取当前登录用户的管理权限
            $user_admin = $db->set_table('huoma_user')->find(['user_name'=>$LoginUser])['user_admin'];
            if($user_admin == 2){
                
                // 没有管理权限
                $result = array(
                    'code' => 202,
                    'msg' => '没有管理权限'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 将$newUsergroupArray转换为数组类型，并且转为JSON
            $newUsergroupArray_ = json_encode(explode(",", $newUsergroupArray), JSON_UNESCAPED_UNICODE);
            
            // 如果空值
            if(empty($newUsergroupArray) || $newUsergroupArray == '') {
                
                // 设置为NULL
                $newUsergroupArray_ = NULL;
            }
            
            // 提交更新
            $setUsergroup = $db->set_table('huoma_domain')->update(
                ['domain_id' => $domain_id], 
                ['domain_usergroup' => $newUsergroupArray_]
            );

            if($setUsergroup){
                
                // 已设置
                $result = array(
		            'code' => 200,
                    'msg' => '已设置'
	            );
            }else{
                
                // 设置失败
                $result = array(
		            'code' => 202,
                    'msg' => '设置失败'
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