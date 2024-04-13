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
    	$corpid = trim($_POST['corpid']);
    	$corpsecret = trim($_POST['corpsecret']);
    	$touser = trim($_POST['touser']);
    	$agentid = trim($_POST['agentid']);
    	$bark_url = trim($_POST['bark_url']);
    	$email_acount = $_POST['email_acount'];
    	$email_pwd = trim($_POST['email_pwd']);
    	$email_receive = trim($_POST['email_receive']);
    	$email_smtp = trim($_POST['email_smtp']);
    	$email_port = trim($_POST['email_port']);
    	$SendKey = trim($_POST['SendKey']);
    	$http_url = trim($_POST['http_url']);
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
    	// 获取当前登录用户的管理权限
        $user_admin = json_decode(json_encode($db->set_table('huoma_user')->find(['user_name'=>$LoginUser])))->user_admin;
        if($user_admin == 2){
            
            // 没有管理权限
            $result = array(
                'code' => 202,
                'msg' => '没有管理权限'
            );
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 参数
        $nitificationConfigParams = [
            'corpid' => $corpid,
            'corpsecret' => $corpsecret,
            'touser' => $touser,
            'agentid' => $agentid,
            'bark_url' => $bark_url,
            'email_acount' => $email_acount,
            'email_pwd' => $email_pwd,
            'email_receive' => $email_receive,
            'email_smtp' => $email_smtp,
            'email_port' => $email_port,
            'SendKey' => $SendKey,
            'http_url' => $http_url
        ];
        
        // 提交更新
        $nitificationConfig = $db->set_table('huoma_notification')->update(['id'=>1],$nitificationConfigParams);
        
        if($nitificationConfig){
            
            // 更新成功
            $result = array(
	            'code' => 200,
                'msg' => '配置成功'
            );
        }else{
            
            // 更新失败
            $result = array(
	            'code' => 202,
                'msg' => '配置失败'
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