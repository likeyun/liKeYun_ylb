<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     * 程序用途：获取域名列表
     * 最后维护日期：2023-10-18
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     * 该软件遵循MIT开源协议。
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 当前登录的用户
        $loginUser = $_SESSION["yinliubao"];
        
        // 获取当前登录的用户的用户组
        $user_group = $db->set_table('huoma_user')->find(['user_name' => $loginUser])['user_group'];
        
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
        
        $rk = getDomainsByType($db, 1, $user_group); // 入口域名
        $ld = getDomainsByType($db, 2, $user_group); // 落地域名
        $dl = getDomainsByType($db, 3, $user_group); // 短链域名
        $ycc = getDomainsByType($db, 5, $user_group); // 对象存储域名
    	
        // 获取结果
    	$result = array(
		    'rkymList' => $rk,
		    'ldymList' => $ld,
		    'dlymList' => $dl,
		    'yccymList' => $ycc,
		    'code' => 200,
		    'msg' => '获取成功'
    	);
    	
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