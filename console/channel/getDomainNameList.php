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
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    
    	// 数据库huoma_domain表
    	$huoma_domain = $db->set_table('huoma_domain');
    
    	// 执行查询（查询当前domain_type=1的域名）
    	$find_rkym = $huoma_domain->findAll(
    	    $conditions = ['domain_type' => '1'],
    	    $order = 'ID DESC'
    	);
    	
    	// 执行查询（查询当前domain_type=2的域名）
    	$find_ldym = $huoma_domain->findAll(
    	    $conditions = ['domain_type' => '2'],
    	    $order = 'ID DESC'
    	);
    	
    	// 执行查询（查询当前domain_type=3的域名）
    	$find_dlym = $huoma_domain->findAll(
    	    $conditions = ['domain_type' => '3'],
    	    $order = 'ID DESC'
    	);
    	
    	
        // 返回查询结果
    	$result = array(
		    'rkymList' => $find_rkym,
		    'ldymList' => $find_ldym,
		    'dlymList' => $find_dlym,
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