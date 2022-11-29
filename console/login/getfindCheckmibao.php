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
	
	// 接收参数
	$user_name = trim($_GET['user_name']);
	$user_email = trim($_GET['user_email']);
	
	// sql防注入
    if(
        preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_name) || 
        preg_match("/[\',:;*?~`!#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_email)){
            
            $result = array(
		        'code' => 203,
                'msg' => '你输入的内容包含了一些不安全字符'
	        );
	        echo json_encode($result,JSON_UNESCAPED_UNICODE);
	        exit;
    }else if(
        preg_match("/(and|or|select|update|drop|DROP|insert|create|delete|like|where|join|script|set)/i",$user_name)){
        
        $result = array(
	        'code' => 203,
            'msg' => '你输入的内容包含了一些不安全字符'
        );
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit;
    }
	
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
