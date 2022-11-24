<?php

// 面向对象连接数据库
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 验证是否存在huoma_dwz表
$conn->query('SELECT * FROM huoma_dwz');
if(preg_match("/huoma_dwz' doesn/", $conn->error)){
    
    // 不存在huoma_dwz表
    $result = array(
		'code' => 205,
        'msg' => '点击这里进行升级'
	);
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	exit;
}

// 验证是否存在huoma_dwz_apikey表
$conn->query('SELECT * FROM huoma_dwz_apikey');
if(preg_match("/huoma_dwz_apikey' doesn/", $conn->error)){
    
    // 不存在huoma_dwz_apikey表
    $result = array(
		'code' => 205,
        'msg' => '点击这里进行升级'
	);
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	exit;
}

// 验证huoma_count表里面的count_dwz_pv字段是否存在
$conn->query('SELECT count_dwz_pv FROM huoma_count');
if(preg_match("/Unknown column 'count_dwz_pv'/", $conn->error)){
    
    // 不存在count_dwz_pv字段
    $result = array(
		'code' => 205,
        'msg' => '点击这里进行升级'
	);
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	exit;
}

?>