<?php

// 面向对象连接数据库
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 验证是否存在huoma_shareCard表
$conn->query('SELECT * FROM huoma_shareCard');
if(preg_match("/huoma_shareCard' doesn/", $conn->error)){
    
    // 不存在huoma_shareCard表
    $result = array(
		'code' => 205,
        'msg' => '点击这里进行升级'
	);
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	exit;
}

// 验证是否存在huoma_shareCardConfig表
$conn->query('SELECT * FROM huoma_shareCardConfig');
if(preg_match("/huoma_shareCardConfig' doesn/", $conn->error)){
    
    // 不存在huoma_shareCardConfig表
    $result = array(
		'code' => 205,
        'msg' => '点击这里进行升级'
	);
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	exit;
}

?>