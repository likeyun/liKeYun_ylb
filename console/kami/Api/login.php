<?php

    // 页面编码
	header("Content-type:application/json");
	
	// 数据库配置
	include '../../Db.php';
    
    // 实例化类
	$db = new DB_API($config);
	
	// 读取配置
    $getKamiConfig = $db->set_table('ylb_kamiConfig')->findAll(
	    $conditions = ['id' => 1],
	    $order = null,
	    $fields = 'kmConf_appid,kmConf_appsecret',
	    $limit = null
	);

    // 从配置中获取小程序的App ID和App Secret
    $appid = $getKamiConfig[0]['kmConf_appid'];
    $secret = $getKamiConfig[0]['kmConf_appsecret'];
    
    // 登录时获取的 code
    $js_code = $_GET['code'];
    
    // 构建请求参数
    $params = array(
        'appid' => $appid,
        'secret' => $secret,
        'js_code' => $js_code,
        'grant_type' => 'authorization_code'
    );
    
    // 构建请求 URL
    $url = 'https://api.weixin.qq.com/sns/jscode2session?' . http_build_query($params);
    
    // 发送请求
    $response = file_get_contents($url);
    
    // 输出响应结果
    $ret = array(
        'code' => 200,
        'msg' => '登录成功',
        'openid' => json_decode($response)->openid
    );
    
    echo json_encode($ret);
?>
