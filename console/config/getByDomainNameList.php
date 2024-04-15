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
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);

    	// 获取当前登录账号的管理员权限
    	$user_admin = $db->set_table('huoma_user')->getField(['user_name'=>$LoginUser],'user_admin');
    	if($user_admin == 1){
    	    
    	    // 当前登录的用户
            $loginUser = $_SESSION["yinliubao"];
        
            // 获取当前登录的用户的用户组
            $user_group = $db->set_table('huoma_user')->find(['user_name' => $loginUser])['user_group'];
            
            // 获得管理权限
            // 获取当前授权用户组的授权域名
        	function getDomainsByType($db, $domainType, $userGroup) {
        	    
                $getDomainSQL = $db->set_table('huoma_domain')->findAll(
                    ['domain_type' => $domainType],
                    'ID DESC',
                    'domain, domain_usergroup'
                );
                
                // 去掉空值
                $filteredResult = array_filter($getDomainSQL, function($item) {
                    return !empty($item['domain_usergroup']);
                });
                $filteredResult = array_values($filteredResult);
                $finalResult = [];
                
                // 遍历授权用户组的域名
                foreach ($filteredResult as $item) {
                    $domainUserGroups = json_decode($item['domain_usergroup']);
            
                    foreach ($domainUserGroups as $usergroup) {
                        if ($usergroup == $userGroup) {
                            $finalResult[] = $item;
                            break;
                        }
                    }
                }
                
                return $finalResult;
            }
            
            $by = getDomainsByType($db, 4, $user_group); // 备用域名

            if($by){
                
                // 获取成功
                $result = array(
                    'domainList' => $by,
                    'code' => 200,
                    'msg' => '获取成功'
                );
            }else{
                
                // 暂无域名
                $result = array(
                    'code' => 204,
                    'msg' => '暂无域名'
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