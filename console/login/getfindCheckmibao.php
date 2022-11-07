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
	
    // 非法字符过滤
	include './sqlRep.php';
	
	// 接收参数
	$user_name = trim(sqlRep($_GET['user_name']));
	$user_email = trim($_GET['user_email']);
	
    // 数据库配置
	include '../Db.php';

	// 实例化类
	$db = new DB_API($config);

	// 数据库huoma_user表
	$huoma_user = $db->set_table('huoma_user');
	
    // 根据账号和邮箱获取密保问题
    $getmibao = ['user_name'=>$user_name,'user_email'=>$user_email];
    $getmibaoResult = $huoma_user->find($getmibao);
    $mibao = json_decode(json_encode($getmibaoResult))->user_mb_ask;
    if($mibao){
        
        $result = array(
            'code' => 200,
            'msg' => '获取成功',
            'user_mb_ask' => $mibao
        );
    }else{
        
        $result = array(
            'code' => 202,
            'msg' => '获取失败'
        );
    }
    
	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>