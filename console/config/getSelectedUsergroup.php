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
    	$domain_id = trim($_GET['domain_id']);
    	
        // 过滤参数
        if(empty($domain_id) || !isset($domain_id)){
            
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
            
            // 获取当前域名的用户组
            $getSelectedUsergroup = $db->set_table('huoma_domain')->find(['domain_id' => $domain_id])['domain_usergroup'];
            
            // 获取所有用户组
            $getUsergroupList = $db->set_table('ylb_usergroup')->findAll(
                $conditions=null,
                $order=null,
                $fields='usergroup_name',
                $limit=null
            );
            
            $usergroupNames = array();
            foreach ($getUsergroupList as $item) {
                if (isset($item['usergroup_name'])) {
                    $usergroupNames[] = $item['usergroup_name'];
                }
            }
            
            if($getSelectedUsergroup && $getUsergroupList){
                
                // 获取可选用户组
                $usergroupList = array_values(array_diff($usergroupNames, json_decode(str_replace("'", '"', $getSelectedUsergroup))));
                
                // 已设置
                $result = array(
		            'code' => 200,
                    'msg' => '获取成功',
                    'domain_id' => $domain_id,
                    'domain_usergroup' => json_decode(str_replace("'", '"', $getSelectedUsergroup)), // 已选
                    'usergroupList' => $usergroupList, // 可选
	            );
            }else{
                
                // 设置失败
                $result = array(
		            'code' => 202,
                    'msg' => '用户组为空',
                    'domain_id' => $domain_id,
                    'domain_usergroup' => [], // 已选
                    'usergroupList' => $usergroupNames // 可选
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