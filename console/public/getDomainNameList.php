<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     * 程序用途：获取域名列表
     * 最后维护日期：2023-06-03
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
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
    
    	// 获取入口域名
    	$get_rkym = $db->set_table('huoma_domain')->findAll(
    	    $conditions = ['domain_type' => 1],
    	    $order = 'ID DESC'
    	);
    	
    	// 获取落地域名
    	$get_ldym = $db->set_table('huoma_domain')->findAll(
    	    $conditions = ['domain_type' => 2],
    	    $order = 'ID DESC'
    	);
    	
    	// 获取短链域名
    	$get_dlym = $db->set_table('huoma_domain')->findAll(
    	    $conditions = ['domain_type' => 3],
    	    $order = 'ID DESC'
    	);
    	
    	// 获取云储存域名
    	$get_yccym = $db->set_table('huoma_domain')->findAll(
    	    $conditions = ['domain_type' => 5],
    	    $order = 'ID DESC'
    	);
    	
        // 获取结果
    	$result = array(
		    'rkymList' => $get_rkym,
		    'ldymList' => $get_ldym,
		    'dlymList' => $get_dlym,
		    'yccymList' => $get_yccym,
		    'code' => 200,
		    'msg' => '获取成功'
    	);
    	
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